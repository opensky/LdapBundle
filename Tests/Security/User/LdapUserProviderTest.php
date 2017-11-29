<?php

namespace OpenSky\Bundle\LdapBundle\Tests\Security\User;

use OpenSky\Bundle\LdapBundle\Security\User\LdapUser;
use OpenSky\Bundle\LdapBundle\Security\User\LdapUserProvider;
use PHPUnit\Framework\TestCase;

class LdapUserProviderTest extends TestCase
{
    private $provider;
    private $ldapUserManager;
    private $rolePrefix;
    private $defaultRoles;

    protected function setUp()
    {
        $this->ldapUserManager = $this->createMock('OpenSky\Bundle\LdapBundle\Security\User\LdapUserManagerInterface');
        $this->rolePrefix = 'ROLE_LDAP_';
        $this->defaultRoles = ['ROLE_LDAP'];

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
        return [
            [
                [],
                ['ROLE_LDAP'],
            ],
            [
                ['admin', 'moderator'],
                ['ROLE_LDAP', 'ROLE_LDAP_ADMIN', 'ROLE_LDAP_MODERATOR'],
            ],
            [
                ['The "Special" Group'],
                ['ROLE_LDAP', 'ROLE_LDAP_THE_SPECIAL_GROUP'],
            ],
        ];
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
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
            ->will($this->returnValue([]));

        $user = $this->provider->refreshUser($existingUser);

        $this->assertTrue($user->equals($existingUser));
        $this->assertEquals($username, $user->getUsername());
        $this->assertEquals(['ROLE_LDAP'], $user->getRoles());
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\UnsupportedUserException
     */
    public function testRefreshUserNotSupported()
    {
        $this->provider->refreshUser($this->createMock('Symfony\Component\Security\Core\User\UserInterface'));
    }
}
