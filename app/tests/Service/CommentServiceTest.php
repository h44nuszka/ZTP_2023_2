<?php
/**
 * Comment service tests
 */
namespace App\Tests\Service;

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
use Doctrine\DBAL\Types\Types;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class comment service tests
 */
class CommentServiceTest extends WebTestCase
{
    /**
     * Set up tests
     */
    public function setUp(): void
    {
        $container = static::getContainer();
        $this->commentRepository = $container->get(CommentRepository::class);
        $this->commentService = $container->get(CommentService::class);
        $this->userRepository = $container->get(UserRepository::class);
        $this->categoryRepository = $container->get(CategoryRepository::class);
        $this->recipeRepository = $container->get(RecipeRepository::class);
        $this->recipeService = $container->get(RecipeService::class);
        $this->categoryService = $container->get(CategoryService::class);
        $this->entityManager = $container->get('doctrine.orm.entity_manager');

    }

    /**
     * Test save methods
     */
    public function testSave()
    {
        // given
        //create user and category
        $this->removeRecipe('test recipe');
        $this->removeCategory();
        $this->removeComment();
        $this->removeUser();

        $user = $this->createUser((array)UserRole::ROLE_ADMIN);
        $recipe = $this->createRecipe($user);

        $comment = new Comment();
        $comment->setRecipe($recipe);
        $comment->setAuthor($user);
        $comment->setCreatedAt(new DateTimeImmutable());
        $comment->setContent('test comment');


        // when
        $this->commentService->save($comment);

        // then
        $expectedCommentId = $comment->getId();
        $resultComment = $this->entityManager->createQueryBuilder()
            ->select('comment')
            ->from(Comment::class, 'comment')
            ->where('comment.id = :id')
            ->setParameter(':id', $expectedCommentId, Types::INTEGER)
            ->getQuery()
            ->getSingleResult();

        $this->assertEquals($comment, $resultComment);
    }

    /**
     * Test delete method
     */
    public function testDelete()
    {
        // given
        $this->removeRecipe('test recipe');
        $this->removeCategory();
        $this->removeComment();
        $this->removeUser();


        $user = $this->createUser((array)UserRole::ROLE_USER);

        $recipe = $this->createRecipe($user);
        $comment = $this->createComment($recipe, $user, 'test comment');
        $expectedCommentId = $comment->getId();

        //when
        $this->commentService->delete($comment);

        //then
        $result = $this->entityManager->createQueryBuilder()
            ->select('comment')
            ->from(Comment::class, 'comment')
            ->where('comment.id = :id')
            ->setParameter(':id', $expectedCommentId, Types::INTEGER)
            ->getQuery()
            ->getOneOrNullResult();

        $this->assertNull($result);

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
     * Remove comment
     */
    private function removeComment(): void
    {
        $entity = $this->commentRepository->findOneBy(array('content'=>'test content'));

        if ($entity != null)
        {
            $this->commentRepository->delete($entity);
        }
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
     * Remove recipe
     * @param string $name
     */
    private function removeRecipe(string $name): void
    {
        $entity = $this->recipeRepository->findOneBy(array('name'=>$name));

        if ($entity != null)
        {
            $this->recipeRepository->delete($entity);
        }
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
     * Remove category
     */
    private function removeCategory(): void
    {
        $entity = $this->categoryRepository->findOneBy(array('title'=>'test category'));

        if ($entity != null)
        {
            $this->categoryRepository->delete($entity);
        }
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
     * Remove user
     */
    private function removeUser(): void
    {
        $entity = $this->userRepository->findOneBy(array('email'=>'test@example.com'));

        if ($entity != null)
        {
            $this->userRepository->remove($entity, true);
        }
    }
}
