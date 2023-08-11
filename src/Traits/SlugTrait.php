<?php

namespace App\Traits;

use Symfony\Component\String\Slugger\AsciiSlugger;

trait SlugTrait
{
    private function createSlug(string $toSlug): string
    {
        $slugger = new AsciiSlugger();
        $slug = $slugger->slug($toSlug)->lower();

        return $slug . '_' . uniqid();
    }
}