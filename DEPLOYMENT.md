# Deploying Lab Manager to Namecheap Shared Hosting (Stellar)

Setup used: **subdomain** (e.g. `app.yourdomain.com`) + **cPanel Git Version Control**
pulling from `https://github.com/ShehrrYarr/SaaS-LMS`.

---

## 0. One-time prerequisites (cPanel)

1. **PHP version** — cPanel → *Select PHP Version* → choose **PHP 8.2** (or newer).
   In *Extensions*, make sure these are ticked: `pdo_mysql`, `mbstring`, `openssl`,
   `tokenizer`, `xml`, `ctype`, `fileinfo`, `gd`, `zip`, `curl`.
2. **SSH access** — cPanel → *Manage Shell* → enable. You'll need it for
   `composer install` and artisan commands. Connect with:
   `ssh <cpanel-user>@<server> -p 21098` (Namecheap's SSH port is 21098).
3. **MySQL** — cPanel → *MySQL® Databases*:
   - Create a database (e.g. `<user>_lms`).
   - Create a DB user with a strong password.
   - Add the user to the database with **All Privileges**.

## 1. Clone the repo via cPanel Git

1. cPanel → **Git™ Version Control** → *Create*.
2. Clone URL: `https://github.com/ShehrrYarr/SaaS-LMS.git`
   (if the repo is private, either make it public temporarily or add the
   cPanel-generated SSH key as a **Deploy key** in GitHub → repo → Settings →
   Deploy keys, and use the SSH clone URL).
3. Repository path: `/home/<cpanel-user>/lms`  ← outside public_html on purpose.
4. Create. cPanel clones the repo.

**Future updates:** push to GitHub locally, then cPanel → Git Version Control →
*Manage* → **Pull or Deploy** → *Update from Remote*.

## 2. Point the subdomain at Laravel's /public

1. cPanel → **Domains** (or *Subdomains*) → create `app.yourdomain.com`.
2. Set its **Document Root** to: `/home/<cpanel-user>/lms/public`
3. Enable **AutoSSL** (cPanel → SSL/TLS Status → Run AutoSSL) so the subdomain
   gets a free HTTPS certificate.

## 3. Server-side setup (SSH, one time)

```bash
ssh <cpanel-user>@<server> -p 21098
cd ~/lms

# 3a. PHP dependencies (no dev packages)
/usr/local/bin/ea-php82 /opt/cpanel/composer/bin/composer install --no-dev --optimize-autoloader
#   If plain `composer` works on your server, simply:
#   composer install --no-dev --optimize-autoloader

# 3b. Environment file
cp env.production.template .env
nano .env        # fill DB credentials, APP_URL, and paste APP_KEY from local .env

# 3c. Storage symlink (report logos, signatures, result files)
php artisan storage:link

# 3d. Database — pick ONE:
# Fresh install (creates tables + superadmin/plans/demo tenant):
php artisan migrate --force
php artisan db:seed --force
# ...or import your local data instead: export locally with
#   mysqldump -u root lms > lms.sql
# then import via cPanel -> phpMyAdmin -> Import.

# 3e. Cache config/routes/views for speed
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

> If `php` on the shell is an old version, use the full binary name
> `ea-php82` wherever the guide says `php`.

## 4. Cron jobs (cPanel → Cron Jobs)

Add these two entries (adjust the PHP binary if needed):

| Schedule | Command |
|---|---|
| `* * * * *` (every minute) | `cd /home/<cpanel-user>/lms && /usr/local/bin/ea-php82 artisan schedule:run >> /dev/null 2>&1` |
| `* * * * *` (every minute) | `cd /home/<cpanel-user>/lms && /usr/local/bin/ea-php82 artisan queue:work --stop-when-empty --max-time=50 >> /dev/null 2>&1` |

The second one processes the email jobs (report-ready notifications, patient
credentials) from the `database` queue without needing a daemon — it drains the
queue and exits, every minute.

## 5. Verify

1. `https://app.yourdomain.com/` → landing page loads over HTTPS.
2. `/superadmin/login` → sign in (`admin@labmanager.com` / seeded password),
   **immediately change the password** in Settings.
3. Create a real lab, log into it, upload a logo (tests the storage symlink),
   create a test order, download the PDF report.
4. Configure the lab's SMTP in Settings and use *Test Connection*.

## Updating the app later

```text
local:  git push
cPanel: Git Version Control -> Pull or Deploy -> Update from Remote
SSH:    cd ~/lms && php artisan migrate --force && php artisan config:cache && php artisan view:cache
```

`public/build` (compiled CSS/JS) is committed to the repo, so a pull updates
the frontend too — no Node needed on the server. Rebuild locally with
`npm run build` and commit whenever styles/JS change.

## Gotchas specific to this app

- **APP_KEY is sacred.** It encrypts every stored recoverable password
  (lab admins, branches, superadmin). Copy it from your local `.env`; never
  regenerate it on the server, and back it up somewhere safe.
- **Don't run `db:seed` twice** with real data present — it's mostly
  `firstOrCreate`, but keep seeding to the first deploy.
- If you later see a blank page or 500: check `storage/logs/laravel.log`, and
  re-run `php artisan config:cache` after any `.env` edit (cached config wins).
- File upload limits (result PDFs up to 5 MB) — if uploads fail, raise
  `upload_max_filesize` and `post_max_size` in cPanel → *MultiPHP INI Editor*.
