<?php

namespace OpenSky\LdapBundle\Tests\DependencyInjection\Security\Factory;

use OpenSky\LdapBundle\DependencyInjection\Security\Factory\HttpBasicPreAuthenticatedFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class HttpBasicPreAuthenticatedFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $factory = new HttpBasicPreAuthenticatedFactory();
        $container = new ContainerBuilder();
        $userProvider = $this->getMock('Symfony\Component\Security\Core\User\UserProviderInterface');
        $defaultEntryPoint = $this->getMock('Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface');

        $container->setDefinition('security.authentication.listener.basic_pre_auth', new Definition());

        list($provider, $listenerId, $returnedDefaultEntryPoint) = $factory->create($container, rand(), array(), $userProvider, $defaultEntryPoint);

        $this->assertTrue($container->hasDefinition($provider));
        $this->assertTrue($container->hasDefinition($listenerId));
        $this->assertSame($defaultEntryPoint, $returnedDefaultEntryPoint);
    }
}
