<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\Handler\EntityFormHandler;
use App\Repository\CategoryRepository;
use App\Service\CategoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminCategoryController extends AbstractController
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly EntityFormHandler  $entityFormHandler,
        private readonly CategoryService    $categoryService
    )
    {
    }

    #[Route('/admin/categories', name: 'app_admin_categories')]
    public function categories(): Response
    {
        return $this->render('admin/category/categories.html.twig', [
            'categories' => $this->categoryRepository->findAll()
        ]);
    }

    #[Route('/admin/create-category', name: 'app_admin_create_category')]
    public function addCategory(Request $request): Response
    {
        $form = $this->entityFormHandler->handle($this->getUser(), $request, new Category, $this->categoryService);

        if ($form === true) {
            $this->addFlash('success', 'Successfully added new category!');
            return $this->redirectToRoute('app_admin_categories');
        }

        return $this->render('admin/category/create-category.html.twig', [
            'categoryForm' => $form
        ]);
    }

    #[Route('/admin/category/{id}/edit', name: 'app_admin_edit_category')]
    public function editCategory(?Category $category, Request $request): Response
    {
        $form = $this->entityFormHandler->handle($this->getUser(), $request, $category, $this->categoryService);

        if ($form === true) {
            $this->addFlash('success', 'Successfully edited category!');
            return $this->redirectToRoute('app_admin_categories');
        }

        return $this->render('admin/category/edit-category.html.twig', [
            'categoryForm' => $form
        ]);
    }


    #[Route('/admin/category/{id}/delete', name: 'app_admin_delete_category')]
    public function deleteCategory(?Category $category): Response
    {
        $this->categoryService->deleteCategory($category);

        $this->addFlash('success', 'Removed category');
        return $this->redirectToRoute('app_index');
    }

}