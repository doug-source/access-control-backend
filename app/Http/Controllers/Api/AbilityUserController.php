<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AbilityUser\CheckRequest;
use App\Library\Builders\Response as ResponseBuilder;
use App\Library\Converters\ResponseIndex;
use App\Services\Ability\Contracts\AbilityServiceInterface;
use App\Services\Ability\Contracts\AbilityUserServiceInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response;

class AbilityUserController extends Controller
{
    public function __construct(
        private AbilityServiceInterface $abilityService,
        private AbilityUserServiceInterface $abilityUserService
    ) {
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

    /**
     * Update the user's abilities including and removing some abilities
     */
    public function update(CheckRequest $request)
    {
        $user = $request->input('user');
        $this->authorize('updateAbility', $user);

        $singleAbilities = $request->input('singleAbilities');

        $syncs = $this->abilityUserService->organizeAbilities(
            collect($request->input('removed')),
            $singleAbilities['included'],
            FALSE,
            $this->abilityUserService->organizeAbilities(
                collect($request->input('included')),
                $singleAbilities['removed'],
                TRUE
            )
        );

        $user->abilities()->sync($syncs->all());

        return ResponseBuilder::successJSON(
            status: Response::HTTP_NO_CONTENT
        );
    }
}
