<?php declare(strict_types=1);

namespace VitesseCms\User\Repositories;

use VitesseCms\User\Models\User;

class UserRepository
{
    public function getById(string $id, bool $hideUnpublished = true): ?User
    {
        User::setFindPublished($hideUnpublished);

        /** @var User $user */
        $user = User::findById($id);
        if($user instanceof User):
            return $user;
        endif;

        return null;
    }

    public function getByEmail(string $email): ?User
    {
        User::setFindValue('email', $email);

        /** @var User $user */
        $user = User::findFirst();
        if($user instanceof User):
            return $user;
        endif;

        return null;
    }

    public function getByPasswordResetToken(string $token): ?User
    {
        User::setFindValue('passwordReset.passwordResetToken',$token);

        /** @var User $user */
        $user = User::findFirst();
        if($user instanceof User):
            return $user;
        endif;

        return null;
    }
}
