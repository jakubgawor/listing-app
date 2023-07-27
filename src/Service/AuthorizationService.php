<?php

namespace App\Service;

use App\Entity\User;
use App\Exception\UnauthorizedAccessException;
use App\Security\Voter\UserProfileEditVoter;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AuthorizationService
{
    public function __construct(private AuthorizationCheckerInterface $authorizationChecker)
    {
    }

    public function denyUnauthorizedUserAccess(User $user): void
    {
        if(!$this->authorizationChecker->isGranted(UserProfileEditVoter::IS_SAME_USER, $user->getUserIdentifier())) {
            throw new UnauthorizedAccessException('You do not have permissions to access this page!', 403);
        }

    }
}