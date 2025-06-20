<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AbilityUser\CheckRequest;
use App\Library\Converters\ResponseIndex;
use App\Services\Ability\Contracts\AbilityServiceInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AbilityUserController extends Controller
{
    public function __construct(private AbilityServiceInterface $abilityService)
    {
        // ...
    }

    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(CheckRequest $request)
    {
        $user = $request->input('user');
        $this->authorize('viewAnyAbility', $user);
        $query = ResponseIndex::handleQuery(
            $request,
            ['field' => 'owner', 'default' => 'yes'],
            ['field' => 'name'],
        );
        return $this->abilityService->findReferenceUserAbilities(
            user: $user,
            owner: $query['owner'] === 'no' ? false : true,
            page: $query['page'],
            group: $query['group'],
            name: $query['name'],
        );
    }
}
