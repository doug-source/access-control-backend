<?php

declare(strict_types=1);

namespace App\Library\Registration;

use App\Services\Register\RegisterServiceInterface;

final class RegisterRequestHandler implements HandlerInterface
{
    public function __construct(private RegisterServiceInterface $registerService)
    {
        // ...
    }

    public function handle(string $email, ?string $phone): bool
    {
        $registerRequest = $this->registerService->findRegisterRequestByEmail($email);
        if ($registerRequest) {
            $this->registerService->updateModelPhone($registerRequest, $phone);
            return false;
        }
        return true;
    }
}
