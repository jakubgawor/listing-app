<?php

namespace App\Form\Handler;

use App\Entity\Category;
use App\Entity\Interface\EntityMarkerInterface;
use App\Entity\Listing;
use App\Entity\User;
use App\Entity\UserProfile;
use App\Form\Type\CategoryFormType;
use App\Form\Type\ListingFormType;
use App\Form\Type\RegistrationFormType;
use App\Form\Type\UserProfileFormType;
use App\Service\Interface\EntityServiceInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class EntityFormHandler
{
    public function __construct(
        private readonly FormFactoryInterface $formFactory
    )
    {
    }

    public function handle(User $user, Request $request, EntityMarkerInterface $entity, EntityServiceInterface $entityService): bool|FormInterface
    {
        $formTypeClass = $this->getFormTypeClass($entity);

        $form = $this->formFactory->create($formTypeClass, $entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entity = $form->getData();

            if ($formTypeClass === RegistrationFormType::class) {
                $entityService->registerUser($user, $form->get('plainPassword')->getData());
            } else {
                $entityService->handleEntity($user, $entity);
            }

            return true;
        }

        return $form;
    }

    public function getFormTypeClass(EntityMarkerInterface $entity): string
    {
        if ($entity instanceof Category) {
            return CategoryFormType::class;
        }

        if ($entity instanceof Listing) {
            return ListingFormType::class;
        }

        if ($entity instanceof UserProfile) {
            return UserProfileFormType::class;
        }

        if ($entity instanceof User) {
            return RegistrationFormType::class;
        }

        throw new \InvalidArgumentException('Unsupported entity type');
    }

}