<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response;
use App\Http\Requests\RoleUser\CheckRequest;
use App\Library\Builders\Response as ResponseBuilder;
use App\Library\Converters\ResponseIndex;
use App\Services\Role\Contracts\RoleServiceInterface;

class RoleUserController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private RoleServiceInterface $roleService)
    {
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
