<?php
/**
 * Category controller test
 */
namespace App\Tests\Controller;

use App\Controller\CategoryController;
use App\Entity\Category;
use App\Entity\Enum\UserRole;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Repository\RecipeRepository;
use App\Repository\UserRepository;
use App\Service\CategoryService;
use App\Service\RecipeService;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class category controller test
 */
class CategoryControllerTest extends WebTestCase
{
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/category';
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
     * Set up tests
     */
    public function setUp(): void
    {
        $this->httpClient = static::createClient();
        $container = static::getContainer();
        $this->categoryService = $container->get(CategoryService::class);
        $this->categoryRepository = $container->get(CategoryRepository::class);
        $this->userRepository = $container->get(UserRepository::class);
    }

    /**
     * Test index route of logged-in user
     */
    public function testIndex()
    {
        // given
        $expectedStatusCode = 200;
        $this->loginAdminUser();

        //when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);

    }

    /**
     * Test show route for logged-in user
     */
    public function testShow(): void
    {
        // given

        $adminUser = $this->loginAdminUser();

        $expectedStatusCode = 200;
        $expectedCategory = $this->createCategory();
        $expectedCategoryId = $expectedCategory->getId();

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'. strval($expectedCategoryId));
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test create route for logged-in user
     */
    public function testCreate()
    {
        //given

        $expectedStatusCode = 200;
        $this->loginAdminUser();

        //when
        $this->httpClient->request('GET', self::TEST_ROUTE .'/create');
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    public function testEdit()
    {
        // given
        $adminUser = $this->loginAdminUser();

        $editedCategory = $this->createCategory();
        $editedCategoryId = $editedCategory->getId();

        $expectedEditedCategoryTitle = 'edited category';

        //when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.strval($editedCategoryId) .'/edit');
        $this->httpClient->submitForm(
            'Zapisz',
            ['category' => ['title' => $expectedEditedCategoryTitle]],
            'PUT'
        );

        // then
        $resultCategory = $this->categoryRepository->findOneBy(array('id'=>$editedCategoryId));
        $resultCategoryTitle = $resultCategory->getTitle();
        $this->assertEquals($expectedEditedCategoryTitle, $resultCategoryTitle);
    }


    /**
     * Test delete route for logged in user
     */
    public function testDelete()
    {
        // given
        $adminUser = $this->loginAdminUser();

        $expectedStatusCode = 200;
        $deletedCategory = $this->createCategory();
        $deletedCategoryId = $deletedCategory->getId();

        $expectedResult = 'not found';

        //when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.strval($deletedCategoryId) .'/delete');
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        $this->httpClient->submitForm(
            'Usuń'
        );


        if ($this->categoryRepository->findOneBy(array('id'=>$deletedCategoryId))){
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
     * Login with user and admin roles
     * @return User
     */
    private function loginAdminUser(): User
    {
        $adminUser = $this->createUser([UserRole::ROLE_USER->value, UserRole::ROLE_ADMIN->value]);
        $this->httpClient->loginUser($adminUser);

        return $adminUser;
    }

}
