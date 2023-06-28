<?php
/**
 * Comment Controller Tests
 */
namespace App\Tests\Controller;

use App\Controller\CommentController;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Enum\UserRole;
use App\Entity\Recipe;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Repository\RecipeRepository;
use App\Repository\UserRepository;
use App\Service\CategoryService;
use App\Service\CommentService;
use App\Service\RecipeService;
use DateTimeImmutable;
use http\Client\Request;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class comment controller test
 */
class CommentControllerTest extends WebTestCase
{
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/comment';

    /**
     * Set up tests
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
        $this->commentService = $container->get(CommentService::class);

    }

    /**
     * Test deleting comments for admin
     */
    public function testDeleteCommentAdmin()
    {
        // given
        $admin = $this->loginAdmin();
        $recipe = $this->createRecipe($admin);

        $expectedStatusCode = 200;
        $deletedComment = $this->createComment($recipe, $admin, 'test comment');
        $deletedCommentId = $deletedComment->getId();

        $expectedResult = 'not found';

        //when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.strval($deletedCommentId) .'/delete');
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        $this->httpClient->submitForm(
            'Usuń'
        );


        if ($this->commentRepository->findOneBy(array('id'=>$deletedCommentId))){
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

        $this->commentService->save($comment);

        return $comment;
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
     * Login admin
     * @return User
     */
    private function loginAdmin(): User
    {
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value]);
        $this->httpClient->loginUser($adminUser);

        return $adminUser;
    }
}
