<?php

namespace OpenSky\Bundle\LdapBundle\Tests\DependencyInjection;

use OpenSky\Bundle\LdapBundle\DependencyInjection\OpenSkyLdapExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class LdapExtensionExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideTestLoad
     */
    public function testLoad($config)
    {
        $container = new ContainerBuilder();
        $extension = new OpenSkyLdapExtension();

        $extension->load(array($config), $container);

        $this->assertTrue($container->hasDefinition('opensky.ldap.user_provider'));

        foreach (array_keys($config) as $key) {
            $this->assertEquals($config[$key], $container->getParameter(sprintf('opensky.ldap.%s', $key)));
        }
    }

    public function provideTestLoad()
    {
        return array(
            array(array()),
            array(array(
                'client_options'     => array('host' => 'example.com'),
                'userDnTemplate'     => 'uid=%s,ou=Users,dc=example,dc=com',
                'roleFilterTemplate' => '(memberuid=%s)',
                'roleBaseDn'         => 'ou=Groups,dc=example,dc=com',
                'roleAttribute'      => 'cn',
                'rolePrefix'         => 'ROLE_',
                'defaultRoles'       => array('ROLE_ADMIN', 'ROLE_LDAP'),
            )),
        );
    }
}
