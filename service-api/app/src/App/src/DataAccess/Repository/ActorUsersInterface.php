<?php

declare(strict_types=1);

namespace App\DataAccess\Repository;

use App\Exception\CreationException;
use App\Exception\NotFoundException;
use Exception;

interface ActorUsersInterface
{
    /**
     * Add an actor user
     *
     * @param string $email
     * @param string $password
     * @param string $activationToken
     * @param int $activationTtl
     * @return array
     * @throws CreationException
     */
    public function add(string $email, string $password, string $activationToken, int $activationTtl) : array;

    /**
     * Get an actor user from the database
     *
     * @param string $email
     * @return array
     * @throws NotFoundException
     */
    public function get(string $email) : array;

    /**
     * Activate the user account in the database using the token value
     *
     * @param string $activationToken
     * @return array
     * @throws NotFoundException
     */
    public function activate(string $activationToken) : array;

    /**
     * Check for the existence of an actor user
     *
     * @param string $email
     * @return bool
     */
    public function exists(string $email) : bool;

    /**
     * Reset a password in the system using a reset token and the intended password
     *
     * @param string $resetToken
     * @param string $password
     * @return bool The password reset was successful or not
     */
    public function resetPassword(string $resetToken, string $password): bool;

    /**
     * Records a successful login against the actor user
     *
     * @param string $email
     * @param string $loginTime An ATOM format datetime string
     */
    public function recordSuccessfulLogin(string $email, string $loginTime) : void;

    /**
     * Records a reset token against an actor user account
     *
     * @param string $email
     * @param string $resetToken
     * @param int $resetExpiry Seconds till token expires
     */
    public function recordPasswordResetRequest(string $email, string $resetToken, int $resetExpiry): void;
}
