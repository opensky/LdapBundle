<?php

namespace OpenSky\Bundle\LdapBundle\Security;

use Symfony\Component\DependencyInjection\DefinitionDecorator;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

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
            ->setDefinition($provider, new DefinitionDecorator('security.authentication.provider.pre_authenticated'))
            ->replaceArgument(0, new Reference($userProvider))
            ->addArgument($id)
            ->addTag('security.authentication_provider')
        ;

        $listener = new Definition(
            new Parameter('security.authentication.listener.basic_pre_auth.class'),
            array(
                new Reference('security.context'),
                new Reference('security.authentication.manager'),
                $id,
                new Reference('logger', ContainerBuilder::IGNORE_ON_INVALID_REFERENCE),
            )
        );

        $listenerId = 'security.authentication.listener.basic_pre_auth.'.$id;
        $container->setDefinition($listenerId, $listener);

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
    public function addConfiguration(NodeDefinition $builder)
    {
        $builder->scalarNode('provider')->end();
    }
}
