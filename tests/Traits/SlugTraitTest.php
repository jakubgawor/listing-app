<?php

namespace App\Tests\Traits;

use App\Traits\SlugTrait;
use PHPUnit\Framework\TestCase;

class SlugTraitTest extends TestCase
{
    /** @test */
    public function create_slug()
    {
        $slugTrait = new class {
            use SlugTrait;
        };

        $result = $slugTrait->createSlug('to_Slug');

        $this->assertStringStartsWith('to-slug_', $result);
    }

}