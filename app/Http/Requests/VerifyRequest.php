<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

abstract class VerifyRequest extends FormRequest
{
    protected Checker $checker;
    protected CheckerFactoryScheme $factory;

    protected $stopOnFirstFailure = true;

    public function __construct(CheckerFactoryScheme $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public abstract function authorize(): bool;

    /**
     * Determine if the current user is logged in
     */
    protected function isLoggedIn(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return FALSE;
        }
        $tokenMethod = 'currentAccessToken';
        $token = $user->$tokenMethod();
        return $token !== null;
    }

    /**
     * Initialize the Checker protected property if necessary and return it.
     */
    protected function getChecker()
    {
        if (!isset($this->checker)) {
            $this->checker = $this->factory->getChecker($this);
        }
        return $this->checker;
    }

    public function all($keys = NULL)
    {
        $request = parent::all($keys);
        return $this->getChecker()->all($this, $request);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return $this->getChecker()->rules();
    }

    /**
     * {@inheritDoc}
     *
     * @return array<string, mixed>
     */
    public function messages()
    {
        return $this->getChecker()->messages();
    }
}
