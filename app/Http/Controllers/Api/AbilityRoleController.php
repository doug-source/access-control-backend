<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Repositories\RoleRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class AbilityRoleController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly RoleRepository $roleRepository,
    ) {
        // ...
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Role $role)
    {
        $this->authorize('viewAnyAbility', $role);
        return $this->roleRepository->paginateAbilities(
            role: $role,
            perPage: $request->input('group', config('database.paginate.perPage')),
            name: $request->input('name'),
        );
    }
}
