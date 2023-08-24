<?php

namespace App\Form\Handler;

use App\Entity\Listing;
use App\Entity\User;
use App\Form\Type\ListingFormType;
use App\Service\ListingService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class ListingFormHandler
{
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly ListingService       $listingService,
    )
    {
    }

    public function handle(Request $request, User $user, Listing $listing): FormInterface|bool
    {
        $form = $this->formFactory->create(ListingFormType::class, $listing);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $listing = $form->getData();

            if ($listing->getBelongsToUser() === null) {
                $this->listingService->create($listing, $user);
            } else {
                $this->listingService->edit($listing, $user);
            }

            return true;
        }

        return $form;
    }
}