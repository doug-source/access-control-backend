<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterPermission\CheckRequest;
use App\Library\Builders\Response as ResponseBuilder;
use App\Models\RegisterPermission;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class RegisterPermissionController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(CheckRequest $request)
    {
        $this->authorize('viewAny', RegisterPermission::class);
        return ResponseBuilder::successJSON(
            $this->searchRegisterPermissions(
                perPage: $request->input('group', 3),
                email: $request->input('email')
            )
        );
    }

    /**
     * Display one RegisterPermission instance
     */
    public function show(CheckRequest $request)
    {
        $registerPermission = RegisterPermission::find($request->validated('registerPermissionID'));
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

    /**
     * Query the RegisterPermission instance list
     *
     * @return  \Illuminate\Database\Eloquent\Collection
     */
    private function searchRegisterPermissions($perPage = 3, ?string $email = NULL/* , $paginate = FALSE */)
    {
        $query = RegisterPermission::select('id', 'email', 'phone', 'created_at');
        if ($email) {
            $query = $query->where([
                ['email', 'like', "%{$email}%"]
            ]);
        }
        return $query->paginate($perPage);
    }
}
