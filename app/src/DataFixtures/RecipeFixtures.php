<?php
/**
 * Recipe fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Recipe;
use App\Entity\User;
use App\Entity\Tag;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class RecipeFixtures.
 */
class RecipeFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    /**
     * Load data.
     *
     * @psalm-suppress PossiblyNullPropertyFetch
     * @psalm-suppress PossiblyNullReference
     * @psalm-suppress UnusedClosureParam
     */
    public function loadData(): void
    {
        if (null === $this->manager || null === $this->faker) {
            return;
        }
        $this->createMany(100, 'recipes', function (int $i) {
            $recipe = new Recipe();
            $recipe->setName($this->faker->sentence);
            $recipe->setContent($this->faker->paragraph(10));
            $recipe->setCreatedAt(\DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-100 days', '-1 days')));

            /** @var Category $category */
            $category = $this->getRandomReference('categories');
            $recipe->setCategory($category);

            /** @var array<array-key, Tag> $tags */
            $tags = $this->getRandomReferences(
                'tags',
                $this->faker->numberBetween(0, 5)
            );
            foreach ($tags as $tag) {
                $recipe->addTag($tag);
            }

            //            $recipe->setStatus(RecipeStatus::from($this->faker->numberBetween(1,2)));
            //
            /** @var User %author */
            $author = $this->getRandomReference('users');
            $recipe->setAuthor($author);

            return $recipe;
        });

        $this->manager->flush();
    }

    /**
     * Get dependencies
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [CategoryFixtures::class, TagFixtures::class, UserFixtures::class];
    }
}
