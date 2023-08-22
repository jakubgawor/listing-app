<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\Interface\EntityMarkerInterface;
use App\Entity\User;
use App\Service\Interface\EntityServiceInterface;
use App\Traits\EntityCheckerTrait;
use Doctrine\ORM\EntityManagerInterface;

class CategoryService implements EntityServiceInterface
{
    use EntityCheckerTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,

    )
    {
    }

    public function handleEntity(User $user, EntityMarkerInterface $entity): void
    {
        $this->checkEntityType($entity, Category::class);

        if ($entity->getAddedBy() === null) {
            $this->createCategory($entity, $user);
        } else {
            $this->editCategory($entity);
        }
    }

    public function createCategory(Category $category, User $user): void
    {
        $this->entityManager->persist($category);
        $this->entityManager->persist($category->setAddedBy($user));

        $this->entityManager->flush();
    }

    public function editCategory(Category $category): void
    {
        $this->entityManager->persist($category);

        $this->entityManager->flush();
    }

    public function deleteCategory(Category $category): void
    {
        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }


}