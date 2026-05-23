<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransactionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_transaction');
    }

    public function view(User $user, Transaction $transaction): bool
    {
        return $user->can('view_transaction');
    }

    public function create(User $user): bool
    {
        return $user->can('create_transaction');
    }

    public function update(User $user, Transaction $transaction): bool
    {
        return $user->can('update_transaction');
    }

    public function delete(User $user, Transaction $transaction): bool
    {
        return $user->can('delete_transaction');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_transaction');
    }

    public function forceDelete(User $user, Transaction $transaction): bool
    {
        return $user->can('force_delete_transaction');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_transaction');
    }

    public function restore(User $user, Transaction $transaction): bool
    {
        return $user->can('restore_transaction');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_transaction');
    }

    public function replicate(User $user, Transaction $transaction): bool
    {
        return $user->can('replicate_transaction');
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder_transaction');
    }
}
