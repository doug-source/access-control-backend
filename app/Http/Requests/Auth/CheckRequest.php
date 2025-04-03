<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Checker;
use App\Http\Requests\Auth\Strategy\CheckerFactory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CheckRequest extends FormRequest
{
    /** @var App\Http\Requests\Checker */
    private Checker $checker;

    /**
     * Indicates if the validator should stop on the first rule failure.
     *
     * @var bool
     */
    protected $stopOnFirstFailure = true;

    /**
     * Initialize the Checker private property if necessary and return it.
     *
     * @return App\Http\Requests\Checker
     */
    private function getChecker()
    {
        if (!isset($this->checker)) {
            $this->checker = CheckerFactory::getChecker($this);
        }
        return $this->checker;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return !Auth::check();
        // return !auth()->check();
    }

    /**
     * Used by access the url argument 'customerID' (if present) included
     * on the url to validate it
     *
     * @param array|mixed|null $keys
     * @return array
     */
    public function all($keys = NULL)
    {
        $request = parent::all($keys);
        return $this->checker->all($this, $request);
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
     * Get the validation messages that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function messages()
    {
        return $this->getChecker()->messages();
    }
}
