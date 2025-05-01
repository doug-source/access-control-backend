<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest\CheckRequest;
use App\Library\Builders\Response as ResponseBuilder;
use App\Models\RegisterRequest;
use App\Library\Registration\{
    RegisterRequestHandler,
    PermissionRequestHandler
};
use App\Services\Register\RegisterServiceInterface;

class RegisterRequestsController extends Controller
{
    public function __construct(private readonly RegisterServiceInterface $registerService)
    {
        $this->registerService->setHandlers(
            new RegisterRequestHandler($this->registerService),
            new PermissionRequestHandler($this->registerService)
        );
    }

    /**
     * Display a listing of the resource.
     */
    public function index(CheckRequest $request)
    {
        return ResponseBuilder::successJSON(
            $this->searchRegisterRequests(
                perPage: $request->input('group', 3),
                email: $request->input('email')
            )
        );
    }

    /**
     * Execute the logic from register request form submit.
     */
    public function store(CheckRequest $request)
    {
        $email = $request->input('email');
        if (!$this->registerService->existsUserByEmail($email)) {
            $this->registerService->handleRegister($email, $request->input('phone'));
        }

        return ResponseBuilder::successJSON();
    }

    /**
     * Remove the specified register request instance.
     */
    public function destroy(CheckRequest $request)
    {
        RegisterRequest::destroy($request->validated('registerRequestID'));
        return ResponseBuilder::successJSON();
    }

    /**
     * Query the RegisterRequest instance list
     *
     * @return  \Illuminate\Database\Eloquent\Collection
     */
    protected function searchRegisterRequests($perPage = 3, ?string $email = NULL/* , $paginate = FALSE */)
    {
        $query = RegisterRequest::select('id', 'email', 'phone', 'created_at');
        if ($email) {
            $query = $query->where([
                ['email', 'like', "%{$email}%"]
            ]);
        }
        return $query->paginate($perPage);
    }
}
