<?php

namespace OpenSky\Bundle\LdapBundle\Tests\Security\User;

use OpenSky\Bundle\LdapBundle\Security\User\LdapUser;

class LdapUserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
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
        $this->assertEquals(array(), $user->getRoles());

        $user = new LdapUser('jmikola', array('ROLE_ADMIN'));
        $this->assertEquals(array('ROLE_ADMIN'), $user->getRoles());
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
        $this->assertFalse($user->equals($this->getMock('Symfony\Component\Security\Core\User\AccountInterface')));
    }
}
