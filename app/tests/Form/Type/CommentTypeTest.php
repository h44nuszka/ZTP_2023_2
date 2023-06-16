<?php
/**
 * Comment type tests
 */
namespace App\Tests\Form\Type;

use App\Entity\Comment;
use App\Form\Type\CommentType;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * Comment type test class
 */
class CommentTypeTest extends TypeTestCase
{
    /**
     * Test for submitting valid data
     */
    public function testSubmitValidData(): void
    {
        // given
        $formData = [
            'content' => 'test content',
        ];
        $model = new Comment();
        $form = $this->factory->create(CommentType::class, $model);

        $expected = new Comment();
        $expected->setContent('test content');

        // when
        $form->submit($formData);

        //then
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expected, $model);
    }
}
