<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class CategoryService
{
    public function __construct(
        private EntityManagerInterface $entityManager,

    )
    {
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