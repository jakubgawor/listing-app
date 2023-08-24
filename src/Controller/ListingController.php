<?php

namespace App\Controller;

use App\Entity\Listing;
use App\Enum\UserRoleEnum;
use App\Form\Handler\ListingFormHandler;
use App\Repository\ListingRepository;
use App\Service\AuthorizationService;
use App\Service\ListingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ListingController extends AbstractController
{
    public function __construct(
        private readonly ListingRepository    $listingRepository,
        private readonly AuthorizationService $authorizationService,
        private readonly ListingService       $listingService,
        private readonly ListingFormHandler   $listingFormHandler,
    )
    {
    }

    #[Route('/', name: 'app_index')]
    public function index(): Response
    {
        return $this->render('listing/index.html.twig', [
            'listings' => $this->listingRepository->findVerified()
        ]);
    }

    #[Route('/listing/{slug}', name: 'app_show_listing')]
    public function showListing(?Listing $listing): Response
    {
        $listing = $this->listingService->showOne($listing);

        return $this->render('listing/showListing.html.twig', [
            'listing' => $listing
        ]);
    }

    #[Route('/create-listing', name: 'app_listing_create')]
    #[IsGranted(UserRoleEnum::ROLE_USER_EMAIL_VERIFIED)]
    public function create(Request $request): Response
    {
        $form = $this->listingFormHandler->handle($request, $this->getUser(), new Listing);

        if ($form === true) {
            $this->addFlash('success', 'Successfully added new listing!');
            return $this->redirectToRoute('app_index');
        }

        return $this->render('listing/create.html.twig', [
            'listingForm' => $form->createView()
        ]);
    }

    #[Route('/listing/{slug}/edit', name: 'app_listing_edit')]
    #[IsGranted(UserRoleEnum::ROLE_USER_EMAIL_VERIFIED)]
    public function edit(?Listing $listing, Request $request): Response
    {
        $this->authorizationService->denyUserAccessToNotVerfiedListings($listing);
        $this->authorizationService->denyUnauthorizedUserAccess($listing->getBelongsToUser());

        $form = $this->listingFormHandler->handle($request, $this->getUser(), $listing);

        if ($form === true) {
            $this->addFlash('success', 'Your listing has been updated!');
            return $this->redirectToRoute('app_index');
        }

        return $this->render('listing/edit.html.twig', [
            'listingForm' => $form->createView()
        ]);
    }

    #[Route('/listing/{slug}/delete', name: 'app_listing_delete')]
    #[IsGranted(UserRoleEnum::ROLE_USER_EMAIL_VERIFIED)]
    public function delete(?Listing $listing): Response
    {
        $this->authorizationService->denyUserAccessToNotVerfiedListings($listing);
        $this->authorizationService->denyUnauthorizedUserAccess($listing->getBelongsToUser());
        $this->listingService->deleteListing($listing);

        $this->addFlash('success', 'Your listing has been deleted!');
        return $this->redirectToRoute('app_index');
    }

}
