<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class TransactionObserver
{
    public function creating(Transaction $transaction): void
    {
        if (empty($transaction->transaction_number)) {
            $transaction->transaction_number = static::generateTransactionNumber();
        }

        if ($transaction->user_id) {
            $user = $transaction->user()->first();
            if ($user) {
                if (empty($transaction->department_id) && $user->department_id) {
                    $transaction->department_id = $user->department_id;
                }
                if (empty($transaction->office_location_id) && $user->office_location_id) {
                    $transaction->office_location_id = $user->office_location_id;
                }
            }
        }
    }

    public function created(Transaction $transaction): void
    {
        $this->mirrorToAsset($transaction);

        AuditLogger::log(
            match ($transaction->type) {
                Transaction::TYPE_REGISTERED => 'REGISTERED',
                Transaction::TYPE_CHECK_IN => 'CHECKED_IN',
                default => 'CHECKED_OUT',
            },
            $transaction,
            null,
            $transaction->toArray()
        );
    }

    public function updated(Transaction $transaction): void
    {
        $this->mirrorToAsset($transaction);

        if ($transaction->isDirty('checked_in_at') && $transaction->checked_in_at !== null) {
            AuditLogger::log('CHECKED_IN', $transaction, ['type' => $transaction->getOriginal('type')], ['type' => $transaction->type]);
        }
    }

    public function deleted(Transaction $transaction): void {}

    public function restored(Transaction $transaction): void {}

    public function forceDeleted(Transaction $transaction): void {}

    protected function mirrorToAsset(Transaction $transaction): void
    {
        if ($transaction->type === Transaction::TYPE_REGISTERED) {
            return;
        }

        $asset = $transaction->asset;

        if (! $asset) {
            return;
        }

        $isCheckOut = $transaction->type === Transaction::TYPE_CHECK_OUT;

        $update = [
            'assigned_to_user_id' => $isCheckOut ? $transaction->user_id : null,
            'assignment_status_id' => $transaction->assignment_status_id,
            'assigned_date' => $transaction->checked_out_at?->toDateString(),
            'return_date' => $transaction->checked_in_at?->toDateString(),
            'return_reason_id' => $transaction->return_reason_id,
        ];

        $optional = array_filter([
            'maintenance_status_id' => $transaction->maintenance_status_id,
            'maintenance_type_id' => $transaction->maintenance_type_id,
            'warranty_provider_id' => $transaction->warranty_provider_id,
        ], fn ($v) => $v !== null);

        $asset->forceFill(array_merge($update, $optional))->saveQuietly();
    }

    protected static function generateTransactionNumber(): string
    {
        $year = date('Y');
        $count = DB::table('transactions')
            ->where('transaction_number', 'like', "TXN-{$year}-%")
            ->count();
        $sequence = str_pad((string) ($count + 1), 5, '0', STR_PAD_LEFT);
        return "TXN-{$year}-{$sequence}";
    }
}
