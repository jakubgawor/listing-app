<?php

namespace App\Controller;

use App\DTO\ChangePasswordDTO;
use App\Entity\User;
use App\Enum\UserRoleEnum;
use App\Form\Handler\ChangePasswordFormHandler;
use App\Form\Handler\UserProfileFormHandler;
use App\Service\AuthorizationService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AccountController extends AbstractController
{
    public function __construct(
        private readonly AuthorizationService      $authorizationService,
        private readonly UserProfileFormHandler    $userProfileFormHandler,
        private readonly UserService               $userService,
        private readonly ChangePasswordFormHandler $changePasswordFormHandler,
    )
    {
    }

    #[Route('/user/{username}', name: 'app_user_profile')]
    #[IsGranted(UserRoleEnum::ROLE_USER)]
    public function index(?User $user): Response
    {
        return $this->render('user_profile/index.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/user/{username}/edit', name: 'app_user_profile_edit')]
    #[IsGranted(UserRoleEnum::ROLE_USER_EMAIL_VERIFIED)]
    public function edit(Request $request, ?User $user): Response
    {
        $this->authorizationService->denyUnauthorizedUserAccess($user);

        $form = $this->userProfileFormHandler->handle($request, $user->getUserProfile(), $user);

        if ($form === true) {
            $this->addFlash('success', 'Your profile has been updated!');
            return $this->redirectToRoute('app_user_profile', [
                'username' => $user->getUsername()
            ]);
        }

        return $this->render('user_profile/edit.html.twig', [
            'user' => $this->getUser(),
            'userProfileForm' => $form->createView()
        ]);
    }


    #[Route('/user/{username}/delete', name: 'app_user_delete', methods: 'GET')]
    #[IsGranted(UserRoleEnum::ROLE_USER_EMAIL_VERIFIED)]
    public function delete(?User $user): Response
    {
        $this->authorizationService->denyUnauthorizedUserAccess($user);
        $this->userService->deleteUser($user);

        $this->addFlash('success', 'Your profile has been deleted!');
        return $this->redirectToRoute('app_index');
    }

    #[Route('/user/{username}/change-password', name: 'app_user_change_password')]
    #[IsGranted(UserRoleEnum::ROLE_USER_EMAIL_VERIFIED)]
    public function changePassword(Request $request, ?User $user): Response
    {
        $this->authorizationService->denyUnauthorizedUserAccess($user);

        $form = $this->changePasswordFormHandler->handle($request, $user, new ChangePasswordDTO());

        if ($form === true) {
            $this->addFlash('success', 'Your password has been updated!');
            return $this->redirectToRoute('app_index');
        }

        return $this->render('user_profile/change_password.html.twig', [
            'user' => $user,
            'changePasswordForm' => $form->createView()
        ]);
    }
}