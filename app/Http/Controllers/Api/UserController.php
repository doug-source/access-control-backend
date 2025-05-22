<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Library\Builders\Response as ResponseBuilder;
use App\Services\Register\Contracts\RegisterServiceInterface;
use App\Http\Requests\User\CheckRequest;
use App\Library\Converters\Phone as PhoneConverter;
use App\Models\User;
use App\Repositories\RegisterPermissionRepository;
use App\Repositories\UserRepository;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\{
    Request,
    Response
};

class UserController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly RegisterServiceInterface $registerService,
        private readonly RegisterPermissionRepository $permissionRepository,
        private readonly UserRepository $userRepository,
    ) {
        // ...
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);
        return $this->userRepository->paginate(
            perPage: $request->input('group', config('database.paginate.perPage')),
            name: $request->input('name')
        );
    }

    /**
     * Display one resource instance
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);
        return $user->ui;
    }

    /**
     * Remove the resource from database
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        $this->userRepository->delete($user->id);
        return ResponseBuilder::successJSON();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CheckRequest $request)
    {
        $registerPermission = $this->permissionRepository->findByEmail($request->email);
        $this->permissionRepository->delete($registerPermission->id);

        $phone = PhoneConverter::clear($registerPermission->phone ?? $request->phone);
        $fields = [...$request->only(['name', 'email', 'password']), 'phone' => $phone];
        $user = $this->userRepository->create(attributes: $fields);
        event(new Registered($user));
        // $this->applyDefaultUserRole($user);

        return ResponseBuilder::successJSON(
            status: Response::HTTP_CREATED
        );
    }
}
