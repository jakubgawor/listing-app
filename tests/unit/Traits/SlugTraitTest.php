<?php

namespace App\Tests\unit\Traits;

use App\Traits\SlugTrait;
use PHPUnit\Framework\TestCase;

class SlugTraitTest extends TestCase
{
    /** @test */
    public function createSlug_works_correctly()
    {
        $slugTrait = new class {
            use SlugTrait;
        };

        $result = $slugTrait->createSlug('to_Slug');

        $this->assertStringStartsWith('to-slug_', $result);
    }

}