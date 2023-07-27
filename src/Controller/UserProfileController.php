<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\UserRoleEnum;
use App\Form\Handler\UserProfileFormHandler;
use App\Service\AuthorizationService;
use App\Service\UserProfileService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserProfileController extends AbstractController
{
    #[Route('/user/{username}', name: 'app_user_profile')]
    #[IsGranted(UserRoleEnum::ROLE_USER)]
    public function index(
        User $user
    ): Response
    {
        return $this->render('user_profile/index.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/user/{username}/edit', name: 'app_user_profile_edit')]
    #[IsGranted(UserRoleEnum::ROLE_USER_EMAIL_VERIFIED)]
    public function edit(
        Request $request,
        User $user,
        AuthorizationService $authorizationService,
        UserProfileFormHandler $userProfileFormHandler
    ): Response
    {
        $authorizationService->denyUnauthorizedUserAccess($user);

        $form = $userProfileFormHandler->handle($user, $request);

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

    #[Route('/user/{username}/delete', name: 'app_user_profile_delete', methods: 'GET')]
    #[IsGranted(UserRoleEnum::ROLE_USER_EMAIL_VERIFIED)]
    public function delete(
        User $user,
        UserService $userService,
        AuthorizationService $authorizationService
    ): Response
    {
        $authorizationService->denyUnauthorizedUserAccess($user);
        $userService->deleteUser($user);

        $this->addFlash('success', 'Your profile has been deleted!');
        return $this->redirectToRoute('app_index');
    }
}
