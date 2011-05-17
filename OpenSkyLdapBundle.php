<?php

namespace OpenSky\Bundle\LdapBundle;

use OpenSky\Bundle\LdapBundle\DependencyInjection\OpenSkyLdapExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OpenSkyLdapBundle extends Bundle
{
    public function __construct()
    {
        $this->extension = new OpenSkyLdapExtension();
    }
}
