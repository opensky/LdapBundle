<?php

namespace Bundle\OpenSky\LdapBundle\Tests\DependencyInjection;

use Bundle\OpenSky\LdapBundle\DependencyInjection\LdapExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class LdapExtensionExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideTestLdapLoad
     */
    public function testLdapLoad($config)
    {
        $container = new ContainerBuilder();
        $extension = new LdapExtension();

        $extension->ldapLoad($config, $container);

        $this->assertTrue($container->hasDefinition('os_security.authentication.factory.basic_pre_auth'));
        $this->assertTrue($container->hasDefinition('os_security.authentication.listener.basic_pre_auth'));
        $this->assertTrue($container->hasDefinition('os_security.user.provider.ldap'));

        foreach (array_keys($config) as $key) {
            $this->assertEquals($config[$key], $container->getParameter(sprintf('os_security.ldap.%s', $key)));
        }
    }

    public function provideTestLdapLoad()
    {
        return array(
            array(array()),
            array(array(
                'client_options'     => array('host' => 'example.com'),
                'userDnTemplate'     => 'uid=%s,ou=Users,dc=example,dc=com',
                'roleFilterTemplate' => '(memberuid=%s)',
                'roleBaseDn'         => 'ou=Groups,dc=example,dc=com',
                'roleAttribute'      => 'cn',
            )),
        );
    }
}
