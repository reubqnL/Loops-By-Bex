#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

if [[ ! -f .env ]]; then
    echo "error: local .env file not found."
    exit 1
fi

exec python3 - "$@" <<'PY'
import fnmatch
import ftplib
import getpass
import hashlib
import json
import os
import posixpath
import socket
import ssl
import subprocess
import sys

args = set(sys.argv[1:])
dry_run = ("--dry-run" in args) or ("-n" in args)
force_all = ("--all" in args) or ("-a" in args)

env = {}
with open(".env", "r", encoding="utf-8") as fh:
    for raw_line in fh:
        line = raw_line.strip()
        if not line or line.startswith("#") or "=" not in line:
            continue
        k, v = line.split("=", 1)
        k = k.strip()
        v = v.strip().strip('"').strip("'")
        env[k] = v

host = env.get("DEPLOY_HOST", "").strip()
user = env.get("DEPLOY_USER", "").strip()
remote_root = env.get("DEPLOY_PATH", ".").strip() or "."
configured_port = env.get("DEPLOY_PORT", "").strip()
password = env.get("DEPLOY_PASS", "")

if not host or not user:
    print("error: set DEPLOY_USER and DEPLOY_HOST in your local .env file.")
    sys.exit(1)

EXCLUDED_DIRS = {
    ".git",
    ".idea",
    ".vscode",
    "database",
    "vendor",
    "node_modules",
    "Unselected files",
}

EXCLUDED_EXACT = {
    ".DS_Store",
    ".env",
    ".env.example",
    ".gitattributes",
    ".gitignore",
    ".deploy-cache.json",
    "includes/env.php",
    "deploy.sh",
    "router.php",
    "assets/wireframe.png",
    "composer.json",
    "composer.lock",
    "LICENSE",
}

GIT_ADMIN_FILES = {
    "FETCH_HEAD",
    "ORIG_HEAD",
    "MERGE_HEAD",
    "CHERRY_PICK_HEAD",
    "REVERT_HEAD",
    "COMMIT_EDITMSG",
    "MERGE_MSG",
    "SQUASH_MSG",
    "BISECT_LOG",
    "REBASE_HEAD",
}

EXCLUDED_PATTERNS = (
    ".env.*",
    "*.env",
    "style/*.scss",
    "style/*.map",
    "*.scss",
    "*.map",
    "*.sql",
    "*.sqlite",
    "*.sqlite3",
    "*.db",
    "*.log",
    "*.md",
    "*.sh",
    "assets/*.jpg",
    "assets/*.jpeg",
)


def is_excluded(rel_posix: str) -> bool:
    if rel_posix in EXCLUDED_EXACT:
        return True
    base = posixpath.basename(rel_posix)
    if base in GIT_ADMIN_FILES or base == ".DS_Store" or base.startswith(".env"):
        return True
    for pat in EXCLUDED_PATTERNS:
        if fnmatch.fnmatch(rel_posix, pat) or fnmatch.fnmatch(base, pat):
            return True
    return False


def sha256_file(path: str) -> str:
    h = hashlib.sha256()
    with open(path, "rb") as f:
        for chunk in iter(lambda: f.read(65536), b""):
            h.update(chunk)
    return h.hexdigest()


cache_path = ".deploy-cache.json"
target_identity = "\0".join((host.lower(), user, configured_port or "auto", posixpath.normpath(remote_root)))
target_cache_key = hashlib.sha256(target_identity.encode("utf-8")).hexdigest()
cache = {}
cache_store = {"format_version": 2, "targets": {}}
if os.path.isfile(cache_path):
    try:
        with open(cache_path, "r", encoding="utf-8") as cf:
            saved_cache = json.load(cf)
        if (
            isinstance(saved_cache, dict)
            and saved_cache.get("format_version") == 2
            and isinstance(saved_cache.get("targets"), dict)
        ):
            cache_store = saved_cache
    except Exception:
        pass

if not force_all:
    target_cache = cache_store["targets"].get(target_cache_key, {})
    if isinstance(target_cache, dict):
        cache = target_cache


def save_cache() -> None:
    targets = cache_store.setdefault("targets", {})
    targets[target_cache_key] = cache
    cache_store["format_version"] = 2
    with open(cache_path, "w", encoding="utf-8") as cf:
        json.dump(cache_store, cf, indent=2, sort_keys=True)


all_files = []
to_upload = []

for root, dirs, files in os.walk("."):
    dirs[:] = sorted(d for d in dirs if d not in EXCLUDED_DIRS)
    for fname in sorted(files):
        full = os.path.join(root, fname)
        rel = os.path.relpath(full, ".").replace(os.sep, "/")
        if is_excluded(rel):
            continue
        digest = sha256_file(full)
        all_files.append((rel, full, digest))
        if force_all or cache.get(rel) != digest:
            to_upload.append((rel, full, digest))

if remote_root in ("", "."):
    print("warning: DEPLOY_PATH is '.', relative to the SSH/FTP account's starting directory; confirm this is the live site's document root or set DEPLOY_PATH.")
if not force_all and all_files and not cache:
    print("notice: no per-target deploy cache is available; all local production files will be checked for this target.")

if dry_run:
    print(f"dry-run: {len(to_upload)} of {len(all_files)} production files queued for {user}@{host} ({remote_root}):")
    for rel, _, _ in to_upload:
        print(f"  + {rel}")
    print("\n(automatically blocked: Git metadata files including FETCH_HEAD, .env*, includes/env.php, .git/, style/*.scss, router.php, deploy.sh, database/)")
    sys.exit(0)

