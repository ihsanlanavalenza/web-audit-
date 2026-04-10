# Production Checklist

For remote GitHub deployment setup, see [GITHUB_REMOTE_DEPLOY.md](GITHUB_REMOTE_DEPLOY.md).

## 1. Security First

- Rotate all secrets before go-live:
  - `APP_KEY`
  - `DB_PASSWORD`
  - `MAIL_PASSWORD` (Gmail App Password)
  - `GOOGLE_CLIENT_SECRET`
- Do not commit production secrets to git.
- Use a dedicated DB user (do not use `root`).

## 2. Environment

Set production values in server environment:

- `PHP >= 8.4` (pastikan versi domain di cPanel sudah 8.4)
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://your-domain.com`
- `DB_CONNECTION=mysql`
- `DB_HOST=localhost`
- `DB_PORT=3306`
- `DB_DATABASE=auditinm_webaudit`
- `DB_USERNAME=auditinm_dbuser`
- `DB_PASSWORD=<set-on-server-only>`
- `MAIL_MAILER=smtp`
- `QUEUE_CONNECTION=database` (or redis)

Then run:

```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 3. Database and Storage

```bash
php artisan migrate --force
php artisan storage:link
```

## 4. Queue Worker (Required)

Notifications are queued. Ensure worker is running continuously.

Example command:

```bash
php artisan queue:work --queue=mail,default --tries=5 --backoff=60 --timeout=120
```

If using VPS/dedicated server, use Supervisor/systemd to keep worker always on.

If using cPanel shared hosting, use Cron Job fallback:

```bash
* * * * * /usr/local/bin/php /home/auditinm/web-audit-system-/artisan queue:work --queue=mail,default --tries=5 --backoff=60 --timeout=120 --stop-when-empty >> /dev/null 2>&1
```

## 5. Smoke Tests

- Login with email/password
- Login with Google OAuth
- Send invitation email
- Accept invitation and verify email notifications
- Upload multi-file in Data Request and verify status + notifications

## 6. Monitoring

- Watch app logs (`storage/logs/laravel.log`)
- Monitor failed jobs:

```bash
php artisan queue:failed
php artisan queue:retry all
```
