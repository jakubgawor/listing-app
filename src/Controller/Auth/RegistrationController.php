<?php

namespace App\Controller\Auth;

use App\Entity\User;
use App\Form\Handler\EntityFormHandler;
use App\Repository\UserRepository;
use App\Security\LoginFormAuthenticator;
use App\Service\AuthorizationService;
use App\Service\RegistrationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    public function __construct(
        private readonly EntityFormHandler    $entityFormHandler,
        private readonly RegistrationService  $registrationService,
        private readonly AuthorizationService $authorizationService
    )
    {
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserAuthenticatorInterface $userAuthenticator, LoginFormAuthenticator $authenticator): Response
    {
        $this->authorizationService->denyLoggedUserAccess($this->getUser());

        $user = new User();
        $form = $this->entityFormHandler->handle($user, $request, $user, $this->registrationService);

        if ($form === true) {
            $this->addFlash('notification', 'Check your inbox to verify your account email address!');
            return $userAuthenticator->authenticateUser($user, $authenticator, $request);
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
    {
        $id = $request->query->get('id');
        $this->registrationService->verifyEmailAddress($id, $request);

        $this->addFlash('success', 'Your email address has been verified!');
        return $this->redirectToRoute('app_index');
    }
}
