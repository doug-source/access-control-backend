<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest\CheckRequest;
use App\Models\RegisterRequest;
use App\Library\Builders\Response as ResponseBuilder;
use App\Library\Builders\Token as TokenBuilder;
use App\Library\Converters\ResponseIndex;
use App\Library\Registration\{
    RegisterRequestHandler,
    PermissionRequestHandler
};
use App\Models\RegisterPermission;
use App\Repositories\RegisterPermissionRepository;
use App\Repositories\RegisterRequestRepository;
use App\Services\Register\Contracts\RegisterServiceInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response;

class RegisterRequestsController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly RegisterServiceInterface $registerService,
        private readonly RegisterPermissionRepository $permissionRepository,
        private readonly RegisterRequestRepository $regRequestRepository,
    ) {
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
        $this->authorize('viewAny', RegisterRequest::class);
        $query = ResponseIndex::handleQuery(
            $request,
            ['field' => 'email'],
        );

        return ResponseBuilder::successJSON(
            data: $this->regRequestRepository->paginate(
                page: $query['page'],
                group: $query['group'],
                email: $query['email'],
            )
        );
    }

    /**
     * Display one RegisterRequest instance
     */
    public function show(CheckRequest $request)
    {
        $registerRequest = $this->regRequestRepository->find($request->validated('registerRequestID'));
        $this->authorize('view', $registerRequest);
        return ResponseBuilder::successJSON(
            data: [
                'id' => $registerRequest->id,
                'email' => $registerRequest->email,
                'phone' => $registerRequest->phone,
                'createdAt' => $registerRequest->created_at_formatted,
                'updatedAt' => $registerRequest->updated_at_formatted,
            ]
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

        return ResponseBuilder::successJSON(
            status: Response::HTTP_CREATED
        );
    }

    /**
     * Remove the specified register request instance.
     */
    public function destroy(CheckRequest $request)
    {
        $this->authorize('delete', RegisterRequest::class);
        $this->regRequestRepository->delete($request->validated('registerRequestID'));
        return ResponseBuilder::successJSON();
    }

    /**
     * Execute the register request instance's approval.
     */
    public function approve(CheckRequest $request)
    {
        $registerRequestID = $request->validated('registerRequestID');
        $registerRequest = $this->regRequestRepository->find($registerRequestID);
        $this->authorize('delete', RegisterRequest::class);
        $this->regRequestRepository->delete($registerRequestID);
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
        $this->authorize('create', RegisterPermission::class);
        $this->permissionRepository->create(attributes: $fields);
        $this->registerService->sendApprovalMail($registerRequest->email, $token);

        return ResponseBuilder::successJSON();
    }
}
