<?php

namespace App\Form\Handler;

use App\DTO\ChangePasswordDTO;
use App\Entity\User;
use App\Form\Type\ChangePasswordFormType;
use App\Service\UserService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class ChangePasswordFormHandler
{
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly UserService          $userService,
    )
    {
    }

    public function handle(Request $request, User $user, ChangePasswordDTO $changePasswordDTO): FormInterface|bool
    {
        $form = $this->formFactory->create(ChangePasswordFormType::class, $changePasswordDTO);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $changePasswordDTO = $form->getData();

            $this->userService->changePassword($user, $changePasswordDTO);

            return true;
        }

        return $form;
    }
}