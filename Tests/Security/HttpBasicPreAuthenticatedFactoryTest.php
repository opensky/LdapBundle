<?php

namespace OpenSky\Bundle\LdapBundle\Tests\Security;

use OpenSky\Bundle\LdapBundle\Security\HttpBasicPreAuthenticatedFactory;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class HttpBasicPreAuthenticatedFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $factory = new HttpBasicPreAuthenticatedFactory();
        $container = new ContainerBuilder();
        $userProvider = $this->getMock('Symfony\Component\Security\Core\User\UserProviderInterface');
        $defaultEntryPoint = $this->getMock('Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface');

        // Load "security.authentication.listener.basic_pre_auth.class" parameter from ldap.xml
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../Resources/config'));
        $loader->load('ldap.xml');

        list($provider, $listenerId, $returnedDefaultEntryPoint) = $factory->create($container, rand(), array(), $userProvider, $defaultEntryPoint);

        $this->assertTrue($container->hasDefinition($provider));
        $this->assertTrue($container->hasDefinition($listenerId));
        $this->assertSame($defaultEntryPoint, $returnedDefaultEntryPoint);
    }
}
