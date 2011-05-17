<?php

namespace OpenSky\Bundle\LdapBundle\Security\User;

interface LdapUserManagerInterface
{
    function hasUsername($username);
    function getUsernames();
    function getRoles();
    function getRolesForUsername($username);
    function setRolesForUsername($username, array $roles);
}
