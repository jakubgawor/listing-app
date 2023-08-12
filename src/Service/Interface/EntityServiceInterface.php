<?php

namespace App\Service\Interface;

use App\Entity\Interface\EntityMarkerInterface;
use App\Entity\User;

interface EntityServiceInterface
{
    public function handleEntity(User $user, EntityMarkerInterface $entity);
}