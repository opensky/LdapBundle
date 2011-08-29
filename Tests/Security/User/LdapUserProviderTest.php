<?php

namespace OpenSky\Bundle\LdapBundle\Tests\Security\User;

use OpenSky\Bundle\LdapBundle\Security\User\LdapUser;
use OpenSky\Bundle\LdapBundle\Security\User\LdapUserProvider;

class LdapUserProviderTest extends \PHPUnit_Framework_TestCase
{
    private $provider;
    private $ldapUserManager;
    private $rolePrefix;
    private $defaultRoles;

    protected function setUp()
    {
        $this->ldapUserManager = $this->getMock('OpenSky\Bundle\LdapBundle\Security\User\LdapUserManagerInterface');
        $this->rolePrefix      = 'ROLE_LDAP_';
        $this->defaultRoles    = array('ROLE_LDAP');

        $this->provider = new LdapUserProvider(
            $this->ldapUserManager,
            $this->rolePrefix,
            $this->defaultRoles
        );
    }

    /**
     * @dataProvider provideTestLoadByUsername
     */
    public function testLoadByUsername(array $roleNames, array $expectedRoles)
    {
        $username = 'jmikola';

        $this->ldapUserManager->expects($this->once())
            ->method('hasUsername')
            ->with($username)
            ->will($this->returnValue(true));

        $this->ldapUserManager->expects($this->once())
            ->method('getRolesForUsername')
            ->with($username)
            ->will($this->returnValue($roleNames));

        $user = $this->provider->loadUserByUsername($username);

        $this->assertEquals($username, $user->getUsername());
        $this->assertEquals($expectedRoles, $user->getRoles());
    }

    public function provideTestLoadByUsername()
    {
        return array(
            array(
                array(),
                array('ROLE_LDAP'),
            ),
            array(
                array('admin', 'moderator'),
                array('ROLE_LDAP', 'ROLE_LDAP_ADMIN', 'ROLE_LDAP_MODERATOR'),
            ),
            array(
                array('The "Special" Group'),
                array('ROLE_LDAP', 'ROLE_LDAP_THE_SPECIAL_GROUP'),
            ),
        );
    }

    /**
     * @expectedException Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     */
    public function testLoadUserByUsernameNotFound()
    {
        $username = 'jmikola';

        $this->ldapUserManager->expects($this->once())
            ->method('hasUsername')
            ->with($username)
            ->will($this->returnValue(false));

        $this->provider->loadUserByUsername($username);
    }

    public function testRefreshUser()
    {
        $username = 'jmikola';
        $existingUser = new LdapUser($username);

        $this->ldapUserManager->expects($this->once())
            ->method('hasUsername')
            ->with($username)
            ->will($this->returnValue(true));

        $this->ldapUserManager->expects($this->once())
            ->method('getRolesForUsername')
            ->with($username)
            ->will($this->returnValue(array()));

        $user = $this->provider->refreshUser($existingUser);

        $this->assertTrue($user->equals($existingUser));
        $this->assertEquals($username, $user->getUsername());
        $this->assertEquals(array('ROLE_LDAP'), $user->getRoles());
    }

    /**
     * @expectedException Symfony\Component\Security\Core\Exception\UnsupportedUserException
     */
    public function testRefreshUserNotSupported()
    {
        $this->provider->refreshUser($this->getMock('Symfony\Component\Security\Core\User\UserInterface'));
    }
}
