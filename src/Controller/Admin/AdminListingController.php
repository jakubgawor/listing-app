<?php

namespace App\Controller\Admin;

use App\Exception\ListingNotFoundException;
use App\Form\Handler\ListingFormHandler;
use App\Repository\ListingRepository;
use App\Service\AdminService;
use App\Service\Listing\ListingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminListingController extends AbstractController
{
    public function __construct(
        private readonly ListingRepository  $listingRepository,
        private readonly ListingFormHandler $listingFormHandler,
        private readonly ListingService     $listingService,
        private readonly AdminService       $adminService
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
    public function verify(string $slug): Response
    {
        $listing = $this->adminService->verifyListing($this->listingRepository->findOneBySlug($slug));

        $this->addFlash('success', 'Successfully verified  listing!' . $listing->getStatus());
        return $this->redirectToRoute('app_show_listing', [
            'slug' => $listing->getSlug()
        ]);
    }

    #[Route('/admin/listing/{slug}/edit', name: 'app_admin_edit')]
    public function edit(string $slug, Request $request): Response
    {
        $listing = $this->listingRepository->findOneBySlug($slug);

        if ($listing === null) {
            throw new ListingNotFoundException('Listing not found', 404);
        }

        $form = $this->listingFormHandler->handle($listing->getBelongsToUser(), $request, $listing, $this->getUser());

        if ($form === true) {
            $this->addFlash('success', 'Listing has been updated!');
            return $this->redirectToRoute('app_index');
        }

        return $this->render('listing/edit.html.twig', [
            'listingForm' => $form->createView()
        ]);
    }

    #[Route('/admin/listing/{slug}/delete', name: 'app_admin_delete')]
    public function delete(string $slug): Response
    {
        $this->listingService->deleteListing($this->listingRepository->findOneBySlug($slug));

        $this->addFlash('success', 'Listing has been deleted!');
        return $this->redirectToRoute('app_index');
    }

}
