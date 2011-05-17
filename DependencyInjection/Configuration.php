<?php

namespace OpenSky\Bundle\LdapBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

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
                ->scalarNode('userBaseDn')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('userFilter')->defaultValue('(objectClass=*)')->end()
                ->scalarNode('usernameAttribute')->defaultValue('uid')->end()
                ->scalarNode('roleBaseDn')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('roleFilter')->defaultValue('(objectClass=*)')->end()
                ->scalarNode('roleNameAttribute')->defaultValue('cn')->end()
                ->scalarNode('roleUserAttribute')->defaultValue('memberuid')->end()
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
                    ->defaultValue(array())
                    ->beforeNormalization()
                        ->ifTrue(function($v){ return !is_array($v); })
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
                        ->scalarNode('rolePrefix')->defaultValue('ROLE_LDAP_')->end()
                        ->arrayNode('defaultRoles')
                            ->performNoDeepMerging()
                            ->beforeNormalization()->ifString()->then(function($v) { return array('value' => $v); })->end()
                            ->beforeNormalization()
                                ->ifTrue(function($v) { return is_array($v) && isset($v['value']); })
                                ->then(function($v) { return preg_split('/\s*,\s*/', $v['value']); })
                            ->end()
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
