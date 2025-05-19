<?php

declare(strict_types=1);

namespace App\Library\Registration;

use App\Library\Builders\Token as TokenBuilder;
use App\Library\Registration\HandlerInterface;
use App\Services\Register\Contracts\RegisterServiceInterface;
use Carbon\Carbon;

final class PermissionRequestHandler implements HandlerInterface

{
    public function __construct(private RegisterServiceInterface $registerService)
    {
        // ...
    }

    public function handle(string $email, ?string $phone): bool
    {
        $permission = $this->registerService->findRegisterPermissionByEmail($email);
        if ($permission) {
            $token = $permission->token;
            if (Carbon::now()->greaterThan(Carbon::parse($permission->expiration_data))) {
                $token = TokenBuilder::build();
                $this->registerService->updateRegisterPermission(
                    id: $permission->id,
                    token: $token,
                    expirationData: now()->addHours(
                        config('app.register.expire')
                    )
                );
            }
            $this->registerService->updateModelPhone($permission, $phone);
            $this->registerService->sendApprovalMail($email, $token);
            return false;
        }
        return true;
    }
}
