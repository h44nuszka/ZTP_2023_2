<?php
/**
 * Book Controller test.
 */

namespace App\Tests\Controller;

use App\Entity\Book;
use App\Service\CategoryService;
use App\Entity\Category;
use App\Service\BookService;
use App\Entity\Enum\UserRole;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;


/**
 * Class BookControllerTest.
 */
class BookControllerTest extends WebTestCase
{

    private ?EntityManagerInterface $entityManager;

     /**
     * Category service.
     */
    private ?CategoryService $categoryService;


    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/book';

    /**
     * Test client.
     */
    private KernelBrowser $httpClient;

    /**
     * Set up tests.
     */
    public function setUp(): void
    {
        $this->httpClient = static::createClient();
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');

        $this->categoryService = $container->get(CategoryService::class);

    }

    /**
     * Test index route for anonymous user.
     */
    public function testIndexRouteAnonymousUser(): void
    {
        // given
        $expectedStatusCode = 301;

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test index route for admin user.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testIndexRouteAdminUser(): void
    {
        // given
        $this->removeUser();
        $expectedStatusCode = 200;
        $adminUser = $this->createUser([UserRole::ROLE_USER->value, UserRole::ROLE_ADMIN->value]);
        $this->httpClient->loginUser($adminUser);

        // when
        $this->httpClient->followRedirects(true);
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }


     /**
     * Test new route for admin user.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testNewRouteAdminUser(): void
    {
        // given
        $this->removeUser();
        $expectedStatusCode = 200;
        $adminUser = $this->createUser([UserRole::ROLE_USER->value, UserRole::ROLE_ADMIN->value]);
        $this->httpClient->loginUser($adminUser);

        // when
        $this->httpClient->followRedirects(true);
        $this->httpClient->request('GET', '/book/new');
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

       /**
     * Test new route for anonymous user.
     */
    public function testNewRouteAnonymousUser(): void
    {
        // given
        $expectedStatusCode = 302;

        // when
        $this->httpClient->request('GET', '/book/new');
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test edit route for admin user.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testEditRouteAdminUser(): void
    {
        // given
        $this->removeUser();
        $expectedStatusCode = 200;
        $adminUser = $this->createUser([UserRole::ROLE_USER->value, UserRole::ROLE_ADMIN->value]);
        $this->httpClient->loginUser($adminUser);
        $expectedStatusCode = 200;
        $expectedBook = new Book();
        $expectedBook->setTitle('Test Book');
        $expectedBook->setAuthor('Test Author');
        $expectedBook->setCreatedAt(new \DateTime());
        $category = $this->createCategory();
        $expectedBook->setCategory($category);
        $this->entityManager->persist($expectedBook);
        $this->entityManager->flush();
        $expectedBookId = $expectedBook->getId();

        // when
        $this->httpClient->followRedirects(true);
        $this->httpClient->request('GET', '/book/edit/' . strval($expectedBookId));
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

       /**
     * Test edit route for anonymous user.
     */
    public function testEditRouteAnonymousUser(): void
    {
        // given
        $expectedStatusCode = 302;
        $expectedBook = new Book();
        $expectedBook->setTitle('Test Book');
        $expectedBook->setAuthor('Test Author');
        $expectedBook->setCreatedAt(new \DateTime());
        $category = $this->createCategory();
        $expectedBook->setCategory($category);
        $this->entityManager->persist($expectedBook);
        $this->entityManager->flush();
        $expectedBookId = $expectedBook->getId();

        // when
        $this->httpClient->request('GET', '/book/edit/' . strval($expectedBookId));
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test show route for admin user.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testShowRouteAdminUser(): void
    {
        // given
        $this->removeUser();
        $expectedStatusCode = 200;
        $adminUser = $this->createUser([UserRole::ROLE_USER->value, UserRole::ROLE_ADMIN->value]);
        $this->httpClient->loginUser($adminUser);

        $expectedStatusCode = 200;
        $expectedBook = new Book();
        $expectedBook->setTitle('Test Book');
        $expectedBook->setAuthor('Test Author');
        $expectedBook->setCreatedAt(new \DateTime());
        $category = $this->createCategory();
        $expectedBook->setCategory($category);
        $this->entityManager->persist($expectedBook);
        $this->entityManager->flush();
        $expectedBookId = $expectedBook->getId();

        // when
        $this->httpClient->followRedirects(true);
        $this->httpClient->request('GET', '/book/show/' . strval($expectedBookId));
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

       /**
     * Test show route for anonymous user.
     */
    public function testShowRouteAnonymousUser(): void
    {
        // given
        $expectedStatusCode = 200;
        $expectedBook = new Book();
        $expectedBook->setTitle('Test Book');
        $expectedBook->setAuthor('Test Author');
        $expectedBook->setCreatedAt(new \DateTime());
        $category = $this->createCategory();
        $expectedBook->setCategory($category);
        $this->entityManager->persist($expectedBook);
        $this->entityManager->flush();
        $expectedBookId = $expectedBook->getId();

        // when
        $this->httpClient->request('GET', '/book/show/' . strval($expectedBookId));
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }




    /**
     * Create user.
     *
     * @param array $roles User roles
     *
     * @return User User entity
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    private function createUser(array $roles): User
    {
        $passwordHasher = static::getContainer()->get('security.password_hasher');
        $user = new User();
        $user->setEmail('test2@example.com');
        $user->setRoles($roles);
        $user->setName('test2');
        $user->setPassword(
            $passwordHasher->hashPassword(
                $user,
                'p@55w0rd'
            )
        );
        $userRepository = static::getContainer()->get(UserRepository::class);
        $userRepository->add($user);

        return $user;
    }

    private function removeUser(): void
    {

        $userRepository = static::getContainer()->get(UserRepository::class);
        $entity = $userRepository->findOneBy(array('email' => 'test2@example.com'));


        if ($entity != null){
            $userRepository->remove($entity);
        }

    }


    public function createCategory(): Category
    {
        $category = new Category();
        $category->setName('Test Category');
        $this->categoryService->add($category);
        return $category;
    }

}