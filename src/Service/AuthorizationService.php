<?php

namespace App\Service;

use App\Entity\User;
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
    }

    public function denyLoggedUserAccess(?User $user): void
    {
        if ($user) {
            throw new UnauthorizedAccessException('You are already logged in!', 403);
        }
    }

}