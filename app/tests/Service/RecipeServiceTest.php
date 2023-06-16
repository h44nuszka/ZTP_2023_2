<?php
/**
 * Recipe Service test.
 */

namespace App\Tests\Service;

use App\Entity\Category;
use App\Entity\Enum\UserRole;
use App\Entity\Recipe;
use App\Entity\Tag;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Repository\RecipeRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use App\Service\CategoryService;
use App\Service\RecipeService;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class recipe service test
 */
class RecipeServiceTest extends WebTestCase
{
    /**
     * Test client
     * @var KernelBrowser
     */
    private KernelBrowser $httpClient;

    /**
     * Set up tests.
     */
    public function setUp(): void
    {
        $container = static::getContainer();
        $this->categoryService = $container->get(CategoryService::class);
        $this->tagRepository = $container->get(TagRepository::class);
        $this->recipeService = $container->get(RecipeService::class);
        $this->recipeRepository = $container->get(RecipeRepository::class);
        $this->userRepository = $container->get(UserRepository::class);
        $this->categoryRepository = $container->get(CategoryRepository::class);
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
    }

    /**
     * Test save method
     */
    public function testSave()
    {
        // given
        //create user and category

        $user = $this->createUser((array)UserRole::ROLE_ADMIN);
        $category = $this->createCategory();

        $recipe = new Recipe();
        $recipe->setAuthor($user);
        $recipe->setName('test recipe');
        $recipe->setCreatedAt(new DateTimeImmutable());
        $recipe->setContent('test content');
        $recipe->setCategory($category);

        // when
        $this->recipeService->save($recipe);

        // then

        $expectedRecipeId = $recipe->getId();
        $resultRecipe = $this->entityManager->createQueryBuilder()
            ->select('recipe')
            ->from(Recipe::class, 'recipe')
            ->where('recipe.id = :id')
            ->setParameter(':id', $expectedRecipeId, Types::INTEGER)
            ->getQuery()
            ->getSingleResult();

        $this->assertEquals($recipe, $resultRecipe);
    }

    /**
     * Test delete method
     */
    public function testDelete():void
    {
        // given
        $user = $this->createUser((array)UserRole::ROLE_USER);
        $recipe = $this->createRecipe($user);
        $expectedRecipeId = $recipe->getId();
        //when
        $this->recipeService->delete($recipe);

        //then

        $result = $this->entityManager->createQueryBuilder()
            ->select('recipe')
            ->from(Recipe::class, 'recipe')
            ->where('recipe.id = :id')
            ->setParameter(':id', $expectedRecipeId, Types::INTEGER)
            ->getQuery()
            ->getOneOrNullResult();

        $this->assertNull($result);
    }


    /**
     * Create user
     * @param array $roles
     * @return User
     */
    private function createUser(array $roles):User
    {
        $passwordHasher = static::getContainer()->get('security.password_hasher');
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setRoles($roles);
        $user->setPassword(
            $passwordHasher->hashPassword(
                $user,
                'p@55w0rd'
            )
        );
        $this->userRepository->save($user, true);

        return $user;
    }

    /**
     * Create category
     * @return Category
     */
    private function createCategory(): Category
    {
        $category = new Category();
        $category->setTitle('test category');
        $this->categoryService->save($category);

        return $category;
    }

    /**
     * Create tag
     * @return Tag
     */
    private function createTag(): Tag
    {
        $tag = new Tag();
        $tag->setTitle('test tag');
        $tag->setCreatedAt(new DateTimeImmutable());
        $tag->setUpdatedAt(new DateTimeImmutable());
        $tag->setSlug('test tag');
        $this->tagRepository->save($tag, true);

        return $tag;
    }

    /**
     * CREATE RECIPE
     * @param $user
     * @return Recipe
     */
    private function createRecipe($user): Recipe
    {
        $recipe = new Recipe();
        $recipe->setAuthor($user);
        $recipe->setName('test recipe');
        $recipe->setCreatedAt(new DateTimeImmutable());
        $recipe->setContent('test content');
        $category = $this->createCategory();
        $recipe->setCategory($category);
        $tag = $this->createTag();
        $recipe->addTag($tag);
        $this->recipeService->save($recipe);

        return $recipe;
    }
}
