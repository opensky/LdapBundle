<?php

namespace OpenSky\Bundle\LdapBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * LdapExtension.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class OpenSkyLdapExtension extends Extension
{
    /**
     * @see Symfony\Component\DependencyInjection\Extension\ExtensionInterface::load()
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('ldap.xml');

        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), $configs);

        $container->setParameter('opensky.ldap.client.options', $config['client']);

        foreach(array('user_base_dn', 'user_filter', 'username_attribute', 'role_base_dn', 'role_filter', 'role_name_attribute', 'role_user_attribute') as $key) {
            $container->setParameter('opensky.ldap.user_manager.'.$key, $config[$key]);
        }

        foreach(array('role_prefix', 'default_roles') as $key) {
            $container->setParameter('opensky.ldap.user_provider.'.$key, $config['security'][$key]);
        }
    }

    /**
     * @see Symfony\Component\DependencyInjection\Extension\ExtensionInterface::getAlias()
     * @codeCoverageIgnore
     */
    public function getAlias()
    {
        return 'opensky_ldap';
    }
}
