<?php

namespace OpenSky\Bundle\LdapBundle\Security\User;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Zend\Ldap\Ldap;

/**
 * LdapUserProvider is an LDAP-based user provider.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class LdapUserProvider implements UserProviderInterface
{
    private $ldap;
    private $userDnTemplate;
    private $roleFilterTemplate;
    private $roleBaseDn;
    private $roleAttribute;
    private $rolePrefix;
    private $defaultRoles;

    /**
     * Constructor.
     *
     * @param Ldap   $ldap               LDAP client instance
     * @param string $userDnTemplate     DN template for user LDAP::exits() query
     * @param string $roleFilterTemplate Filter template for role LDAP::search() query
     * @param string $roleBaseDn         Base DN for role LDAP::search() query
     * @param string $roleAttribute      Entry attribute from which to derive role name
     * @param string $rolePrefix         Prefix for transforming group names to roles
     * @param array  $defaultRoles       Default roles given to all users
     */
    public function __construct(Ldap $ldap, $userDnTemplate, $roleFilterTemplate, $roleBaseDn, $roleAttribute, $rolePrefix = 'ROLE_', array $defaultRoles = array())
    {
        $this->ldap               = $ldap;
        $this->userDnTemplate     = $userDnTemplate;
        $this->roleFilterTemplate = $roleFilterTemplate;
        $this->roleBaseDn         = $roleBaseDn;
        $this->roleAttribute      = $roleAttribute;
        $this->rolePrefix         = $rolePrefix;
        $this->defaultRoles       = $defaultRoles;
    }

    /**
     * @see Symfony\Component\Security\Core\User\UserProviderInterface::loadUserByUsername()
     */
    public function loadUserByUsername($username)
    {
        $this->ldap->bind();

        if (!$this->ldap->exists(sprintf($this->userDnTemplate, $username))) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username));
        }

        $roles = $this->getRolesForUsername($username);

        return new LdapUser($username, $roles);
    }

    /**
     * @see Symfony\Component\Security\Core\User\UserProviderInterface::loadUser()
     */
    public function loadUser(UserInterface $account)
    {
        if (!$account instanceof LdapUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($account)));
        }

        return $this->loadUserByUsername((string) $account);
    }

    /**
     * Gets roles for the username.
     *
     * @param string $username
     * @return array
     */
    private function getRolesForUsername($username)
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
                if ($role = $this->createRoleFromAttribute($entry[$this->roleAttribute][0])) {
                    $roles[] = $role;
                }
            }
        }

        return array_unique(array_merge($this->defaultRoles, $roles));
    }

    /**
     * Creates a role name from an LDAP entry attribute.
     *
     * If a name cannot be derived from the attribute, null will be returned.
     *
     * @param string $attribute
     * @return string|null
     */
    private function createRoleFromAttribute($attribute)
    {
        // Replace sequences of non-alphanumeric characters with an underscore
        $role = preg_replace('/[^\\pL\d]+/u', '_', $attribute);

        // Attempt transliteration of non-ASCII characters
        if (function_exists('iconv')) {
            $role = iconv('utf-8', 'us-ascii//TRANSLIT', $role);
        }

        // Strip any remaining non-word characters
        $role = preg_replace('/[^\w]+/', '', $role);

        // Trim surrounding underscores and convert to uppercase
        $role = strtoupper(trim($role, '_'));

        return $role === '' ? null : $this->rolePrefix . $role;
    }

    /**
     * @see Symfony\Component\Security\Core\User\UserProviderInterface::supportsClass()
     * @codeCoverageIgnore
     */
    public function supportsClass($class)
    {
        return $class === 'OpenSky\Bundle\LdapBundle\Security\User\LdapUser';
    }

}
