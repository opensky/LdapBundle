<?php

namespace OpenSky\Bundle\LdapBundle\Tests\DependencyInjection;

use OpenSky\Bundle\LdapBundle\DependencyInjection\OpenSkyLdapExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class LdapExtensionExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadMinimalConfiguration()
    {
        $config = array(
            'userBaseDn' => 'ou=Users,dc=example,dc=com',
            'roleBaseDn' => 'ou=Groups,dc=example,dc=com',
        );

        $container = new ContainerBuilder();
        $extension = new OpenSkyLdapExtension();

        $extension->load(array($config), $container);

        $this->assertTrue($container->hasDefinition('opensky.ldap.user_manager'));
        $this->assertTrue($container->hasDefinition('opensky.ldap.user_provider'));

        foreach(array('userBaseDn', 'roleBaseDn') as $key) {
            $this->assertEquals($config[$key], $container->getParameter('opensky.ldap.user_manager.' . $key));
        }
    }

    public function testLoadFullConfiguration()
    {
        $config = array(
            'userBaseDn'        => 'ou=Users,dc=example,dc=com',
            'userFilter'        => '(objectClass=employee)',
            'usernameAttribute' => 'uid',
            'roleBaseDn'        => 'ou=Groups,dc=example,dc=com',
            'roleFilter'        => '(objectClass=role)',
            'roleNameAttribute' => 'cn',
            'roleUserAttribute' => 'memberuid',
            'client' => array(
                'host' => 'example.com',
            ),
            'security' => array(
                'rolePrefix'   => 'ROLE_',
                'defaultRoles' => array('ROLE_ADMIN', 'ROLE_LDAP'),
            )
        );

        $container = new ContainerBuilder();
        $extension = new OpenSkyLdapExtension();

        $extension->load(array($config), $container);

        $this->assertEquals($config['client'], $container->getParameter('opensky.ldap.client.options'));

        foreach(array('userBaseDn', 'userFilter', 'usernameAttribute', 'roleBaseDn', 'roleFilter', 'roleNameAttribute', 'roleUserAttribute') as $key) {
            $this->assertEquals($config[$key], $container->getParameter('opensky.ldap.user_manager.' . $key));
        }

        foreach(array('rolePrefix', 'defaultRoles') as $key) {
            $this->assertEquals($config['security'][$key], $container->getParameter('opensky.ldap.user_provider.' . $key));
        }
    }
}
