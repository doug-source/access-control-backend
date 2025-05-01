<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest\CheckRequest;
use App\Models\RegisterRequest;
use App\Library\Builders\Response as ResponseBuilder;
use App\Library\Builders\Token as TokenBuilder;
use App\Library\Registration\{
    RegisterRequestHandler,
    PermissionRequestHandler
};
use App\Models\RegisterPermission;
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
     * Execute the register request instance's approval.
     */
    public function approve(CheckRequest $request)
    {
        $registerRequestID = $request->validated('registerRequestID');
        $registerRequest = RegisterRequest::find($registerRequestID);
        RegisterRequest::destroy($registerRequestID);
        $token = TokenBuilder::build();
        $expire = config('app.register.expire');
        $fields = [
            'email' => $registerRequest->email,
            'token' => $token,
            'expiration_data' => now()->addHours($expire)
        ];
        if ($registerRequest->phone) {
            $fields['phone'] = $registerRequest->phone;
        }
        RegisterPermission::create($fields);
        $this->registerService->sendApprovalMail($registerRequest->email, $token);

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
