<?php

namespace App\Form\Handler;

use App\Entity\User;
use App\Form\Type\UserProfileFormType;
use App\Service\UserProfile\UserProfileService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class UserProfileFormHandler
{
    public function __construct(private UserProfileService $userProfileService, private FormFactoryInterface $formFactory)
    {
    }

    public function handle(User $user, Request $request): bool|FormInterface
    {
        $form = $this->formFactory->create(UserProfileFormType::class, $user->getUserProfile());
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $userProfile = $form->getData();
            $this->userProfileService->updateUserProfile($user, $userProfile);

            return true;
        }

        return $form;
    }
}