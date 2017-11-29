<?php

namespace OpenSky\Bundle\LdapBundle\DependencyInjection\Security;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

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
            '%opensky_ldap.authentication.listener.basic_pre_auth.class%',
            [
                new Reference('security.context'),
                new Reference('security.authentication.manager'),
                $id,
                new Reference('logger', ContainerBuilder::IGNORE_ON_INVALID_REFERENCE),
            ]
        );
        $listener->addTag('monolog.logger', ['channel' => 'security']);

        $listenerId = 'opensky_ldap.authentication.listener.basic_pre_auth.'.$id;
        $container->setDefinition($listenerId, $listener);

        return [$provider, $listenerId, $defaultEntryPoint];
    }

    /**
     * @see Symfony\Bundle\FrameworkBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface::getPosition()
     * @codeCoverageIgnore
     */
    public function getPosition()
    {
        return 'pre_auth';
    }

    /**
     * @see Symfony\Bundle\FrameworkBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface::getKey()
     * @codeCoverageIgnore
     */
    public function getKey()
    {
        return 'http-basic-pre-auth';
    }

    /**
     * @see Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface::addConfiguration()
     * @codeCoverageIgnore
     */
    public function addConfiguration(NodeDefinition $builder)
    {
        $builder->children()->scalarNode('provider')->end()->end();
    }
}
