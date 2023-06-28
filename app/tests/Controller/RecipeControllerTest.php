<?php
/**
 * Recipe Controller test.
 */

namespace App\Tests\Controller;

use App\Controller\RecipeController;
use App\Entity\Comment;
use App\Entity\Enum\UserRole;
use App\Entity\Recipe;
use App\Entity\User;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Repository\RecipeRepository;
use App\Repository\UserRepository;
use App\Service\RecipeService;
use App\Service\CategoryService;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class recipe controller test
 */
class RecipeControllerTest extends WebTestCase
{
    /**
     * Test client
     * @var KernelBrowser
     */
    private KernelBrowser $httpClient;

    /**
     * Category service.
     */
    private ?CategoryService $categoryService;

    /**
     * Recipe service
     * @var RecipeService|object|null
     */
    private ?RecipeService $recipeService;

    /**
     * Set up tests.
     */
    public function setUp(): void
    {
        $this->httpClient = static::createClient();
        $container = static::getContainer();
        $this->categoryService = $container->get(CategoryService::class);
        $this->recipeService = $container->get(RecipeService::class);
        $this->recipeRepository = $container->get(RecipeRepository::class);
        $this->userRepository = $container->get(UserRepository::class);
        $this->categoryRepository = $container->get(CategoryRepository::class);
        $this->commentRepository = $container->get(CommentRepository::class);

    }


    /**
     * Test route.
     */
    public function testIndexForAnonymous(): void
    {
        // given
        $expectedStatusCode = 302;

        // when
        $this->httpClient->request('GET', '/');
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test index route for regular/admin user
     */
    public function testIndexAdminUser(): void
    {
        // given
        $expectedStatusCode = 200;
        $this->loginAdminUser();

        // when
        $this->httpClient->request('GET', '/');
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test show for anonymous
     */
    public function testShowAnonymous(): void
    {
        // given

        $expectedStatusCode = 302;
        $expectedRecipe = $this->createRecipe($this->createUser((array)UserRole::ROLE_USER));
        $expectedRecipeId = $expectedRecipe->getId();

        // when
        $this->httpClient->request('GET', strval($expectedRecipeId));
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test show for admin/user
     */
    public function testShowAdminUser(): void
    {
        // given
        $adminUser = $this->loginAdminUser();

        $expectedStatusCode = 200;
        $expectedRecipe = $this->createRecipe($adminUser);
        $expectedRecipeId = $expectedRecipe->getId();

        // when
        $this->httpClient->request('GET', strval($expectedRecipeId));
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test create for anonymous
     */
    public function testCreateAnonymous(): void
    {
        // given
        $expectedStatusCode = 302;

        //when
        $this->httpClient->request('GET', '/create');
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test create for admin/user
     */
    public function testCreateRouteAdminUser(): void
    {
        // given
        $expectedStatusCode = 200;
        $this->loginAdminUser();

        //when

        $this->httpClient->request('GET', '/create');
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test edit for admin/user
     */
    public function testEditAdminUser(): void
    {
        // given

        $adminUser = $this->loginAdminUser();

        $editedRecipe = $this->createRecipe($adminUser);
        $editedRecipeId = $editedRecipe->getId();

        $expectedEditedRecipeName = 'edited recipe';
        //when
        $this->httpClient->request('GET', strval($editedRecipeId) .'/edit');
        $this->httpClient->submitForm(
            'Zapisz',
            ['recipe' => ['name' => $expectedEditedRecipeName]]
        );


        $resultRecipe = $this->recipeRepository->findOneBy(array('id'=>$editedRecipeId));
        $resultRecipeName = $resultRecipe->getName();
        // then
        $this->assertEquals($expectedEditedRecipeName, $resultRecipeName);
    }

    /**
     * Test edit for anonymous
     */
    public function testEditAnonymous(): void
    {
        // given
        $expectedStatusCode = 302;


        $editedRecipe = $this->createRecipe($this->createUser((array)UserRole::ROLE_USER));
        $editedRecipeId = $editedRecipe->getId();

        //when
        $this->httpClient->request('GET', strval($editedRecipeId) .'/edit');
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();
        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test delete for admin/user
     */
    public function testDeleteAdminUser(): void
    {
        // given
        $adminUser = $this->loginAdminUser();

        $expectedStatusCode = 200;
        $deletedRecipe = $this->createRecipe($adminUser);
        $deletedRecipeId = $deletedRecipe->getId();

        $expectedResult = 'not found';

        //when
        $this->httpClient->request('GET', strval($deletedRecipeId) .'/delete');
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        $this->httpClient->submitForm(
            'Usuń'
        );


        if ($this->recipeRepository->findOneBy(array('id'=>$deletedRecipeId))){
            $result = 'found';
        }
        else{
            $result = 'not found';
        }

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Test delete for anonymous
     */
    public function testDeleteAnonymous(): void
    {
        // given
        $expectedStatusCode = 302;
        $expectedRecipe = $this->createRecipe($this->createUser((array)UserRole::ROLE_USER));
        $expectedRecipeId = $expectedRecipe->getId();
        //when
        $this->httpClient->request('GET', strval($expectedRecipeId) .'/delete');
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

//
//    public function testCommentAdminUser(): void
//    {
//        // given
//
//        $adminUser = $this->loginAdminUser();
//
//        $expectedStatusCode = 200;
//        $expectedRecipe = $this->createRecipe($adminUser);
//        $expectedRecipeId = $expectedRecipe->getId();
//        $expectedComment = $this->createComment($expectedRecipe, $adminUser, 'test content');
//
//        //when
//        $this->httpClient->request('GET', '/'.strval($expectedRecipeId) .'/comment');
//        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();
//        $this->httpClient->submitForm(
//            'Zapisz',
//            ['comment' => ['content' => 'test content']]
//        );
//        $resultComment = $this->commentRepository->findOneBy(array('content'=>'test content'));
//
//        // then
//        $this->assertEquals($expectedStatusCode, $resultStatusCode);
//        $this->assertEquals($expectedComment, $resultComment);
//    }

    /**
     * Test comment for anonymous
     */
    public function testCommentAnonymous(): void
    {
        // given

        $expectedStatusCode = 302;
        $expectedRecipe = $this->createRecipe($this->createUser((array)UserRole::ROLE_USER));
        $expectedRecipeId = $expectedRecipe->getId();
        //when
        $this->httpClient->request('GET', strval($expectedRecipeId) .'/comment');
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
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
     * Create recipe
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

        $this->recipeService->save($recipe);

        return $recipe;
    }

    /**
     * Create comment
     * @param Recipe $recipe
     * @param User $author
     * @param string $content
     * @return Comment
     */
    private function createComment(Recipe $recipe, User $author, string $content):Comment
    {
        $comment = new Comment();
        $comment->setRecipe($recipe);
        $comment->setAuthor($author);
        $comment->setCreatedAt(new DateTimeImmutable());
        $comment->setContent($content);

        $commentRepository = static::getContainer()->get(CommentRepository::class);
        $commentRepository->save($comment);

        return $comment;
    }

    /**
     * Login admin/user
     * @return User
     */
    private function loginAdminUser(): User
    {
        $adminUser = $this->createUser([UserRole::ROLE_USER->value, UserRole::ROLE_ADMIN->value]);
        $this->httpClient->loginUser($adminUser);

        return $adminUser;
    }
}