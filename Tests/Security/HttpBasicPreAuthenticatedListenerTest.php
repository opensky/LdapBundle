<?php

namespace OpenSky\Bundle\LdapBundle\Tests\Security;

use OpenSky\Bundle\LdapBundle\Security\HttpBasicPreAuthenticatedListener;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class HttpBasicPreAuthenticatedListenerTest extends TestCase
{
    private $listener;
    private $method;

    protected function setUp()
    {
        $this->listener = new HttpBasicPreAuthenticatedListener(
            $this->createMock(TokenStorageInterface::class),
            $this->createMock(AuthenticationManagerInterface::class),
            'ldap.provider'
        );

        $this->method = new ReflectionMethod($this->listener, 'getPreAuthenticatedData');
        $this->method->setAccessible(true);
    }

    /**
     * @dataProvider provideTestGetPreAuthenticatedData
     */
    public function testGetPreAuthenticatedData($serverParams, $expectedData)
    {
        $request = new Request([], [], [], [], [], $serverParams);

        $this->assertEquals($expectedData, $this->method->invoke($this->listener, $request));
    }

    public function provideTestGetPreAuthenticatedData()
    {
        return [
            [
                ['PHP_AUTH_USER' => 'username', 'PHP_AUTH_PW' => 'password'],
                ['username', 'password'],
            ],
            [
                ['PHP_AUTH_USER' => 'username'],
                ['username', ''],
            ],
        ];
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\BadCredentialsException
     */
    public function testGetPreAuthenticatedDataBadCredentials()
    {
        $this->method->invoke($this->listener, new Request());
    }
}
