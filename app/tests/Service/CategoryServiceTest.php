<?php
/**
 * Category serice tests
 */
namespace App\Tests\Service;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\RecipeRepository;
use App\Service\CategoryService;
use App\Service\CategoryServiceInterface;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use function PHPUnit\Framework\assertEquals;

/**
 * Class category service test
 */
class CategoryServiceTest extends WebTestCase
{
    /**
     * Category repository
     */
    private EntityManagerInterface $entityManager;

    /**
     * Category service
     */
    private CategoryServiceInterface $categoryService;

    /**
     * Set up tests
     */
    public function setUp():void
    {
        $container = static::getContainer();
        $this->recipeRepository = $container->get(RecipeRepository::class);
        $this->categoryRepository = $container->get(CategoryRepository::class);
        $this->categoryService = $container->get(CategoryService::class);
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
    }

    /**
     * Test save method
     */
    public function testSave(): void
    {
        // given

        $category = new Category();
        $category->setTitle('test category');
        $category->setUpdatedAt(new DateTimeImmutable());
        $category->setCreatedAt(new DateTimeImmutable());

        // when
        $this->categoryService->save($category);

        // then
        $expectedCategoryId = $category->getId();
        $resultCategory = $this->entityManager->createQueryBuilder()
            ->select('category')
            ->from(Category::class, 'category')
            ->where('category.id = :id')
            ->setParameter(':id', $expectedCategoryId, Types::INTEGER)
            ->getQuery()
            ->getSingleResult();

        $this->assertEquals($category, $resultCategory);
    }

    /**
     * Test delete method
     */
    public function testDelete()
    {
        // given

        $category = $this->createCategory();
        $expectedCategoryId = $category->getId();

        //when
        $this->categoryService->delete($category);

        //then
        $result = $this->entityManager->createQueryBuilder()
            ->select('category')
            ->from(Category::class, 'category')
            ->where('category.id = :id')
            ->setParameter(':id', $expectedCategoryId, Types::INTEGER)
            ->getQuery()
            ->getOneOrNullResult();

        $this->assertNull($result);
    }

    /**
     * Test pagination empty list.
     */
    public function testGetPaginatedList(): void
    {
        // given
        $page = 1;
        $dataSetSize = 3;
        $expectedResultSize = 3;

        $counter = 0;
        while ($counter < $dataSetSize) {
            $category = new Category();
            $category->setTitle('Test Category #'.$counter);
            $this->categoryService->save($category);

            ++$counter;
        }

        // when
        $result = $this->categoryService->getPaginatedList($page);

        // then
        $this->assertEquals($expectedResultSize, $result->count());
    }

    public function testCanBeDeleted(): void
    {
        // given
        $expectedAnswer = true;
        $category = $this->createCategory();
        if ($this->recipeRepository->countByCategory($category) > 0)
        {
            $expectedAnswer = false;
        }
        // when
        $result = $this->categoryService->canBeDeleted($category);

        //then
        $this->assertEquals($expectedAnswer, $result);
    }


    /**
     * Create category
     * @param $title
     * @return category
     */
    private function createCategory($title='test category'): Category
    {
        $category = new Category();
        $category->setTitle($title);
        $this->categoryService->save($category);

        return $category;
    }

}
