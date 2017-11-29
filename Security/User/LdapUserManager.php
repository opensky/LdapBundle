<?php

namespace OpenSky\Bundle\LdapBundle\Security\User;

use Zend\Ldap\Ldap;

/**
 * LdapUserManager manages LDAP users and roles.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class LdapUserManager implements LdapUserManagerInterface
{
    private $ldap;
    private $userBaseDn;
    private $userFilter;
    private $usernameAttribute;
    private $roleBaseDn;
    private $roleFilter;
    private $roleNameAttribute;
    private $roleUserAttribute;

    /**
     * Constructor.
     *
     * @param Ldap   $ldap              LDAP client instance
     * @param string $userBaseDn        Base DN for user records
     * @param string $userFilter        Filter for user queries
     * @param string $usernameAttribute User entry attribute from which to derive username
     * @param string $roleBaseDn        Base DN for role records
     * @param string $roleFilter        Filter for role queries
     * @param string $roleNameAttribute Role entry attribute from which to derive name
     * @param string $roleUserAttribute Role entry attribute from which to derive user memberships
     */
    public function __construct(Ldap $ldap, $userBaseDn, $userFilter, $usernameAttribute, $roleBaseDn, $roleFilter, $roleNameAttribute, $roleUserAttribute)
    {
        $this->ldap = $ldap;
        $this->userBaseDn = $userBaseDn;
        $this->userFilter = $userFilter;
        $this->usernameAttribute = $usernameAttribute;
        $this->roleBaseDn = $roleBaseDn;
        $this->roleFilter = $roleFilter;
        $this->roleNameAttribute = $roleNameAttribute;
        $this->roleUserAttribute = $roleUserAttribute;
    }

    /**
     * Check if the username exists.
     *
     * @param string $username
     *
     * @return bool
     */
    public function hasUsername($username)
    {
        $dn = sprintf('%s=%s,%s', $this->usernameAttribute, $username, $this->userBaseDn);

        return (bool) $this->ldap->count($this->userFilter, $dn, Ldap::SEARCH_SCOPE_BASE);
    }

    /**
     * Get a list of usernames.
     *
     * @return array
     */
    public function getUsernames()
    {
        return $this->resolveEntriesToAttributes($this->ldap->searchEntries(
            $this->userFilter,
            $this->userBaseDn,
            Ldap::SEARCH_SCOPE_SUB,
            [$this->usernameAttribute]
        ), $this->usernameAttribute);
    }

    /**
     * Get a list of roles.
     *
     * @return array
     */
    public function getRoles()
    {
        return $this->resolveEntriesToAttributes($this->ldap->searchEntries(
            $this->roleFilter,
            $this->roleBaseDn,
            Ldap::SEARCH_SCOPE_SUB,
            [$this->roleNameAttribute]
        ), $this->roleNameAttribute);
    }

    /**
     * Get a list of roles for the username.
     *
     * @param string $username
     *
     * @return array
     */
    public function getRolesForUsername($username)
    {
        return $this->resolveEntriesToAttributes($this->ldap->searchEntries(
            sprintf('(&%s(%s=%s))', $this->roleFilter, $this->roleUserAttribute, $username),
            $this->roleBaseDn,
            Ldap::SEARCH_SCOPE_SUB,
            [$this->roleNameAttribute]
        ), $this->roleNameAttribute);
    }

    /**
     * Set roles for a username.
     *
     * @param string $username
     * @param array  $roles
     */
    public function setRolesForUsername($username, array $roles)
    {
        $existingRoles = $this->getRolesForUsername($username);

        $addRoles = array_diff($roles, $existingRoles);
        $removeRoles = array_diff($existingRoles, $roles);

        $updateEntriesByDn = [];

        foreach ($addRoles as $name) {
            $dn = sprintf('%s=%s,%s', $this->roleNameAttribute, $name, $this->roleBaseDn);

            if ($entry = $this->ldap->getEntry($dn)) {
                if (!isset($entry[$this->roleUserAttribute]) || !in_array($username, $entry[$this->roleUserAttribute])) {
                    $entry[$this->roleUserAttribute][] = $username;
                    $updateEntriesByDn[$dn] = $entry;
                }
            }
        }

        foreach ($removeRoles as $name) {
            $dn = sprintf('%s=%s,%s', $this->roleNameAttribute, $name, $this->roleBaseDn);

            if ($entry = $this->ldap->getEntry($dn)) {
                if (isset($entry[$this->roleUserAttribute]) && ($key = array_search($username, $entry[$this->roleUserAttribute]))) {
                    unset($entry[$this->roleUserAttribute][$key]);
                    $updateEntriesByDn[$dn] = $entry;
                }
            }
        }

        foreach ($updateEntriesByDn as $dn => $entry) {
            $this->ldap->update($dn, $entry);
        }
    }

    /**
     * Resolves entries resulting from a search query to an array of attribute
     * values.
     *
     * @param array  $entries
     * @param string $attributeName
     *
     * @return array
     */
    private function resolveEntriesToAttributes(array $entries, $attributeName)
    {
        $attributes = [];

        foreach ($entries as $entry) {
            if (isset($entry[$attributeName][0])) {
                $attributes[] = $entry[$attributeName][0];
            }
        }

        return $attributes;
    }
}
