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
use App\Services\Register\RegisterServiceInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegisterPermission as MailRegisterPermission;
use Illuminate\Support\Facades\URL;
use \Illuminate\Support\Carbon;

final class RegisterService implements RegisterServiceInterface
{
    /** @var array<int, HandlerInterface> */
    private $handlers;

    public function __construct(
        private readonly User $user,
        private readonly RegisterRequest $registerRequest,
        private readonly RegisterPermission $registerPermission,
    ) {
        // ...
    }

    public function existsUserByEmail(string $email): bool
    {
        return $this->user->newQuery()->where('email', $email)->exists();
    }

    public function findRegisterRequestByEmail(string $email): ?RegisterRequest
    {
        return $this->registerRequest->newQuery()->where('email', $email)->first();
    }

    public function findRegisterPermissionByEmail(string $email): ?RegisterPermission
    {
        return $this->registerPermission->newQuery()->where('email', $email)->first();
    }

    public function createRegisterRequest(string $email, ?string $phone): void
    {
        $this->registerRequest->newInstance(attributes: [
            'email' => $email,
            'phone' => $phone
        ])->save();
    }

    public function updateModelPhone(Model $model, ?string $phone): void
    {
        if ($model->phone === PhoneConverter::clear($phone)) {
            return;
        }
        $model->phone = $phone;
        $model->save();
    }

    public function updateRegisterPermission(int $id, string $token, Carbon $expirationData): void
    {
        $this->registerPermission->newQuery()->where('id', $id)->update([
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
                name: 'users.create',
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
