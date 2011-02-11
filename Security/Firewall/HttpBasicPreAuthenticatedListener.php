<?php

namespace OpenSky\Bundle\LdapBundle\Security\Firewall;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Firewall\AbstractPreAuthenticatedListener;

/**
 * HttpBasicPreAuthenticationListener implements a a pre-authenticated listener
 * that infers the user from basic HTTP authentication.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class HttpBasicPreAuthenticatedListener extends AbstractPreAuthenticatedListener
{
    protected function getPreAuthenticatedData(Request $request)
    {
        if (!$request->server->has('PHP_AUTH_USER')) {
            throw new BadCredentialsException('HTTP-authenticated user was not found');
        }

        return array($request->server->get('PHP_AUTH_USER'), $request->server->get('PHP_AUTH_PW', ''));
    }
}
