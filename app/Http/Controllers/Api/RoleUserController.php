<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response;
use App\Http\Requests\RoleUser\CheckRequest;
use App\Library\Builders\Response as ResponseBuilder;
use App\Library\Converters\ResponseIndex;
use App\Services\Ability\Contracts\AbilityServiceInterface;
use App\Services\Ability\Contracts\AbilityUserServiceInterface;
use App\Services\Role\Contracts\RoleServiceInterface;

class RoleUserController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private RoleServiceInterface $roleService,
        private AbilityServiceInterface $abilityService,
        private AbilityUserServiceInterface $abilityUserService,
    ) {
        // ...
    }

    /**
     * Display a listing of the resource.
     */
    public function index(CheckRequest $request)
    {
        $user = $request->input('user');
        $this->authorize('viewAnyRole', $user);
        $query = ResponseIndex::handleQuery(
            $request,
            ['field' => 'owner', 'default' => 'yes'],
            ['field' => 'name'],
        );
        return $this->roleService->findReferenceRoles(
            user: $user,
            owner: $query['owner'] === 'no' ? false : true,
            page: $query['page'],
            group: $query['group'],
            name: $query['name'],
        );
    }

    /**
     * Update the user's roles including and removing some roles
     */
    public function update(CheckRequest $request)
    {
        $this->authorize('updateRole', User::class);
        /** @var \App\Models\User */
        $user = $request->input('user');
        /** @var \Illuminate\Support\Collection<int, \App\Models\Role> */
        $rolesFromUser = $request->input('rolesFromUser');

        $namesToRemove = collect($request->input('removed', []));
        $namesToInclude = collect($request->input('included', []));

        $this->roleService->handleUserRoleInsertion(
            $user,
            $rolesFromUser,
            $namesToInclude,
            $this->abilityService,
            $this->abilityUserService
        );
        $this->roleService->handleUserRoleRemotion(
            $user,
            $rolesFromUser,
            $namesToRemove,
            $this->abilityService,
            $this->abilityUserService
        );
        $this->roleService->updateUserRoles(
            user: $user,
            namesToRemove: $namesToRemove,
            namesToInclude: $namesToInclude,
        );

        return ResponseBuilder::successJSON(
            status: Response::HTTP_NO_CONTENT
        );
    }
}
