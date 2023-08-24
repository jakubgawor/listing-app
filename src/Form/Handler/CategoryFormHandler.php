<?php

namespace App\Form\Handler;

use App\Entity\Category;
use App\Entity\User;
use App\Form\Type\CategoryFormType;
use App\Service\CategoryService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class CategoryFormHandler
{
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly CategoryService      $categoryService,
    )
    {
    }

    public function handle(Request $request, User $user, Category $category): FormInterface|bool
    {
        $form = $this->formFactory->create(CategoryFormType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();

            if ($category->getAddedBy() === null) {
                $this->categoryService->createCategory($category, $user);
            } else {
                $this->categoryService->editCategory($category);
            }

            return true;
        }

        return $form;
    }
}