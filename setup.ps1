# LC Assets - Setup Script for Windows
# This script automates the full installation of the development environment.

$ErrorActionPreference = "Stop"

function Write-Step($message) {
    Write-Host "`n>> [STEP] $message" -ForegroundColor Cyan
}

function Write-Success($message) {
    Write-Host "`n✅ $message" -ForegroundColor Green
}

function Write-Failure($message) {
    Write-Host "`n❌ Error: $message" -ForegroundColor Red
    exit 1
}

Write-Step "Checking Environment Versions"
try {
    php -v | Select-Object -First 1
    composer -V
    npm -v
    node -v
} catch {
    Write-Failure "One or more required tools (PHP, Composer, Node/NPM) are missing from your PATH."
}

Write-Step "Configuring Environment File"
if (-not (Test-Path ".env")) {
    Write-Host "Creating .env from .env.example..."
    Copy-Item ".env.example" ".env"
} else {
    Write-Host ".env file already exists, skipping copy."
}

Write-Step "Installing PHP Dependencies (Composer)"
composer install
if ($LASTEXITCODE -ne 0) { Write-Failure "Composer install failed." }

Write-Step "Generating Application Key"
php artisan key:generate
if ($LASTEXITCODE -ne 0) { Write-Failure "Key generation failed." }

Write-Step "Installing Node Dependencies (NPM)"
npm install
if ($LASTEXITCODE -ne 0) { Write-Failure "NPM install failed." }

Write-Step "Setting up Database"
# Check if using SQLite and ensure file exists
$envContent = Get-Content ".env" -Raw
if ($envContent -match "DB_CONNECTION=sqlite") {
    $dbPath = "database/database.sqlite"
    if (-not (Test-Path $dbPath)) {
        Write-Host "Creating SQLite database file..."
        New-Item -Path $dbPath -ItemType File
    }
}

Write-Host "Running migrations and seeding..."
php artisan migrate:fresh --seed
if ($LASTEXITCODE -ne 0) { Write-Failure "Migration failed." }

Write-Step "Generating Filament Shield Permissions"
# We run this twice: once to generate and once to ensure the admin panel is selected
# Using 'echo 0' to automatically select the 'admin' panel if prompted
"0" | php artisan shield:generate --all
if ($LASTEXITCODE -ne 0) { Write-Failure "Shield generation failed." }

Write-Step "Creating Super Admin User"
$adminEmail = "admin@luxurycode.qa"
$adminPass = "password123"

$phpSetupScript = @"
<?php
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

require __DIR__.'/vendor/autoload.php';
`$app = require_once __DIR__.'/bootstrap/app.php';
`$kernel = `$app->make(Illuminate\Contracts\Console\Kernel::class);
`$kernel->bootstrap();

try {
    `$user = User::firstOrCreate(
        ['email' => '$adminEmail'],
        [
            'full_name' => 'Super Admin',
            'password' => Hash::make('$adminPass'),
            'role' => 'admin',
            'is_active' => true,
            'preferred_locale' => 'en',
        ]
    );
    `$role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    `$user->assignRole('super_admin');
    `$allPermissions = Permission::all();
    `$role->syncPermissions(`$allPermissions);
    echo '✅ Admin account and permissions finalized.';
} catch (\Exception `$e) {
    echo '❌ Error: ' . `$e->getMessage();
    exit(1);
}
"@

$phpSetupScript | Out-File -FilePath "temp_setup_admin.php" -Encoding UTF8
php temp_setup_admin.php
Remove-Item "temp_setup_admin.php"

if ($LASTEXITCODE -ne 0) { Write-Failure "Admin creation failed." }


Write-Step "Building Frontend Assets"
npm run build
if ($LASTEXITCODE -ne 0) { Write-Failure "NPM build failed." }

Write-Success "Setup Complete!"
Write-Host "--------------------------------------------------" -ForegroundColor Yellow
Write-Host "Admin URL: http://127.0.0.1:8000/admin" -ForegroundColor Yellow
Write-Host "User: $adminEmail" -ForegroundColor Yellow
Write-Host "Pass: $adminPass" -ForegroundColor Yellow
Write-Host "--------------------------------------------------" -ForegroundColor Yellow
Write-Host "Run 'composer dev' to start the development servers." -ForegroundColor Cyan
