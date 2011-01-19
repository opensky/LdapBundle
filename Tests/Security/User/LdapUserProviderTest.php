<?php

namespace Bundle\OpenSky\LdapBundle\Tests\Security\User;

use Bundle\OpenSky\LdapBundle\Security\User\LdapUser;
use Bundle\OpenSky\LdapBundle\Security\User\LdapUserProvider;
use Zend\Ldap\Ldap;

class LdapUserProviderTest extends \PHPUnit_Framework_TestCase
{
    const ROLE_ATTRIBUTE = 'cn';

    private $provider;
    private $ldap;
    private $userDnTemplate;
    private $roleFilterTemplate;
    private $roleBaseDn;
    private $roleAttribute;

    protected function setUp()
    {
        $this->ldap               = $this->getMockLdap();
        $this->userDnTemplate     = 'uid=%s,ou=Users,dc=example,dc=com';
        $this->roleFilterTemplate = '(memberuid=%s)';
        $this->roleBaseDn         = 'ou=Groups,dc=example,dc=com';
        $this->roleAttribute      = self::ROLE_ATTRIBUTE;

        $this->provider = new LdapUserProvider(
            $this->ldap,
            $this->userDnTemplate,
            $this->roleFilterTemplate,
            $this->roleBaseDn,
            $this->roleAttribute
        );
    }

    /**
     * @dataProvider provideTestLoadByUsername
     */
    public function testLoadByUsername($entries, $expectedRoles)
    {
        $username = 'jmikola';

        $this->ldap->expects($this->once())
            ->method('exists')
            ->with(sprintf($this->userDnTemplate, $username))
            ->will($this->returnValue(true));

        $this->ldap->expects($this->once())
            ->method('searchEntries')
            ->with(
                sprintf($this->roleFilterTemplate, $username),
                $this->roleBaseDn,
                Ldap::SEARCH_SCOPE_SUB,
                array($this->roleAttribute)
            )
            ->will($this->returnValue($entries));

        $user = $this->provider->loadUserByUsername($username);

        $this->assertEquals($username, $user->getUsername());
        $this->assertEquals($expectedRoles, $user->getRoles());
    }

    public function provideTestLoadByUsername()
    {
        return array(
            array(
                $this->createRoleEntries(),
                array(),
            ),
            array(
                $this->createRoleEntries('admin', 'moderator'),
                array('ROLE_ADMIN', 'ROLE_MODERATOR'),
            ),
            array(
                $this->createRoleEntries('The "Special" Group'),
                array('ROLE_THE_SPECIAL_GROUP'),
            ),
        );
    }

    /**
     * @expectedException Symfony\Component\Security\Exception\UsernameNotFoundException
     */
    public function testLoadUserByUsernameNotFound()
    {
        $this->ldap->expects($this->once())
            ->method('exists')
            ->will($this->returnValue(false));

        $this->provider->loadUserByUsername('jmikola');
    }

    public function testLoadUserByAccount()
    {
        $username = 'jmikola';
        $account = new LdapUser($username);

        $this->ldap->expects($this->once())
            ->method('exists')
            ->with(sprintf($this->userDnTemplate, $username))
            ->will($this->returnValue(true));

        $this->ldap->expects($this->once())
            ->method('searchEntries')
            ->will($this->returnValue(array()));

        $user = $this->provider->loadUserByAccount($account);

        $this->assertTrue($user->equals($account));
        $this->assertEquals($username, $user->getUsername());
        $this->assertEquals(array(), $user->getRoles());
    }

    /**
     * @expectedException Symfony\Component\Security\Exception\UnsupportedAccountException
     */
    public function testLoadUserByAccountNotSupported()
    {
        $this->provider->loadUserByAccount($this->getMock('Symfony\Component\Security\User\AccountInterface'));
    }

    private function createRoleEntries()
    {
        $entries = array();

        foreach (func_get_args() as $value) {
            $entries[] = array(self::ROLE_ATTRIBUTE => array($value));
        }

        return $entries;
    }

    private function getMockLdap()
    {
        return $this->getMockBuilder('Zend\Ldap\Ldap')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
