<?php

namespace Bundle\OpenSky\LdapBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * LdapExtension.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class LdapExtension extends Extension
{
    /**
     * Loads the ldap configuration.
     *
     * @param array            $config    An array of configuration settings
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function ldapLoad($config, ContainerBuilder $container)
    {
        if (!$container->hasDefinition('os_security.provider.ldap')) {
            $loader = new XmlFileLoader($container, __DIR__.'/../Resources/config');
            $loader->load('ldap.xml');
        }

        foreach (array('client_options', 'userDnTemplate', 'roleFilterTemplate', 'roleBaseDn', 'roleAttribute') as $key) {
            if (array_key_exists($key, $config)) {
                $container->setParameter(sprintf('os_security.ldap.%s', $key), $config[$key]);
            }
        }
    }

    /**
     * @see Symfony\Component\DependencyInjection\Extension.ExtensionInterface::getXsdValidationBasePath()
     * @codeCoverageIgnore
     */
    public function getXsdValidationBasePath()
    {
        return __DIR__.'/../Resources/config';
    }

    /**
     * @see Symfony\Component\DependencyInjection\Extension.ExtensionInterface::getNamespace()
     * @codeCoverageIgnore
     */
    public function getNamespace()
    {
        return 'http://shopopensky.com/schema/dic/security';
    }

    /**
     * @see Symfony\Component\DependencyInjection\Extension.ExtensionInterface::getAlias()
     * @codeCoverageIgnore
     */
    public function getAlias()
    {
        return 'os_security';
    }
}