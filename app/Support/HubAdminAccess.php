<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class HubAdminAccess
{
    public static function isAdmin(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return self::allowedEmails()->contains(strtolower($user->email));
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    public static function allowedEmails(): Collection
    {
        $emails = (string) env('VOLTRUNE_ADMIN_EMAILS', '');

        return collect(explode(',', $emails))
            ->map(static fn (string $email): string => strtolower(trim($email)))
            ->filter()
            ->values();
    }

    public static function applyClientCompaniesFilter(Builder $query): Builder
    {
        $adminEmails = self::allowedEmails();

        if ($adminEmails->isEmpty()) {
            return $query;
        }

        return $query->whereHas('users', function (Builder $userQuery) use ($adminEmails): void {
            $userQuery->whereNotIn('users.email', $adminEmails->all());
        });
    }
}
