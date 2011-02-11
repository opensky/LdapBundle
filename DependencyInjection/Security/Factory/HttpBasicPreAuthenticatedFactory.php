<?php

namespace OpenSky\LdapBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Configuration\Builder\NodeBuilder;

/**
 * HttpBasicPreAuthenticatedFactory creates services for HTTP basic
 * authentication, which assume the user has been pre-authenticated.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class HttpBasicPreAuthenticatedFactory implements SecurityFactoryInterface
{
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $provider = 'security.authentication.provider.pre_authenticated.'.$id;
        $container
            ->register($provider, '%security.authentication.provider.pre_authenticated.class%')
            ->setArguments(array(new Reference($userProvider), new Reference('security.account_checker')))
            ->setPublic(false)
        ;

        $listenerId = 'security.authentication.listener.basic_pre_auth.'.$id;
        $listener = $container->setDefinition($listenerId, clone $container->getDefinition('security.authentication.listener.basic_pre_auth'));
        $arguments = $listener->getArguments();
        $arguments[1] = new Reference($provider);
        $arguments[2] = $listenerId;
        $listener->setArguments($arguments);

        return array($provider, $listenerId, $defaultEntryPoint);
    }

    /**
     * @see Symfony\Bundle\FrameworkBundle\DependencyInjection\Security\Factory.SecurityFactoryInterface::getPosition()
     * @codeCoverageIgnore
     */
    public function getPosition()
    {
        return 'pre_auth';
    }

    /**
     * @see Symfony\Bundle\FrameworkBundle\DependencyInjection\Security\Factory.SecurityFactoryInterface::getKey()
     * @codeCoverageIgnore
     */
    public function getKey()
    {
        return 'http-basic-pre-auth';
    }

    /**
     * @see Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory.SecurityFactoryInterface::addConfiguration()
     * @codeCoverageIgnore
     */
    public function addConfiguration(NodeBuilder $builder)
    {
        $builder->scalarNode('provider')->end();
    }
}
