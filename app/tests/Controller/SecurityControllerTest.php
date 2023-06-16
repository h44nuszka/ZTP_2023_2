<?php
/**
 * Security Controller Tests
 */
namespace App\Tests\Controller;

use App\Controller\SecurityController;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class security controller test
 */
class SecurityControllerTest extends WebTestCase
{

    /**
     * Set up tests
     */
    public function setUp(): void
    {
        $this->httpClient = static::createClient();
    }

    /**
     * Test login
     */
    public function testLogin()
    {
        //given
        $expectedStatusCode = 200;

        //when
        $this->httpClient->request('GET', '/login');

        //then
        $result = $this->httpClient->getResponse()->getStatusCode();

        $this->assertEquals($expectedStatusCode, $result);
    }

    /**
     * Test logout
     */
    public function testLogout()
    {
        //given
        $expectedStatusCode = 302;

        //when
        $this->httpClient->request('GET', '/logout');

        //then
        $result = $this->httpClient->getResponse()->getStatusCode();
        $this->assertEquals($expectedStatusCode, $result);
    }
}
