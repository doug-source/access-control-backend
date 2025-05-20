<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ability;
use App\Repositories\AbilityRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

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
        return $this->abilityRepository->paginate(
            perPage: $request->input('group', config('database.paginate.perPage')),
            name: $request->input('name')
        );
    }
}
