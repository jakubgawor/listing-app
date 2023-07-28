<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class SameUsernameVoter extends Voter
{
    public const IS_SAME_USER = 'IS_SAME_USER';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::IS_SAME_USER && is_string($subject);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        return $user->getUserIdentifier() === $subject;
    }
}
