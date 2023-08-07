<?php

namespace App\Service;

use App\Entity\User;
use App\Enum\UserRoleEnum;
use App\Exception\AdminDeletionException;
use App\Exception\UnauthorizedAccessException;
use App\Security\Voter\SameUsernameVoter;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AuthorizationService
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker
    )
    {
    }

    public function denyUnauthorizedUserAccess(User $user): void
    {
        if (!$this->authorizationChecker->isGranted(SameUsernameVoter::IS_SAME_USER, $user->getUserIdentifier())) {
            throw new UnauthorizedAccessException('You do not have permissions to access this page!', 403);
        }

        if (in_array(UserRoleEnum::ROLE_ADMIN, $user->getRoles())) {
            throw new AdminDeletionException('You can not delete yourself!');
        }
    }


}