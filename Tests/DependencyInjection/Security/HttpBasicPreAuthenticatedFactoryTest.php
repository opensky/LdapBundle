<?php

namespace OpenSky\Bundle\LdapBundle\Tests\DependencyInjection\Security;

use OpenSky\Bundle\LdapBundle\DependencyInjection\Security\HttpBasicPreAuthenticatedFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class HttpBasicPreAuthenticatedFactoryTest extends TestCase
{
    public function testCreate()
    {
        $factory = new HttpBasicPreAuthenticatedFactory();
        $container = new ContainerBuilder();
        $userProvider = 'my.user.provider';
        $defaultEntryPoint = $this->createMock(AuthenticationEntryPointInterface::class);

        // Load "security.authentication.listener.basic_pre_auth.class" parameter from ldap.xml
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../../Resources/config'));
        $loader->load('ldap.xml');

        list($provider, $listenerId, $returnedDefaultEntryPoint) = $factory->create($container, rand(), [], $userProvider, $defaultEntryPoint);

        $this->assertTrue($container->hasDefinition($provider));
        $this->assertTrue($container->hasDefinition($listenerId));
        $this->assertEquals($defaultEntryPoint, $returnedDefaultEntryPoint);
    }
}
