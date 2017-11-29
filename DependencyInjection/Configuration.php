<?php

namespace OpenSky\Bundle\LdapBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @see Symfony\Component\Config\Definition\ConfigurationInterface::getConfigTreeBuilder()
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('opensky_ldap');

        $rootNode
            ->children()
                ->scalarNode('user_base_dn')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('user_filter')->defaultValue('(objectClass=*)')->end()
                ->scalarNode('username_attribute')->defaultValue('uid')->end()
                ->scalarNode('role_base_dn')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('role_filter')->defaultValue('(objectClass=*)')->end()
                ->scalarNode('role_name_attribute')->defaultValue('cn')->end()
                ->scalarNode('role_user_attribute')->defaultValue('memberuid')->end()
            ->end()
        ;

        $this->addClientSection($rootNode);
        $this->addSecuritySection($rootNode);

        return $treeBuilder;
    }

    private function addClientSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                // TODO: Add Zend\Ldap configuration structure
                ->variableNode('client')
                    ->defaultValue([])
                    ->beforeNormalization()
                        ->ifTrue(function ($v) { return !is_array($v); })
                        ->thenEmptyArray()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addSecuritySection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('security')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('role_prefix')->defaultValue('ROLE_LDAP_')->end()
                        ->arrayNode('default_roles')
                            ->performNoDeepMerging()
                            ->beforeNormalization()->ifString()->then(function ($v) { return ['value' => $v]; })->end()
                            ->beforeNormalization()
                                ->ifTrue(function ($v) { return is_array($v) && isset($v['value']); })
                                ->then(function ($v) { return preg_split('/\s*,\s*/', $v['value']); })
                            ->end()
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
