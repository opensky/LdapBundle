<?php

namespace OpenSky\Bundle\LdapBundle\Security\User;

use Zend\Ldap\Ldap;

class LdapUserManager
{
    private $ldap;
    private $roleFilterTemplate;
    private $roleBaseDn;
    private $roleAttribute;

    /**
     * Constructor.
     *
     * @param Ldap   $ldap               LDAP client instance
     * @param string $userFilter         Filter for username list LDAP::search() query
     * @param string $userBaseDn         Base DN for username list LDAP::search() query
     * @param string $userAttribute      Entry attribute from which to derive username
     * @param string $roleFilterTemplate Filter template for role LDAP::search() query
     * @param string $roleBaseDn         Base DN for role LDAP::search() query
     * @param string $roleAttribute      Entry attribute from which to derive role name
     */
    public function __construct(Ldap $ldap, $userFilter, $userBaseDn, $userAttribute, $roleFilterTemplate, $roleBaseDn, $roleAttribute)
    {
        $this->ldap               = $ldap;
        $this->userFilter         = $userFilter;
        $this->userBaseDn         = $userBaseDn;
        $this->userAttribute      = $userAttribute;
        $this->roleFilterTemplate = $roleFilterTemplate;
        $this->roleBaseDn         = $roleBaseDn;
        $this->roleAttribute      = $roleAttribute;
    }

    public function getUsernames()
    {
        $usernames = array();

        $entries = $this->ldap->searchEntries(
            $this->userFilter,
            $this->userBaseDn,
            Ldap::SEARCH_SCOPE_SUB,
            array($this->userAttribute)
        );

        foreach ($entries as $entry) {
            if (isset($entry[$this->userAttribute][0])) {
                $usernames[] = $entry[$this->userAttribute][0];
            }
        }

        return $usernames;
    }

    public function getRoles()
    {
        $roles = array();

        $entries = $this->ldap->searchEntries(
            '(cn=*)',
            $this->roleBaseDn,
            Ldap::SEARCH_SCOPE_SUB,
            array($this->roleAttribute)
        );

        foreach ($entries as $entry) {
            if (isset($entry[$this->roleAttribute][0])) {
                $roles[] = $entry[$this->roleAttribute][0];
            }
        }

        return array_unique($roles);
    }

    /**
     * Gets roles for the username.
     *
     * @param string $username
     * @return array
     */
    public function getRolesForUsername($username)
    {
        $roles = array();

        $entries = $this->ldap->searchEntries(
            sprintf($this->roleFilterTemplate, $username),
            $this->roleBaseDn,
            Ldap::SEARCH_SCOPE_SUB,
            array($this->roleAttribute)
        );

        foreach ($entries as $entry) {
            if (isset($entry[$this->roleAttribute][0])) {
                $roles[] = $entry[$this->roleAttribute][0];
            }
        }

        return $roles;
    }

    public function setRolesForUsername($username, array $roles)
    {
        $currentRoles = $this->getRolesForUsername($username);
        $allRoles = array_unique(array_merge($currentRoles, $roles));

        $entriesByName = array();
        foreach ($allRoles as $role) {
            $entriesByName[$role] = $this->ldap->getEntry(sprintf('cn=%s,ou=Groups,dc=theopenskyproject,dc=com', $role));
        }

        foreach ($currentRoles as $role) {
            if (!in_array($role, $roles)) {
                $key = array_search($username, $entriesByName[$role]['memberuid']);
                unset($entriesByName[$role]['memberuid'][$key]);
            }
        }

        foreach ($roles as $role) {
            if (!isset($entriesByName[$role]['memberuid']) || !in_array($username, $entriesByName[$role]['memberuid'])) {
                $entriesByName[$role]['memberuid'][] = $username;
            }
        }
        
        foreach ($entriesByName as $role => $entry) {
            $dn = sprintf('cn=%s,ou=Groups,dc=theopenskyproject,dc=com', $role);
            $this->ldap->update($dn, $entry);
        }
    }
}