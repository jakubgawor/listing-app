<?php

namespace App\Tests\Builder;

use App\Entity\Listing;
use App\Entity\User;

interface EntityBuilderInterface
{
    public function createUser(): User;

    public function createListing(string $title, string $description, string $status, User $user): Listing;
}
