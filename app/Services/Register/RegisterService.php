<?php

declare(strict_types=1);

namespace App\Services\Register;

use App\Library\Builders\Phrase;
use App\Library\Converters\Phone as PhoneConverter;
use App\Library\Enums\PhraseKey;
use App\Library\Registration\HandlerInterface;
use App\Models\{
    RegisterPermission,
    RegisterRequest,
    User
};
use App\Services\Register\Contracts\RegisterServiceInterface;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegisterPermission as MailRegisterPermission;
use App\Repositories\RegisterPermissionRepository;
use Illuminate\Support\Facades\URL;
use \Illuminate\Support\Carbon;
use App\Repositories\RegisterRequestRepository;

final class RegisterService implements RegisterServiceInterface
{
    /** @var array<int, HandlerInterface> */
    private $handlers;

    public function __construct(
        private readonly User $user,
        private readonly RegisterRequestRepository $regRequestRepository,
        private readonly RegisterPermissionRepository $permissionRepository
    ) {
        // ...
    }

    public function existsUserByEmail(string $email): bool
    {
        return $this->user->newQuery()->where('email', $email)->exists();
    }

    public function findRegisterRequestByEmail(string $email): ?RegisterRequest
    {
        return $this->regRequestRepository->findByEmail($email);
    }

    public function findRegisterPermissionByEmail(string $email): ?RegisterPermission
    {
        return $this->permissionRepository->findByEmail($email);
    }

    public function createRegisterRequest(string $email, ?string $phone): void
    {
        $this->regRequestRepository->create(
            attributes: [
                'email' => $email,
                'phone' => $phone
            ]
        );
    }

    public function updateModelPhone(RegisterRequest|RegisterPermission $model, ?string $phone): void
    {
        if ($model->phone === PhoneConverter::clear($phone)) {
            return;
        }
        $repository = $model instanceof RegisterRequest ? $this->regRequestRepository : $this->permissionRepository;
        $repository->update(
            id: $model->id,
            attributes: [
                'phone' => $phone
            ]
        );
    }

    public function updateRegisterPermission(int $id, string $token, Carbon $expirationData): void
    {
        $this->permissionRepository->update(id: $id, attributes: [
            'token' => $token,
            'expiration_data' => $expirationData
        ]);
    }

    public function sendApprovalMail(string $email, string $token): void
    {
        Mail::to($email)->send(new MailRegisterPermission([
            'fromName' => config('app.name'),
            'fromEmail' => config('mail.from.address'),
            'subject' => Phrase::pickSentence(PhraseKey::RegisterApproval),
            'url' => URL::temporarySignedRoute(
                name: 'user.create',
                expiration: now()->addMinutes(15),
                parameters: ['token' => $token]
            ),
        ]));
    }

    public function handleRegister(string $email, ?string $phone): void
    {
        $finalize = collect($this->handlers)->reduce(function ($acc, $next) use ($email, $phone) {
            if ($acc) {
                return $next->handle($email, $phone);
            }
            return false;
        }, true);

        if ($finalize) {
            $this->createRegisterRequest($email, $phone);
        }
    }

    public function setHandlers(HandlerInterface ...$handlers): RegisterServiceInterface
    {
        $this->handlers = $handlers;
        return $this;
    }
}
