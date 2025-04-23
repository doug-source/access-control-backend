<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class UserNotProvided implements ValidationRule
{
    /** @var string */
    protected $email;

    /** @var string[] */
    protected $providers;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($email, $providers)
    {
        $this->email = $email;
        $this->providers = $providers;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = User::where('email', $this->email)->first();
        if ($user && !$user->providers()->getResults()->isEmpty()) {
            $msg = Str::of(__('login-by-provider', [
                'log-in' => __('log-in'),
                'with' => __('with'),
                'provider' => implode(' ' . _('or') . ' ', $this->providers),
                'required' => __('required')
            ]))->ucfirst();
            $fail($msg);
        }
    }
}
