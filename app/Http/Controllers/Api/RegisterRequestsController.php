<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest\CheckRequest;
use App\Library\Builders\Response as ResponseBuilder;
use App\Models\RegisterRequest;

class RegisterRequestsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(CheckRequest $request)
    {
        return ResponseBuilder::successJSON(
            $this->searchRegisterRequests(
                perPage: $request->input('group', 3),
                email: $request->input('email')
            )
        );
    }

    /**
     * Query the RegisterRequest instance list
     *
     * @return  \Illuminate\Database\Eloquent\Collection
     */
    protected function searchRegisterRequests($perPage = 3, ?string $email = NULL/* , $paginate = FALSE */)
    {
        $query = RegisterRequest::select('id', 'email', 'phone', 'created_at');
        if ($email) {
            $query = $query->where([
                ['email', 'like', "%{$email}%"]
            ]);
        }
        return $query->paginate($perPage);
    }
}
