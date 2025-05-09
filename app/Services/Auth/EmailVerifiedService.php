<?php

namespace App\Services\Auth;

use App\Services\Auth\Contracts\EmailVerifiedServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class EmailVerifiedService implements EmailVerifiedServiceInterface
{
    public function userHasEmailVerified(): bool
    {
        $user = Auth::user();
        $emailVerifiedAt = $user?->email_verified_at;

        return !is_null($emailVerifiedAt) && $this->isEmailVerifiedAtDateOlderThenNow($emailVerifiedAt);
    }

    /**
     * Define if the user's email_verified_at is older than today's datetime
     */
    private function isEmailVerifiedAtDateOlderThenNow(string $emailVerifiedAt): bool
    {
        return Carbon::now()->greaterThan(Carbon::parse($emailVerifiedAt));
    }
}
