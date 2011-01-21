<?php

namespace OpenSky\LdapBundle\Security\Firewall;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Security\Firewall\PreAuthenticatedListener;
use Symfony\Component\Security\Exception\BadCredentialsException;

/**
 * HttpBasicPreAuthenticationListener implements a a pre-authenticated listener
 * that infers the user from basic HTTP authentication.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class HttpBasicPreAuthenticatedListener extends PreAuthenticatedListener
{
    protected function getPreAuthenticatedData(Request $request)
    {
        if (!$request->server->has('PHP_AUTH_USER')) {
            throw new BadCredentialsException('HTTP-authenticated user was not found');
        }

        return array($request->server->get('PHP_AUTH_USER'), $request->server->get('PHP_AUTH_PW', ''));
    }
}
