<?php

namespace OpenSky\Bundle\LdapBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\FileLocator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * LdapExtension.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class LdapExtension extends Extension
{
    public function ldapLoad(array $configs, ContainerBuilder $container)
    {
        // TODO: Remove this after configuration merging is implemented
        foreach ($configs as $config) {
            $this->doLdapLoad($config, $container);
        }
    }

    /**
     * Loads the ldap configuration.
     *
     * @param array            $config    An array of configuration settings
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function doLdapLoad($config, ContainerBuilder $container)
    {
        if (!$container->hasDefinition('os_security.authentication.factory.basic_pre_auth')) {
            $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
            $loader->load('ldap.xml');
        }

        foreach (array('client_options', 'userDnTemplate', 'roleFilterTemplate', 'roleBaseDn', 'roleAttribute', 'rolePrefix', 'defaultRoles') as $key) {
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