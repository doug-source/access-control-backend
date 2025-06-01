<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Library\Converters\ResponseIndex;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\User\CheckRequest;
use App\Library\Builders\Response as ResponseBuilder;
use Illuminate\Http\Response;

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

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $user = $this->userRepository->findTrashed($id);
        if (is_null($user)) {
            abort(Response::HTTP_NOT_FOUND);
        }
        $this->authorize('view', $user);
        return $user->ui;
    }

    /**
     * Remove forced the specified soft deleted resource from storage.
     */
    public function destroy(int $id)
    {
        $user = $this->userRepository->findTrashed($id);
        if (is_null($user)) {
            abort(Response::HTTP_NOT_FOUND);
        }
        $this->authorize('forceDelete', $user);
        $this->userRepository->forceDelete($user);
        return ResponseBuilder::successJSON();
    }
}
