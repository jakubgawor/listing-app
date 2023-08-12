<?php

namespace App\Traits;

use App\Entity\Interface\EntityMarkerInterface;
use InvalidArgumentException;

trait EntityCheckerTrait
{
    public function checkEntityType(EntityMarkerInterface $entity, string $expectedType): void
    {
        if (!$entity instanceof $expectedType) {
            throw new InvalidArgumentException('Unsupported entity type');
        }
    }
}