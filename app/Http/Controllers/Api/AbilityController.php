<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ability\CheckRequest;
use App\Library\Builders\Response as ResponseBuilder;
use App\Library\Converters\ResponseIndex;
use App\Models\Ability;
use App\Repositories\AbilityRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AbilityController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private AbilityRepository $abilityRepository)
    {
        // ...
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Ability::class);
        $query = ResponseIndex::handleQuery(
            $request,
            ['field' => 'name'],
        );
        return $this->abilityRepository->paginate(
            page: $query['page'],
            group: $query['group'],
            name: $query['name'],
        );
    }

    /**
     * Display one resource instance
     */
    public function show(Ability $ability)
    {
        $this->authorize('view', $ability);
        return $ability->ui;
    }

    /**
     * Persist one resource instance
     */
    public function store(CheckRequest $request)
    {
        $this->authorize('create', Ability::class);
        $ability = $this->abilityRepository->create([
            'name' => $request->input('name')
        ]);
        return ResponseBuilder::successJSON(
            status: Response::HTTP_CREATED,
            headers: [
                'Location' => route('ability.show', ['ability' => $ability->id])
            ]
        );
    }

    /**
     * Update the resource's data
     */
    public function update(CheckRequest $request, Ability $ability)
    {
        $this->authorize('update', $ability);
        $name = $request->validated('name');
        $this->abilityRepository->update($ability->id, [
            'name' => $name
        ]);
        return ResponseBuilder::successJSON(
            status: Response::HTTP_NO_CONTENT
        );
    }

    /**
     * Remove the resource from database
     */
    public function destroy(CheckRequest $request, Ability $ability)
    {
        $this->authorize('delete', $ability);
        $this->abilityRepository->delete($ability->id);
        return ResponseBuilder::successJSON();
    }
}
