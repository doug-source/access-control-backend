<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Library\Builders\Response as ResponseBuilder;
use App\Services\Register\Contracts\RegisterServiceInterface;
use App\Http\Requests\User\CheckRequest;
use App\Library\Converters\Phone as PhoneConverter;
use App\Library\Converters\ResponseIndex;
use App\Models\User;
use App\Repositories\RegisterPermissionRepository;
use App\Repositories\UserRepository;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\{
    Request,
    Response
};
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class UserController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly RegisterServiceInterface $registerService,
        private readonly RegisterPermissionRepository $permissionRepository,
        private readonly UserRepository $userRepository,
    ) {
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
        );
    }

    /**
     * Display one resource instance
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);
        return $user->ui;
    }

    /**
     * Remove the resource from database
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        $this->userRepository->delete($user->id);
        return ResponseBuilder::successJSON();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CheckRequest $request)
    {
        $registerPermission = $this->permissionRepository->findByEmail($request->email);
        $this->permissionRepository->delete($registerPermission->id);

        $phone = PhoneConverter::clear($registerPermission->phone ?? $request->phone);
        $user = $this->userRepository->create(attributes: [
            ...$request->only(['name', 'email', 'password']),
            'phone' => $phone,
        ]);
        event(new Registered($user));

        return ResponseBuilder::successJSON(
            status: Response::HTTP_CREATED
        );
    }

    /**
     * Update the resource's data
     */
    public function update(CheckRequest $request)
    {
        $user = Auth::user();
        $attributes = $this->detachRequest($request, $user);
        $this->userRepository->update(id: $user->id, attributes: $attributes);

        return ResponseBuilder::successJSON(
            data: ['photo' => $attributes['photo'] ?? $user->photo]
        );
    }

    /**
     * Filter just the request fields modified.
     *
     * @return array<array-key, string>
     */
    private function detachRequest(Request $request, Authenticatable|Model|null $user)
    {
        $filePath = self::handleFile($request, $user, 'photo', 'user-photos');
        $inputs = collect([
            ...$request->only(['name', 'phone']),
            ...($filePath ? ['photo' => $filePath] : [])
        ])->filter(fn($val, $key) => $user->$key !== $val)->toArray();
        $inputs['phone'] = PhoneConverter::clear($inputs['phone'] ?? NULL);
        return $inputs;
    }
}
