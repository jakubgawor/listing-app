<?php

namespace App\Tests\Builder;

use App\Entity\Category;
use App\Entity\Listing;
use App\Entity\User;

interface EntityBuilderInterface
{
    public function createUser(array $data): User;

    public function createListing(string $title, string $description, string $status, User $user, Category $category): Listing;

    public function createCategory(string $categoryName, User $addedBy);
}
