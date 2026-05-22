<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class LookupPolicy
{
    protected function isManager(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'asset_manager']);
    }

    public function viewAny(User $user): bool { return $this->isManager($user); }
    public function view(User $user, Model $record): bool { return $this->isManager($user); }
    public function create(User $user): bool { return $this->isManager($user); }
    public function update(User $user, Model $record): bool { return $this->isManager($user); }
    public function delete(User $user, Model $record): bool { return $this->isManager($user); }
    public function deleteAny(User $user): bool { return $this->isManager($user); }
    public function forceDelete(User $user, Model $record): bool { return $user->hasRole('admin'); }
    public function forceDeleteAny(User $user): bool { return $user->hasRole('admin'); }
    public function restore(User $user, Model $record): bool { return $this->isManager($user); }
    public function restoreAny(User $user): bool { return $this->isManager($user); }
    public function replicate(User $user, Model $record): bool { return $this->isManager($user); }
    public function reorder(User $user): bool { return $this->isManager($user); }
}
