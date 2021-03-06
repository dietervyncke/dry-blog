<?php

namespace Tnt\Blog\Facade;

use Oak\Facade;
use Tnt\Blog\Contracts\BlogPostRepositoryInterface;

class BlogPosts extends Facade
{
    /**
     * @return string
     */
    protected static function getContract(): string
    {
        return BlogPostRepositoryInterface::class;
    }
}