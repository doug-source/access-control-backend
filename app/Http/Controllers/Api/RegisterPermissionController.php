<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterPermission\CheckRequest;
use App\Library\Builders\Response as ResponseBuilder;
use App\Models\RegisterPermission;
use App\Repositories\RegisterPermissionRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class RegisterPermissionController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly RegisterPermissionRepository $repository)
    {
        // ...
    }

    /**
     * Display a listing of the resource.
     */
    public function index(CheckRequest $request)
    {
        $this->authorize('viewAny', RegisterPermission::class);
        return ResponseBuilder::successJSON(
            data: $this->repository->paginate(
                perPage: $request->input('group', config('database.paginate.perPage')),
                email: $request->input('email'),
            )
        );
    }

    /**
     * Display one RegisterPermission instance
     */
    public function show(CheckRequest $request)
    {
        $registerPermission = $this->repository->find($request->validated('registerPermissionID'));
        $this->authorize('view', $registerPermission);
        return ResponseBuilder::successJSON(
            data: [
                'id' => $registerPermission->id,
                'email' => $registerPermission->email,
                'phone' => $registerPermission->phone,
                'createdAt' => $registerPermission->created_at_formatted,
                'updatedAt' => $registerPermission->updated_at_formatted,
                'expirationData' => $registerPermission->expiration_data_formatted,
            ]
        );
    }
}
