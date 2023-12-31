<?php

namespace App\Controller\Admin;

use App\Entity\Listing;
use App\Form\Handler\ListingFormHandler;
use App\Repository\ListingRepository;
use App\Service\AdminService;
use App\Service\ListingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminListingController extends AbstractController
{
    public function __construct(
        private readonly ListingRepository $listingRepository,
        private readonly ListingService    $listingService,
        private readonly AdminService      $adminService,
        private readonly ListingFormHandler $listingFormHandler,
    )
    {
    }

    #[Route('/admin/listings', name: 'app_admin_listings')]
    public function showListings(): Response
    {
        return $this->render('admin/listings.html.twig', [
            'listings' => $this->listingRepository->findNotVerified()
        ]);
    }

    #[Route('/admin/listing/{slug}', name: 'app_admin_show_listing')]
    public function showListing(string $slug): Response
    {
        return $this->render('admin/showListing.html.twig', [
            'listing' => $this->listingRepository->findOneBySlug($slug)
        ]);
    }

    #[Route('/admin/listing/{slug}/verify', name: 'app_admin_verify')]
    public function verify(?Listing $listing): Response
    {
        $this->adminService->verifyListing($listing);

        $this->addFlash('success', 'Successfully verified  listing! ' . $listing->getTitle());
        return $this->redirectToRoute('app_show_listing', [
            'slug' => $listing->getSlug()
        ]);
    }

    #[Route('/admin/listing/{slug}/edit', name: 'app_admin_edit')]
    public function edit(?Listing $listing, Request $request): Response
    {
        $form = $this->listingFormHandler->handle($request, $listing->getBelongsToUser(), $listing);

        if ($form === true) {
            $this->addFlash('success', 'Listing has been updated!');
            return $this->redirectToRoute('app_index');
        }

        return $this->render('listing/edit.html.twig', [
            'listingForm' => $form->createView()
        ]);
    }

    #[Route('/admin/listing/{slug}/delete', name: 'app_admin_delete')]
    public function delete(?Listing $listing): Response
    {
        $this->listingService->deleteListing($listing);

        $this->addFlash('success', 'Listing has been deleted!');
        return $this->redirectToRoute('app_index');
    }

}
