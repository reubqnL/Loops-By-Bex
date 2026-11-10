#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

if [[ ! -f .env ]]; then
    echo "error: local .env file not found."
    exit 1
fi

deploy_user=""
deploy_host=""
deploy_path="."

while IFS='=' read -r raw_key raw_val || [[ -n "${raw_key:-}" ]]; do
    key="$(echo "${raw_key:-}" | xargs)"
    [[ -z "$key" || "$key" == \#* ]] && continue
    val="${raw_val:-}"
    val="${val%\"}"
    val="${val#\"}"
    val="${val%\'}"
    val="${val#\'}"
    case "$key" in
        DEPLOY_USER) deploy_user="$val" ;;
        DEPLOY_HOST) deploy_host="$val" ;;
        DEPLOY_PATH) deploy_path="$val" ;;
    esac
done < .env

if [[ -z "$deploy_user" || -z "$deploy_host" ]]; then
    echo "error: add DEPLOY_USER, DEPLOY_HOST, and DEPLOY_PATH to your local .env first."
    exit 1
fi

target_dir="$deploy_path"
if [[ "$target_dir" != "/" ]]; then
    target_dir="${target_dir%/}/"
fi

rsync_cmd=(rsync -avz)
if [[ "${1:-}" == "--dry-run" || "${1:-}" == "-n" ]]; then
    rsync_cmd+=(--dry-run)
    echo "running dry-run preview (no files will be uploaded)..."
fi

"${rsync_cmd[@]}" \
    --exclude='.git/' \
    --exclude='.gitattributes' \
    --exclude='.gitignore' \
    --exclude='.idea/' \
    --exclude='.vscode/' \
    --exclude='.DS_Store' \
    --exclude='.env' \
    --exclude='.env.*' \
    --exclude='*.env' \
    --exclude='includes/env.php' \
    --exclude='deploy.sh' \
    --exclude='router.php' \
    --exclude='database/' \
    --exclude='vendor/' \
    --exclude='node_modules/' \
    --exclude='storage/*.sqlite' \
    --exclude='storage/*.sqlite3' \
    --exclude='storage/*.db' \
    --exclude='storage/*.log' \
    --exclude='style/*.scss' \
    --exclude='style/*.map' \
    --exclude='assets/*.jpg' \
    --exclude='assets/*.jpeg' \
    --exclude='assets/wireframe.png' \
    --exclude='composer.json' \
    --exclude='composer.lock' \
    --exclude='LICENSE' \
    --exclude='*.md' \
    ./ "${deploy_user}@${deploy_host}:${target_dir}"

echo "done."
