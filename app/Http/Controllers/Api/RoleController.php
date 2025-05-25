<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\CheckRequest;
use App\Library\Builders\Response as ResponseBuilder;
use App\Library\Converters\ResponseIndex;
use App\Models\Role;
use App\Repositories\RoleRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RoleController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private RoleRepository $roleRepository)
    {
        // ...
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Role::class);
        $query = ResponseIndex::handleQuery($request, ['field' => 'name']);

        return $this->roleRepository->paginate(
            page: $query['page'],
            group: $query['group'],
            name: $query['name'],
        );
    }

    /**
     * Display one resource instance
     */
    public function show(Role $role)
    {
        $this->authorize('view', $role);
        return $role->ui;
    }

    /**
     * Persist one resource instance
     */
    public function store(CheckRequest $request)
    {
        $this->authorize('create', Role::class);
        $role = $this->roleRepository->create([
            'name' => $request->input('name')
        ]);
        return ResponseBuilder::successJSON(
            status: Response::HTTP_CREATED,
            headers: [
                'Location' => route('role.show', ['role' => $role->id])
            ]
        );
    }

    /**
     * Update the resource's data
     */
    public function update(CheckRequest $request, Role $role)
    {
        $this->authorize('update', $role);
        $name = $request->validated('name');
        $this->roleRepository->update($role->id, [
            'name' => $name
        ]);
        return ResponseBuilder::successJSON(
            status: Response::HTTP_NO_CONTENT
        );
    }

    /**
     * Remove the resource from database
     */
    public function destroy(CheckRequest $request, Role $role)
    {
        $this->authorize('delete', $role);
        $this->roleRepository->delete($role->id);
        return ResponseBuilder::successJSON();
    }
}
