<?php

namespace OpenSky\Bundle\LdapBundle\Tests\Security\User;

use OpenSky\Bundle\LdapBundle\Security\User\LdapUserManager;
use PHPUnit\Framework\TestCase;
use Zend\Ldap\Ldap;

class LdapUserManagerTest extends TestCase
{
    const ROLE_NAME_ATTRIBUTE = 'cn';
    const USERNAME_ATTRIBUTE = 'uid';

    private $manager;
    private $ldap;
    private $userDnTemplate;
    private $userFilter;
    private $userBaseDn;
    private $userAttribute;
    private $roleFilterTemplate;
    private $roleBaseDn;
    private $roleAttribute;
    private $rolePrefix;
    private $defaultRoles;

    protected function setUp()
    {
        $this->ldap = $this->createMock(Ldap::class);

        $this->userBaseDn = 'ou=Users,dc=example,dc=com';
        $this->userFilter = '(objectClass=employee)';
        $this->usernameAttribute = self::USERNAME_ATTRIBUTE;
        $this->roleBaseDn = 'ou=Groups,dc=example,dc=com';
        $this->roleFilter = '(objectClass=*)';
        $this->roleNameAttribute = self::ROLE_NAME_ATTRIBUTE;
        $this->roleUserAttribute = 'memberuid';

        $this->manager = new LdapUserManager(
            $this->ldap,
            $this->userBaseDn,
            $this->userFilter,
            $this->usernameAttribute,
            $this->roleBaseDn,
            $this->roleFilter,
            $this->roleNameAttribute,
            $this->roleUserAttribute
        );
    }

    /**
     * @dataProvider provideTestHasUsername
     */
    public function testHasUsername($expectedCount, $expectedResult)
    {
        $username = 'jmikola';
        $expectedDn = sprintf('%s=%s,%s', $this->usernameAttribute, $username, $this->userBaseDn);

        $this->ldap->expects($this->once())
            ->method('count')
            ->with($this->userFilter, $expectedDn, Ldap::SEARCH_SCOPE_BASE)
            ->will($this->returnValue($expectedCount));

        $this->assertEquals($expectedResult, $this->manager->hasUsername($username));
    }

    public function provideTestHasUsername()
    {
        return [
            [0, false],
            [1, true],
        ];
    }

    /**
     * @dataProvider provideAttributes
     */
    public function testGetUsernames($usernames)
    {
        $this->ldap->expects($this->once())
            ->method('searchEntries')
            ->with($this->userFilter, $this->userBaseDn, Ldap::SEARCH_SCOPE_SUB, [$this->usernameAttribute])
            ->will($this->returnValue(call_user_func_array([$this, 'createUserEntries'], $usernames)));

        $this->assertEquals($usernames, $this->manager->getUsernames());
    }

    /**
     * @dataProvider provideAttributes
     */
    public function testGetRoles($roles)
    {
        $this->ldap->expects($this->once())
            ->method('searchEntries')
            ->with($this->roleFilter, $this->roleBaseDn, Ldap::SEARCH_SCOPE_SUB, [$this->roleNameAttribute])
            ->will($this->returnValue(call_user_func_array([$this, 'createRoleEntries'], $roles)));

        $this->assertEquals($roles, $this->manager->getRoles());
    }

    /**
     * @dataProvider provideAttributes
     */
    public function testGetRolesForUsername($roles)
    {
        $username = 'jmikola';
        $expectedFilter = sprintf('(&%s(%s=%s))', $this->roleFilter, $this->roleUserAttribute, $username);

        $this->ldap->expects($this->once())
            ->method('searchEntries')
            ->with($expectedFilter, $this->roleBaseDn, Ldap::SEARCH_SCOPE_SUB, [$this->roleNameAttribute])
            ->will($this->returnValue(call_user_func_array([$this, 'createRoleEntries'], $roles)));

        $this->assertEquals($roles, $this->manager->getRolesForUsername($username));
    }

    public function provideAttributes()
    {
        return [
            [[]],
            [['alpha']],
            [['alpha', 'beta', 'gamma']],
        ];
    }

    private function createRoleEntries()
    {
        $entries = [];

        foreach (func_get_args() as $value) {
            $entries[] = [self::ROLE_NAME_ATTRIBUTE => [$value]];
        }

        return $entries;
    }

    private function createUserEntries()
    {
        $entries = [];

        foreach (func_get_args() as $value) {
            $entries[] = [self::USERNAME_ATTRIBUTE => [$value]];
        }

        return $entries;
    }
}
