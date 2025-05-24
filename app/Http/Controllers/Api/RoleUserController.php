<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests\RoleUser\CheckRequest;
use App\Library\Builders\Response as ResponseBuilder;
use App\Services\User\Contracts\RoleServiceInterface;

class RoleUserController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly RoleServiceInterface $roleService,
        private readonly UserRepository $userRepository,
    ) {
        // ...
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, User $user)
    {
        $this->authorize('viewAnyRole', $user);
        return $this->userRepository->paginateRoles(
            user: $user,
            perPage: $request->input('group', config('database.paginate.perPage')),
            name: $request->input('name'),
        );
    }

    /**
     * Update the user's roles including and removing some roles
     */
    public function update(CheckRequest $request)
    {
        $this->authorize('updateRole', User::class);
        $user = $request->input('user');
        $user->roles()->sync(
            $this->roleService->combine(
                roles: $user->roles,
                namesToRemove: collect($request->input('removed', [])),
                namesToInclude: collect($request->input('included', [])),
            )->pluck('id')->all()
        );

        return ResponseBuilder::successJSON(
            status: Response::HTTP_NO_CONTENT
        );
    }
}
