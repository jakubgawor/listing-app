<?php

namespace App\Form\Handler;

use App\Entity\Listing;
use App\Entity\User;
use App\Form\Type\ListingFormType;
use App\Service\Listing\ListingService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class ListingFormHandler
{
    public function __construct(private ListingService $listingService, private FormFactoryInterface $formFactory)
    {
    }

    public function handle(User $user, Request $request, Listing $listing = new Listing): bool|FormInterface
    {
        $form = $this->formFactory->create(ListingFormType::class, $listing);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $listing = $form->getData();
            $this->listingService->createListing($listing, $user);

            return true;
        }

        return $form;
    }
}