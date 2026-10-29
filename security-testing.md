## 1. Instant Free Online Checkers (Run on your live IONOS URL — Zero Install)

Once your updated files and `.htaccess` are uploaded to IONOS, paste your domain into these free sites:

* **Mozilla Observatory (`developer.mozilla.org/en-US/observatory`) & SecurityHeaders.com**
    * **What they test:** Check your Content-Security-Policy, Strict-Transport-Security (HSTS), X-Frame-Options, X-Content-Type-Options, and Referrer-Policy headers (`includes/security.php` + `.htaccess`) and grade your site from F to A+.
* **Qualys SSL Labs (`ssllabs.com/ssltest`)**
    * **What it tests:** Deep-checks your IONOS HTTPS/TLS certificate, encryption strength, and HTTPS redirect setup.
* **CSP Evaluator by Google (`csp-evaluator.withgoogle.com`)**
    * **What it tests:** Paste your Content-Security-Policy header and it highlights if any directive could let an attacker sneak in a rogue script.

---

## 2. Free Code & Secret Scanners (Run on your Mac / GitHub)

* **GitHub Secret Scanning & Push Protection** (Built into GitHub — turn ON in **Repo → Settings → Code security**)
    * **What it does:** Automatically blocks `git push` if you ever accidentally try to push a Stripe key (`sk_live_...`), database password, or API token.
* **Semgrep CLI** (Free / Open Source — `brew install semgrep`)
    * **What it does:** Scans all your `.php` and `.js` files right in PhpStorm's terminal (`semgrep scan --config auto`) looking for SQL injection, XSS (unescaped output), missing CSRF checks, or unsafe PHP functions.
* **Gitleaks** (`brew install gitleaks` → `gitleaks detect`)
    * **What it does:** Scans your entire Git commit history on your Mac to verify zero passwords or keys were ever committed in old commits.
* **composer audit** (Built into Composer — run `composer audit` in terminal)
    * **What it does:** Checks `composer.lock` against the official PHP Security Advisories database to make sure `vlucas/phpdotenv` (or any package you add later) has zero known CVEs.

---

## 3. Free Active Testing Tools (Run against `http://localhost:8000` on your Mac!)

> **Tip:** Always run active vulnerability scanners against `http://localhost:8000` on your Mac rather than firing thousands of attack requests at IONOS shared hosting. IONOS's firewall may temporarily block your home Wi-Fi IP if it thinks you're attacking their server!

* **OWASP ZAP** (`zaproxy.org` — Free Desktop App for Mac)
    * **What it does:** The industry-standard free web penetration-testing tool. Point its Automated Scan at `http://localhost:8000` and it crawls your pages trying XSS payloads, fake form submissions, cookie tampering, directory traversal, and open-redirect tricks, then gives you a plain-English report.
* **Chrome / Safari DevTools** (Built into your browser — 30-second manual check)
    * **Application → Cookies:** Click `lbb_sid` and verify `HttpOnly` ✓ and `SameSite: Lax` are ticked (and `Secure` ✓ when on HTTPS).
    * **URL bar test:** Once on IONOS, try visiting `https://yourdomain.co.uk/.env`, `https://yourdomain.co.uk/config.php`, and `https://yourdomain.co.uk/includes/security.php` — your `.htaccess` rules should block `.env` / `/includes` with `403 Forbidden` (and `config.php` prints nothing).