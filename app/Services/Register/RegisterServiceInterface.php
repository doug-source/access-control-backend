<?php

declare(strict_types=1);

namespace App\Services\Register;

use App\Library\Registration\HandlerInterface;
use App\Models\{
    RegisterPermission,
    RegisterRequest
};
use Illuminate\Database\Eloquent\Model;
use \Illuminate\Support\Carbon;

interface RegisterServiceInterface
{
    /**
     * Define the existence of an User instance with email
     */
    public function existsUserByEmail(string $email): bool;

    /**
     * Search an RegisterRequest instance by email
     */
    public function findRegisterRequestByEmail(string $email): ?RegisterRequest;

    /**
     * Search an RegisterPermission instance by email
     */
    public function findRegisterPermissionByEmail(string $email): ?RegisterPermission;

    /**
     * Persist a new RegisterRequest instance inside database
     */
    public function createRegisterRequest(string $email, ?string $phone): void;

    /**
     * Update the Model instance's phone into database
     */
    public function updateModelPhone(Model $model, ?string $phone): void;

    /**
     * Update an existent Register Permission instance into database
     */
    public function updateRegisterPermission(int $id, string $token, Carbon $expirationData): void;

    /**
     * Send approval email to user account register
     */
    public function sendApprovalMail(string $email, string $token): void;

    /**
     * Handle the User Account Register processes
     */
    public function handleRegister(string $email, ?string $phone): void;

    /**
     * Store the registration handlers
     */
    public function setHandlers(HandlerInterface ...$handlers): RegisterServiceInterface;
}
