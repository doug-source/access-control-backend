<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Library\Converters\ResponseIndex;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\User\CheckRequest;

class UserRemovedController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly UserRepository $userRepository)
    {
        // ...
    }

    /**
     * Display a listing of the resource.
     */
    public function index(CheckRequest $request)
    {
        $this->authorize('viewAny', User::class);
        $query = ResponseIndex::handleQuery(
            $request,
            ['field' => 'name'],
        );

        return $this->userRepository->paginate(
            page: $query['page'],
            group: $query['group'],
            name: $query['name'],
            trashed: true,
        );
    }
}
