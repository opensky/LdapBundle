<?php

namespace OpenSky\Bundle\LdapBundle\Security\User;

interface LdapUserManagerInterface
{
    public function hasUsername($username);

    public function getUsernames();

    public function getRoles();

    public function getRolesForUsername($username);

    public function setRolesForUsername($username, array $roles);
}
