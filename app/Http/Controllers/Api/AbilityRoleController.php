<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AbilityRole\CheckRequest;
use App\Library\Builders\Response as ResponseBuilder;
use App\Library\Converters\ResponseIndex;
use App\Services\Ability\Contracts\AbilityRoleServiceInterface;
use App\Services\Ability\Contracts\AbilityServiceInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response;

class AbilityRoleController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private AbilityServiceInterface $abilityService,
        private AbilityRoleServiceInterface $abilityRoleService,
    ) {
        // ...
    }

    /**
     * Display a listing of the resource.
     */
    public function index(CheckRequest $request)
    {
        $role = $request->input('role');
        $this->authorize('viewAnyAbility', $role);
        $query = ResponseIndex::handleQuery(
            $request,
            ['field' => 'owner', 'default' => 'yes'],
            ['field' => 'name'],
        );
        return $this->abilityService->findReferenceRoleAbilities(
            role: $role,
            owner: $query['owner'] === 'no' ? false : true,
            page: $query['page'],
            group: $query['group'],
            name: $query['name'],
        );
    }

    /**
     * Update the role's abilities including and removing some abilities
     */
    public function update(CheckRequest $request)
    {
        $role = $request->input('role');
        $this->authorize('updateAbility', $role);

        $namesToRemove = collect($request->input('removed', []));
        $namesToInclude = collect($request->input('included', []));
        $usersFromRole = $role->users;

        $this->abilityRoleService->handleRoleAbilityInclusion(
            usersFromRole: $usersFromRole,
            namesToInclude: $namesToInclude,
        );
        $this->abilityRoleService->handleRoleAbilityRemotion(
            usersFromRole: $usersFromRole,
            namesToRemove: $namesToRemove,
            role: $role,
        );
        $this->abilityService->updateRoleAbilities(
            role: $role,
            namesToRemove: $namesToRemove,
            namesToInclude: $namesToInclude,
        );

        return ResponseBuilder::successJSON(
            status: Response::HTTP_NO_CONTENT
        );
    }
}
