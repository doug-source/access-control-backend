<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AbilityRole\CheckRequest;
use App\Library\Builders\Response as ResponseBuilder;
use App\Library\Converters\ResponseIndex;
use App\Services\Ability\Contracts\AbilityServiceInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response;

class AbilityRoleController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private AbilityServiceInterface $abilityService)
    {
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
        $role->abilities()->sync(
            $this->abilityService->combine(
                abilities: $role->abilities,
                namesToRemove: collect($request->input('removed', [])),
                namesToInclude: collect($request->input('included', [])),
            )->pluck('id')->all()
        );

        return ResponseBuilder::successJSON(
            status: Response::HTTP_NO_CONTENT
        );
    }
}
