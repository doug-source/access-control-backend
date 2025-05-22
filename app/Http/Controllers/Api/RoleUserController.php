<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class RoleUserController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
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
}
