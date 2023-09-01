<?php

namespace App\Tests\Service;

use App\Entity\Category;
use App\Entity\User;
use App\Service\CategoryService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class CategoryServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private CategoryService $categoryService;

    public function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->categoryService = new CategoryService($this->entityManager);
    }

    /** @test */
    public function createCategory_works_correctly()
    {
        $category = $this->createMock(Category::class);
        $user = $this->createMock(User::class);

        $this->entityManager->expects($this->exactly(2))->method('persist')->with($category);
        $this->entityManager->expects($this->once())->method('flush');

        $this->categoryService->createCategory($category, $user);
    }

    /** @test */
    public function editCategory_works_correctly()
    {
        $category = $this->createMock(Category::class);

        $this->entityManager->expects($this->once())->method('persist')->with($category);
        $this->entityManager->expects($this->once())->method('flush');

        $this->categoryService->editCategory($category);
    }

    /** @test */
    public function deleteCategory_works_correctly()
    {
        $category = $this->createMock(Category::class);

        $this->entityManager->expects($this->once())->method('remove')->with($category);
        $this->entityManager->expects($this->once())->method('flush');

        $this->categoryService->deleteCategory($category);
    }

}