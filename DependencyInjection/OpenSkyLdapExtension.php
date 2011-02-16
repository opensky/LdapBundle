<?php

namespace OpenSky\Bundle\LdapBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * LdapExtension.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class OpenSkyLdapExtension extends Extension
{
    /**
     * @see Symfony\Component\DependencyInjection\Extension.ExtensionInterface::load()
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('ldap.xml');

        // TODO: Implement configuration merging and refactor this
        foreach ($configs as $config) {
            foreach (array('client_options', 'userDnTemplate', 'roleFilterTemplate', 'roleBaseDn', 'roleAttribute', 'rolePrefix', 'defaultRoles') as $key) {
                if (array_key_exists($key, $config)) {
                    $container->setParameter(sprintf('opensky.ldap.%s', $key), $config[$key]);
                }
            }
        }
    }

    /**
     * @see Symfony\Component\DependencyInjection\Extension.ExtensionInterface::getAlias()
     * @codeCoverageIgnore
     */
    public function getAlias()
    {
        return 'open_sky_ldap';
    }
}