<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\Type\CategoryFormType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminCategoryController extends AbstractController
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly EntityManagerInterface $entityManager
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
        $category = new Category();

        $form = $this->createForm(CategoryFormType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();

            $this->entityManager->persist($category);
            $this->entityManager->persist($category->setAddedBy($this->getUser()));

            $this->entityManager->flush();

            $this->addFlash('success', 'Successfully added new category!');
            return $this->redirectToRoute('app_admin_categories');
        }

        return $this->render('admin/category/create-category.html.twig', [
            'categoryForm' => $form
        ]);
    }


}