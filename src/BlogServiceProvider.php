<?php

namespace Tnt\Blog;

use dry\admin\Portal;
use Oak\Contracts\Config\RepositoryInterface;
use Oak\Contracts\Container\ContainerInterface;
use Oak\Migration\MigrationManager;
use Oak\Migration\Migrator;
use Oak\ServiceProvider;
use Tnt\Blog\Admin\BlogCategoryManager;
use Tnt\Blog\Admin\BlogAuthorManager;
use Tnt\Blog\Admin\BlogPostManager;
use Tnt\Blog\Contracts\BlogPostRepositoryInterface;
use Tnt\Blog\Revisions\CreateBlogCategoryTable;
use Tnt\Blog\Revisions\CreateBlogAuthorTable;
use Tnt\Blog\Revisions\CreateBlogPostBlockTable;
use Tnt\Blog\Revisions\CreateBlogPostPhotoTable;
use Tnt\Blog\Revisions\CreateBlogPostTable;

class BlogServiceProvider extends ServiceProvider
{
    /**
     * @param ContainerInterface $app
     * @return mixed|void
     */
    public function register(ContainerInterface $app)
    {
        $app->set(BlogPostRepositoryInterface::class, BlogPostRepository::class);
    }

    /**
     * @param ContainerInterface $app
     * @return mixed|void
     */
    public function boot(ContainerInterface $app)
    {
        if ($app->isRunningInConsole()) {

            $migrator = $app->getWith(Migrator::class, [
                'name' => 'blog',
            ]);

            $migrator->setRevisions([
                CreateBlogCategoryTable::class,
                CreateBlogAuthorTable::class,
                CreateBlogPostTable::class,
                CreateBlogPostBlockTable::class,
                CreateBlogPostPhotoTable::class,
            ]);

            $app->get(MigrationManager::class)
                ->addMigrator($migrator);
        }

        $this->registerAdminModules($app);
    }

    /**
     * @param ContainerInterface $app
     */
    private function registerAdminModules(ContainerInterface $app)
    {
        $hasCategories = $app->get(RepositoryInterface::class)->get('blog.categories', true);
        $hasAuthors = $app->get(RepositoryInterface::class)->get('blog.authors', true);
        $hasPhotos = $app->get(RepositoryInterface::class)->get('blog.photos', true);
        $advancedLayout = $app->get(RepositoryInterface::class)->get('blog.advanced-layout', true);
        $blockTypes = $app->get(RepositoryInterface::class)->get('blog.types', [
            'text-photo',
            'photo-text',
            'photo',
            'text',
            'textframe',
            'quote',
        ]);
        $languages = $app->get(RepositoryInterface::class)->get('blog.languages', [
            'nl',
            'en',
            'fr'
        ]);

        $modules = [
            new BlogPostManager([
                'categories' => $hasCategories,
                'authors' => $hasAuthors,
                'photos' => $hasPhotos,
                'advancedLayout' => $advancedLayout,
                'blockTypes' => $blockTypes,
                'languages' => $languages,
            ]),
        ];

        if ($hasCategories) {
            $modules[] = new BlogCategoryManager($languages);
        }

        if ($hasAuthors) {cd
            $modules[] = new BlogAuthorManager($languages);
        }

        array_unshift(\dry\admin\Router::$modules, new Portal('blog', 'Blog', $modules));
    }
}
