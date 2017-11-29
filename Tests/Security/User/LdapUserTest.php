<?php

namespace OpenSky\Bundle\LdapBundle\Tests\Security\User;

use OpenSky\Bundle\LdapBundle\Security\User\LdapUser;
use PHPUnit\Framework\TestCase;

class LdapUserTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorException()
    {
        new LdapUser(null);
    }

    public function testMagicToString()
    {
        $user = new LdapUser('jmikola');
        $this->assertEquals('jmikola', (string) $user);
    }

    public function testGetRoles()
    {
        $user = new LdapUser('jmikola');
        $this->assertEquals([], $user->getRoles());

        $user = new LdapUser('jmikola', ['ROLE_ADMIN']);
        $this->assertEquals(['ROLE_ADMIN'], $user->getRoles());
    }

    public function testGetPassword()
    {
        $user = new LdapUser('jmikola');
        $this->assertNull($user->getPassword());
    }

    public function testGetUsername()
    {
        $user = new LdapUser('jmikola');
        $this->assertEquals('jmikola', $user->getUsername());
    }

    public function testGetSalt()
    {
        $user = new LdapUser('jmikola');
        $this->assertNull($user->getSalt());
    }

    public function testEquals()
    {
        $user = new LdapUser('jmikola');

        $this->assertTrue($user->equals(new LdapUser('jmikola')));
        $this->assertFalse($user->equals(new LdapUser('foobar')));
        $this->assertFalse($user->equals($this->createMock('Symfony\Component\Security\Core\User\UserInterface')));
    }
}
