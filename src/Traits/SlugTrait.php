<?php

namespace App\Traits;

use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Uid\Uuid;

trait SlugTrait
{
    public function createSlug(string $toSlug): string
    {
        $slugger = new AsciiSlugger();
        $slug = $slugger->slug($toSlug)->lower();

        return $slug . '_' . Uuid::v7();
    }
}