if not to_upload:
    print("already up to date — 0 changed files to upload. (use ./deploy.sh --all to force re-upload)")
    sys.exit(0)


def can_connect(port_num: int, timeout: float = 5.0) -> bool:
    try:
        with socket.create_connection((host, port_num), timeout=timeout):
            return True
    except OSError:
        return False


chosen_port = None
if configured_port.isdigit():
    p = int(configured_port)
    if can_connect(p):
        chosen_port = p
else:
    for candidate in (21, 22):
        if can_connect(candidate):
            chosen_port = candidate
            break

if chosen_port is None:
    print(f"error: could not reach {host} on port 21 (FTPS) or port 22 (SFTP).")
    print("  1. If you are on school/college/public Wi-Fi, outbound FTP/SSH ports (21 & 22) are often blocked — try home Wi-Fi or hotspot.")
    print("  2. Otherwise check PhpStorm -> Settings -> Deployment -> LOOPSBYBEX and set DEPLOY_PORT in .env if different.")
    sys.exit(1)

if chosen_port == 22:
    target_dir = remote_root if remote_root == "/" else f"{remote_root.rstrip('/')}/"
    cmd = [
        "rsync", "-avz", "--checksum",
        "--exclude=.git/",
        "--exclude=FETCH_HEAD",
        "--exclude=ORIG_HEAD",
        "--exclude=MERGE_HEAD",
        "--exclude=CHERRY_PICK_HEAD",
        "--exclude=REVERT_HEAD",
        "--exclude=COMMIT_EDITMSG",
        "--exclude=MERGE_MSG",
        "--exclude=SQUASH_MSG",
        "--exclude=BISECT_LOG",
        "--exclude=REBASE_HEAD",
        "--exclude=Unselected files/",
        "--exclude=.gitattributes",
        "--exclude=.gitignore",
        "--exclude=.idea/",
        "--exclude=.vscode/",
        "--exclude=.DS_Store",
        "--exclude=.env",
        "--exclude=.env.*",
        "--exclude=.env*",
        "--exclude=*.env",
        "--exclude=.deploy-cache.json",
        "--exclude=includes/env.php",
        "--exclude=deploy.sh",
        "--exclude=router.php",
        "--exclude=database/",
        "--exclude=vendor/",
        "--exclude=node_modules/",
        "--exclude=storage/*.sqlite",
        "--exclude=storage/*.sqlite3",
        "--exclude=storage/*.db",
        "--exclude=storage/*.log",
        "--exclude=style/*.scss",
        "--exclude=style/*.map",
        "--exclude=assets/*.jpg",
        "--exclude=assets/*.jpeg",
        "--exclude=assets/wireframe.png",
        "--exclude=composer.json",
        "--exclude=composer.lock",
        "--exclude=LICENSE",
        "--exclude=*.md",
        "--exclude=*.scss",
        "--exclude=*.map",
        "--exclude=*.sql",
        "--exclude=*.sqlite",
        "--exclude=*.sqlite3",
        "--exclude=*.db",
        "--exclude=*.log",
        "--exclude=*.sh",
        "./",
        f"{user}@{host}:{target_dir}",
    ]
    print(f"connected on port 22 (SFTP/SSH) — syncing via rsync...")
    rc = subprocess.call(cmd)
    if rc == 0:
        for rel, _, digest in all_files:
            cache[rel] = digest
        save_cache()
        print("deploy complete!")
    sys.exit(rc)

if not password:
    password = getpass.getpass(f"FTPS Password for {user}@{host}: ")


class SessionReuseFTP_TLS(ftplib.FTP_TLS):
    def ntransfercmd(self, cmd, rest=None):
        conn, size = ftplib.FTP.ntransfercmd(self, cmd, rest)
        if self._prot_p:
            conn = self.context.wrap_socket(
                conn,
                server_hostname=self.host,
                session=self.sock.session,
            )
        return conn, size


print(f"connecting to {host}:21 over Explicit FTPS (TLS)...")
ctx = ssl.create_default_context()
ftp = SessionReuseFTP_TLS(context=ctx)
ftp.connect(host, 21, timeout=12)
ftp.auth()
ftp.login(user, password)
ftp.prot_p()
ftp.set_pasv(True)

if remote_root not in ("", "."):
    ftp.cwd(remote_root)

ensured_dirs = set()


def ensure_remote_dir(dir_path: str) -> None:
    if not dir_path or dir_path in ensured_dirs:
        return
    parts = [p for p in dir_path.split("/") if p and p != "."]
    cur = ""
    for part in parts:
        cur = f"{cur}/{part}" if cur else part
        if cur in ensured_dirs:
            continue
        try:
            ftp.mkd(cur)
        except ftplib.error_perm:
            pass
        ensured_dirs.add(cur)


print(f"uploading {len(to_upload)} file(s)...")
for idx, (rel, full, digest) in enumerate(to_upload, 1):
    parent = posixpath.dirname(rel)
    if parent:
        ensure_remote_dir(parent)
    with open(full, "rb") as fh:
        ftp.storbinary(f"STOR {rel}", fh)
    cache[rel] = digest
    save_cache()
    print(f"  [{idx}/{len(to_upload)}] {rel}")

try:
    ftp.quit()
except Exception:
    ftp.close()

print("deploy complete!")
PY
