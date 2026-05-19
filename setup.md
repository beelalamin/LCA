# Laravel & Filament Production Deployment Guide

### 1. CloudPanel & DNS Preparation

- **DNS Records:** Create an IPv4 `A` record for your subdomain (`lc`).
- **Clean DNS:** Delete all `AAAA` (IPv6) records for the subdomain. If using Cloudflare, temporarily disable proxies (grey cloud) for initial SSL verification.
- **Install SSL:** In CloudPanel → **Sites** → `lc.addainventoryhub.com` → **SSL/TLS** → Click **Create and Install Let's Encrypt**.
- **Update Document Root:** In CloudPanel → **Vhost**. Ensure the webserver root targets the Laravel `/public` subdirectory:

```nginx
root /home/addainventoryhub-lc/htdocs/lc.addainventoryhub.com/public;

```

- **Nginx Livewire & Asset Fix:** In the same Vhost file, locate the static assets caching regex block (`location ~* ^.+\.(css|js...)$`) and inject the Laravel routing fallback to prevent Filament and Livewire asset `(failed)` or `404` errors:

```nginx
location ~* ^.+\.(css|js|jpg|jpeg|gif|png|ico|gz|svg|svgz|ttf|otf|woff|woff2|eot|mp4|ogg|ogv|webm|webp|zip|swf|map)$ {
    try_files $uri $uri/ /index.php?$query_string; # <--- INJECTED TO PREVENT ASSET BREAKS
    add_header Access-Control-Allow-Origin "*";
    expires max;
    access_log off;
}

```

- **Secure FastCGI Environment:** Inside the `location ~ \.php$` block running on port `8080`, ensure HTTPS params are explicitly declared to prevent endless redirect loops:

```nginx
fastcgi_param HTTPS "on";
fastcgi_param SERVER_PORT 443;

```

---

### 2. Laravel Codebase Preparation

Apply these foundational configuration changes locally and commit them to your `main` production branch.

- **Trust Nginx Proxy** (`bootstrap/app.php`):

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->trustProxies(at: '*');
})

```

- **Force HTTPS Assets** (`app/Providers/AppServiceProvider.php`):

```php
public function boot(): void
{
    if (app()->environment('production')) {
        \Illuminate\Support\Facades\URL::forceScheme('https');
    }
}

```

- **Authorize Production Access** (`app/Models/User.php`):

```php
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    public function canAccessPanel(Panel $panel): bool
    {
        return true; // Use role or explicit logic check later
    }
}

```

---

### 3. Server Configuration & One-Time Framework Setup

SSH into your CloudPanel server using the dedicated user profile to configure runtime states.

**A. Configure `.env**`

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://lc.addainventoryhub.com
ASSET_URL=https://lc.addainventoryhub.com
SESSION_SECURE_COOKIE=true

```

**B. Initial Folder Architecture & Runtime Permissions**

```bash
cd /home/addainventoryhub-lc/htdocs/lc.addainventoryhub.com

# 1. Force establish framework caches & missing bootstrap folders
mkdir -p storage/framework/{cache,sessions,testing,views}
mkdir -p storage/app/public
mkdir -p storage/logs
mkdir -p bootstrap/cache

# 2. Reset exact user file ownership and production permission flags
chown -R addainventoryhub-lc:addainventoryhub-lc /home/addainventoryhub-lc/htdocs/lc.addainventoryhub.com
chmod -R 775 storage bootstrap/cache

```

**C. Initial Database, Assets, & Filament Setup**
Always specify the exact CLI binary `php8.3` version to bypass global engine package version updates.

```bash
# 1. Install production dependencies
php8.3 /usr/local/bin/composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# 2. Setup key and database schemas cleanly
php8.3 artisan key:generate
php8.3 artisan migrate:fresh --force

# 3. Publish and repair core Filament framework symlinks
php8.3 artisan storage:link
php8.3 artisan vendor:publish --force --tag=livewire:assets
php8.3 artisan filament:assets

# 4. Generate the master user securely via Tinker shell
php8.3 artisan tinker

```

Inside the interactive **Tinker** terminal, execute the array mapping (handles database models using custom `full_name` requirements safely):

```php
\App\Models\User::create([
    'full_name' => 'Admin User',
    'email' => 'admin@addainventoryhub.com',
    'password' => Hash::make('your-secure-password-here'),
    'role' => 'admin',
    'is_active' => true
]);

```

_(Type `exit` and hit Enter to close the tinker shell)._

**D. Roles & Permissions Generation (Filament Shield)**
Run these to build permissions matrices and grant full dashboard execution rights to your admin user row:

```bash
# Build standard permission shields
php8.3 artisan shield:generate --all

# Supercharge the user profile with total admin privileges
php8.3 artisan shield:super-admin
# (Enter 'admin@addainventoryhub.com' when prompted by the utility)

```

---

### 4. CI/CD Pipeline (GitHub Actions)

Add repository deployment keys under GitHub Repository Secrets: `SSH_HOST`, `SSH_PORT` (e.g. 22), `SSH_USERNAME` (`addainventoryhub-lc`), and `SSH_KEY` (Private SSH Key).

Update your pipeline workflow file at `.github/workflows/deploy.yml`:

```yaml
name: Deploy LC Assets

on:
    push:
        branches: [main]

jobs:
    deploy:
        runs-on: ubuntu-latest
        steps:
            - uses: actions/checkout@v4
            - uses: actions/setup-node@v4
              with: { node-version: 20 }

            - name: Build frontend
              run: npm install && npm run build

            - name: Sync files
              uses: burnett01/rsync-deployments@5.2
              with:
                  switches: -avzr --delete --omit-dir-times --no-perms --exclude='.env' --exclude='storage/' --exclude='bootstrap/cache/' --exclude='vendor/' --exclude='.git/' --exclude='.github/' --exclude='node_modules/'
                  path: ./
                  remote_path: /home/addainventoryhub-lc/htdocs/lc.addainventoryhub.com
                  remote_host: ${{ secrets.SSH_HOST }}
                  remote_user: ${{ secrets.SSH_USERNAME }}
                  remote_key: ${{ secrets.SSH_KEY }}
                  remote_port: ${{ secrets.SSH_PORT }}

            - name: Server Post-Deploy
              uses: appleboy/ssh-action@v1.0.3
              with:
                  host: ${{ secrets.SSH_HOST }}
                  username: ${{ secrets.SSH_USERNAME }}
                  port: ${{ secrets.SSH_PORT }}
                  key: ${{ secrets.SSH_KEY }}
                  script: |
                      cd /home/addainventoryhub-lc/htdocs/lc.addainventoryhub.com
                      php8.3 /usr/local/bin/composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
                      php8.3 artisan optimize:clear
                      php8.3 artisan config:cache
                      php8.3 artisan route:cache
                      php8.3 artisan view:cache
                      php8.3 artisan filament:assets
                      php8.3 artisan migrate --force
                      sudo supervisorctl restart laravel-worker:* || true
                      chmod -R 775 storage bootstrap/cache
                      sudo systemctl restart php8.3-fpm || true
```

---

### 5. Manual Hard Reset & Cache Clear

Run this clean sequence if server states drift, `.env` definitions are modified manually, or if elements throw a sudden 500 error:

```bash
cd /home/addainventoryhub-lc/htdocs/lc.addainventoryhub.com
php8.3 artisan optimize:clear
php8.3 artisan config:cache
php8.3 artisan route:cache
php8.3 artisan view:cache
php8.3 artisan filament:assets
sudo systemctl restart nginx
sudo systemctl restart php8.3-fpm

```
