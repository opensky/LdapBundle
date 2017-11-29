<?php

namespace OpenSky\Bundle\LdapBundle\Security\User;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * LdapUserProvider is an LDAP-based user provider.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class LdapUserProvider implements UserProviderInterface
{
    private $ldapUserManager;
    private $rolePrefix;
    private $defaultRoles;

    /**
     * Constructor.
     *
     * @param LdapUserManagerInterface $ldapUserManager LDAP user manager instance
     * @param string                   $rolePrefix      Prefix for transforming group names to roles
     * @param array                    $defaultRoles    Default roles given to all users
     */
    public function __construct(LdapUserManagerInterface $ldapUserManager, $rolePrefix = 'ROLE_', array $defaultRoles = [])
    {
        $this->ldapUserManager = $ldapUserManager;
        $this->rolePrefix = $rolePrefix;
        $this->defaultRoles = $defaultRoles;
    }

    /**
     * @see Symfony\Component\Security\Core\User\UserProviderInterface::loadUserByUsername()
     */
    public function loadUserByUsername($username)
    {
        if (!$this->ldapUserManager->hasUsername($username)) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username));
        }

        return new LdapUser($username, $this->getRolesForUsername($username));
    }

    /**
     * @see Symfony\Component\Security\Core\User\UserProviderInterface::refreshUser()
     */
    public function refreshUser(UserInterface $account)
    {
        if (!$account instanceof LdapUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($account)));
        }

        return $this->loadUserByUsername($account->getUsername());
    }

    /**
     * Gets roles for the username.
     *
     * @param string $username
     *
     * @return array
     */
    private function getRolesForUsername($username)
    {
        $roles = [];

        foreach ($this->ldapUserManager->getRolesForUsername($username) as $roleName) {
            if ($role = $this->createRoleFromAttribute($roleName)) {
                $roles[] = $role;
            }
        }

        return array_unique(array_merge($this->defaultRoles, $roles));
    }

    /**
     * Creates a role name from an LDAP attribute.
     *
     * If a name cannot be derived from the attribute, null will be returned.
     *
     * @param string $attribute
     *
     * @return string
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

        return '' === $role ? null : $this->rolePrefix.$role;
    }

    /**
     * @see Symfony\Component\Security\Core\User\UserProviderInterface::supportsClass()
     * @codeCoverageIgnore
     */
    public function supportsClass($class)
    {
        return 'OpenSky\Bundle\LdapBundle\Security\User\LdapUser' === $class;
    }
}
