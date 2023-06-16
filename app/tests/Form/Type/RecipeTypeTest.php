<?php
//
//namespace App\Tests\Form\Type;
//
//use App\Entity\Category;
//use App\Entity\Recipe;
//use App\Entity\Tag;
//use App\Form\Type\RecipeType;
//use App\Repository\TagRepository;
//use App\Service\CategoryService;
//use Doctrine\Persistence\ObjectManager;
//use PHPUnit\Framework\TestCase;
//use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
//use Symfony\Component\Form\PreloadedExtension;
//use Symfony\Component\Form\Test\TypeTestCase;
//
//class RecipeTypeTestWeb extends WebTestCase{
//    public function setUp():void
//    {
//        $container = static::getContainer();
//        $this->categoryService = $container->get(CategoryService::class);
//        $this->tagRepository = $container->get(TagRepository::class);
//    }
//}
//class RecipeTypeTest extends TypeTestCase
//{
//    protected function setUp(): void
//    {
//        // mock any dependencies
//        $this->objectManager = $this->createMock(ObjectManager::class);
//
//        parent::setUp();
//    }
//    protected function getExtensions()
//    {
//        // create a type instance with the mocked dependencies
//        $type = new RecipeType($this->objectManager);
//
//        return [
//            // register the type instances with the PreloadedExtension
//            new PreloadedExtension([$type], []),
//        ];
//    }
//    public function testSubmitValidDate(): void
//    {
//        // given
////        $category = $this->createMock(Category::class);
////        $category->setTitle('test category');
////        $tag = $this->createMock(Tag::class);
//
//        $formData = [
//            'name' => 'test name',
//            'content' => 'test content',
//            'category' => 'test',
//            'tag' => 'test'
//        ];
//        $form = $this->factory->create(RecipeType::class, $formData);
//
//        $expected = new Recipe();
//        $expected->setName('test name');
//        $expected->setContent('test content');
//        $expected->setCategory('test');
//
//        // when
//        $form->submit($formData);
//
//        //then
//        $this->assertTrue($form->isSynchronized());
//        $this->assertEquals($expected, $model);
//    }
//}
