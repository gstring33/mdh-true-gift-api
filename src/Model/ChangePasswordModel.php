<?php

namespace App\Model;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ChangePasswordModel
{
    public string $newPassword;
    public string $newPassword2;

    /**
     * @return bool
     */
    public function isNewPasswordValid (): bool
    {
        return $this->newPassword === $this->newPassword2 && strlen($this->newPassword) > 5;
    }

    /**
     * @return string
     */
    public function plainTextPassword (): string
    {
        return $this->newPassword;
    }
}