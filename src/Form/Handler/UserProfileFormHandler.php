<?php

namespace App\Form\Handler;

use App\Entity\User;
use App\Entity\UserProfile;
use App\Form\Type\UserProfileFormType;
use App\Service\UserProfileService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class UserProfileFormHandler
{
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly UserProfileService   $userProfileService,
    )
    {
    }

    public function handle(Request $request, UserProfile $userProfile, User $user): FormInterface|bool
    {
        $originalEmail = $userProfile->getUser()->getEmail();

        $form = $this->formFactory->create(UserProfileFormType::class, $userProfile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userProfile = $form->getData();

            $this->userProfileService->updateUserProfile($userProfile, $user, $originalEmail);

            return true;
        }

        return $form;
    }
}