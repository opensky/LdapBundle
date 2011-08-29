<?php

namespace OpenSky\Bundle\LdapBundle\Tests\DependencyInjection;

use OpenSky\Bundle\LdapBundle\DependencyInjection\OpenSkyLdapExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class LdapExtensionExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadMinimalConfiguration()
    {
        $config = array(
            'user_base_dn' => 'ou=Users,dc=example,dc=com',
            'role_base_dn' => 'ou=Groups,dc=example,dc=com',
        );

        $container = new ContainerBuilder();
        $extension = new OpenSkyLdapExtension();

        $extension->load(array($config), $container);

        $this->assertTrue($container->hasDefinition('opensky_ldap.user_manager'));
        $this->assertTrue($container->hasDefinition('opensky_ldap.user_provider'));

        foreach(array('user_base_dn', 'role_base_dn') as $key) {
            $this->assertEquals($config[$key], $container->getParameter('opensky_ldap.user_manager.' . $key));
        }
    }

    public function testLoadFullConfiguration()
    {
        $config = array(
            'user_base_dn'        => 'ou=Users,dc=example,dc=com',
            'user_filter'         => '(objectClass=employee)',
            'username_attribute'  => 'uid',
            'role_base_dn'        => 'ou=Groups,dc=example,dc=com',
            'role_filter'         => '(objectClass=role)',
            'role_name_attribute' => 'cn',
            'role_user_attribute' => 'memberuid',
            'client' => array(
                'host' => 'example.com',
            ),
            'security' => array(
                'role_prefix'   => 'ROLE_',
                'default_roles' => array('ROLE_ADMIN', 'ROLE_LDAP'),
            )
        );

        $container = new ContainerBuilder();
        $extension = new OpenSkyLdapExtension();

        $extension->load(array($config), $container);

        $this->assertEquals($config['client'], $container->getParameter('opensky_ldap.client.options'));

        foreach(array('user_base_dn', 'user_filter', 'username_attribute', 'role_base_dn', 'role_filter', 'role_name_attribute', 'role_user_attribute') as $key) {
            $this->assertEquals($config[$key], $container->getParameter('opensky_ldap.user_manager.' . $key));
        }

        foreach(array('role_prefix', 'default_roles') as $key) {
            $this->assertEquals($config['security'][$key], $container->getParameter('opensky_ldap.user_provider.' . $key));
        }
    }
}
