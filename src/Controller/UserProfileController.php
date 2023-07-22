<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\UserRoleEnum;
use App\Form\UserProfileFormType;
use App\Repository\UserRepository;
use App\Security\Voter\UserProfileEditVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserProfileController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('/user/{username}', name: 'app_user_profile', methods: 'GET')]
    #[IsGranted(UserRoleEnum::ROLE_USER)]
    public function index(User $user): Response
    {
        return $this->render('user_profile/index.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/user/{username}/edit', name: 'app_user_profile_edit', methods: ['GET', 'POST'])]
    #[IsGranted(UserRoleEnum::ROLE_USER_EMAIL_VERIFIED)]
    public function edit(Request $request, User $user): Response
    {
        $this->denyAccessUnlessGranted(
            UserProfileEditVoter::IS_SAME_USER,
            $user->getUsername(),
            'You do not have permissions to access this page!'
        );

        $userProfile = $user->getUserProfile();

        $form = $this->createForm(UserProfileFormType::class, $userProfile);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userProfile = $form->getData();

            $this->entityManager->persist($user->setUserProfile($userProfile));
            $this->entityManager->flush();

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
    public function delete(User $user, TokenStorageInterface $tokenStorage): Response
    {
        $this->denyAccessUnlessGranted(
            UserProfileEditVoter::IS_SAME_USER,
            $user->getUsername(),
            'You do not have permissions to access this page!'
        );
        $this->entityManager->remove($user->getUserProfile());
        $this->entityManager->remove($user);

        $tokenStorage->setToken(null);

        $this->entityManager->flush();

        $this->addFlash('success', 'Your profile has been deleted!');
        return $this->redirectToRoute('app_index');
    }
}
