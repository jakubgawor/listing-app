<?php

namespace App\Controller\Admin;

use App\Enum\ListingStatusEnum;
use App\Form\Handler\ListingFormHandler;
use App\Repository\ListingRepository;
use App\Service\Listing\ListingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminListingController extends AbstractController
{
    public function __construct(
        private readonly ListingRepository      $listingRepository,
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    #[Route('/admin/listings', name: 'app_admin_listings')]
    public function showListings(): Response
    {
        return $this->render('admin/listings.html.twig', [
            'listings' => $this->listingRepository->findByStatus(ListingStatusEnum::NOT_VERIFIED)
        ]);
    }

    #[Route('/admin/listing/{slug}', name: 'app_admin_show_listing')]
    public function showListing(string $slug): Response
    {
        return $this->render('admin/showListing.html.twig', [
            'listing' => $this->listingRepository->findOneBy([
                'slug' => $slug
            ])
        ]);
    }

    #[Route('/admin/listing/{slug}/verify', name: 'app_admin_verify')]
    public function verify(string $slug): Response
    {
        $listing = $this->listingRepository->findOneBy([
            'slug' => $slug
        ]);

        if ($listing->getStatus() === ListingStatusEnum::VERIFIED) {
            $this->addFlash('notification', 'This listing is already verified!');
            return $this->redirectToRoute('app_show_listing', [
                'slug' => $slug
            ]);
        }

        $this->entityManager->persist($listing->setStatus(ListingStatusEnum::VERIFIED));
        $this->entityManager->flush();


        $this->addFlash('success', 'Successfully verified  listing!' . $listing->getStatus());
        return $this->redirectToRoute('app_show_listing', [
            'slug' => $slug
        ]);
    }

    #[Route('/admin/listing/{slug}/edit', name: 'app_admin_edit')]
    public function edit(string $slug, Request $request, ListingFormHandler $listingFormHandler): Response
    {
        $listing = $this->listingRepository->findOneBy([
            'slug' => $slug
        ]);

        $form = $listingFormHandler->handle($listing->getBelongsToUser(), $request, $listing);

        if ($form === true) {
            $this->addFlash('success', 'Your listing has been updated!');
            return $this->redirectToRoute('app_index');
        }

        return $this->render('listing/edit.html.twig', [
            'listingForm' => $form->createView()
        ]);
    }

    #[Route('/admin/listing/{slug}/delete', name: 'app_admin_delete')]
    public function delete(string $slug, ListingService $listingService): Response
    {
        $listing = $this->listingRepository->findOneBy([
            'slug' => $slug
        ]);

        $listingService->deleteListing($listing);

        $this->addFlash('success', 'Listing has been deleted!');
        return $this->redirectToRoute('app_index');
    }

}
