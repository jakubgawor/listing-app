<?php

namespace App\Form\Handler;

use App\Entity\User;
use App\Form\Type\RegistrationFormType;
use App\Service\RegistrationService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class RegistrationFormHandler
{
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly RegistrationService  $registrationService,
    )
    {
    }

    public function handle(Request $request, User $user): FormInterface|bool
    {
        $form = $this->formFactory->create(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $this->registrationService->registerUser($user, $form->get('plainPassword')->getData());

            return true;
        }

        return $form;
    }
}