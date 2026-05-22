<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->ensureBaselineRolesExist();

        if (Schema::hasColumn('users', 'role')) {
            $this->backfillRoleAssignments();

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->nullable()->after('email');
            });
        }
    }

    protected function ensureBaselineRolesExist(): void
    {
        $now = now();
        $baseline = ['admin', 'asset_manager', 'user'];

        foreach ($baseline as $name) {
            $exists = DB::table('roles')
                ->where('name', $name)
                ->where('guard_name', 'web')
                ->exists();

            if (! $exists) {
                DB::table('roles')->insert([
                    'name' => $name,
                    'guard_name' => 'web',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    protected function backfillRoleAssignments(): void
    {
        $roleMap = DB::table('roles')
            ->where('guard_name', 'web')
            ->pluck('id', 'name');

        DB::table('users')->orderBy('id')->each(function ($user) use ($roleMap) {
            $legacy = strtolower((string) ($user->role ?? ''));

            $roleName = match ($legacy) {
                'admin' => 'admin',
                'technician', 'asset_manager' => 'asset_manager',
                default => $legacy ?: 'user',
            };

            $roleId = $roleMap[$roleName] ?? $roleMap['user'] ?? null;

            if (! $roleId) {
                return;
            }

            $already = DB::table('model_has_roles')
                ->where('role_id', $roleId)
                ->where('model_id', $user->id)
                ->where('model_type', \App\Models\User::class)
                ->exists();

            if (! $already) {
                DB::table('model_has_roles')->insert([
                    'role_id' => $roleId,
                    'model_id' => $user->id,
                    'model_type' => \App\Models\User::class,
                ]);
            }
        });
    }
};
