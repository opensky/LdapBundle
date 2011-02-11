<?php

namespace OpenSky\Bundle\LdapBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * OpenSkyLdapBundle.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 * @codeCoverageIgnore
 */
class OpenSkyLdapBundle extends Bundle
{
    /**
     * @see Symfony\Component\HttpKernel\Bundle.BundleInterface::getNamespace()
     */
    public function getNamespace()
    {
        return __NAMESPACE__;
    }

    /**
     * @see Symfony\Component\HttpKernel\Bundle.BundleInterface::getPath()
     */
    public function getPath()
    {
        return strtr(__DIR__, '\\', '/');
    }
}
