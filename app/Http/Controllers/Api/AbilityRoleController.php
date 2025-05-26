<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AbilityRole\CheckRequest;
use App\Library\Converters\ResponseIndex;
use App\Models\Role;
use App\Services\User\Contracts\AbilityServiceInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

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
    public function index(CheckRequest $request, Role $role)
    {
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
}
