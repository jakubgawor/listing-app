<?php

namespace App\Controller;

use App\Entity\Listing;
use App\Enum\UserRoleEnum;
use App\Form\Handler\ListingFormHandler;
use App\Repository\ListingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ListingController extends AbstractController
{
    public function __construct(private ListingRepository $listingRepository)
    {
    }

    #[Route('/', name: 'app_index')]
    public function index(): Response
    {
        return $this->render('listing/index.html.twig', [
            'listings' => $this->listingRepository->findAll()
        ]);
    }

    #[Route('/create-listing', name: 'app_listing_create')]
    #[IsGranted(UserRoleEnum::ROLE_USER_EMAIL_VERIFIED)]
    public function create(Request $request, ListingFormHandler $listingFormHandler): Response
    {
        $form = $listingFormHandler->handle($this->getUser(), $request);

        if ($form === true) {

            $this->addFlash('success', 'Successfully added new listing!');
            return $this->redirectToRoute('app_index', [
            ]);
        }

        return $this->render('listing/create.html.twig', [
            'listingForm' => $form->createView()
        ]);
    }
}
