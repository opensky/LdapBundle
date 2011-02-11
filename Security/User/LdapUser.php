<?php

namespace OpenSky\Bundle\LdapBundle\Security\User;

use Symfony\Component\Security\Core\User\AccountInterface;

/**
 * LdapUser is the user implementation used by the LDAP user provider.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class LdapUser implements AccountInterface
{
    protected $username;
    protected $roles;

    /**
     * Constructor.
     *
     * @param string $username
     * @param array  $roles
     */
    public function __construct($username, array $roles = array())
    {
        if (empty($username)) {
            throw new \InvalidArgumentException('The username cannot be empty.');
        }

        $this->username = $username;
        $this->roles = $roles;
    }

    /**
     * @see Symfony\Component\Security\Core\User\AccountInterface::__toString()
     */
    public function __toString()
    {
        return $this->username;
    }

    /**
     * @see Symfony\Component\Security\Core\User\AccountInterface::getRoles()
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @see Symfony\Component\Security\Core\User\AccountInterface::getPassword()
     */
    public function getPassword()
    {
        return null;
    }

    /**
     * @see Symfony\Component\Security\Core\User\AccountInterface::getSalt()
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * @see Symfony\Component\Security\Core\User\AccountInterface::getUsername()
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @see Symfony\Component\Security\Core\User\AccountInterface::eraseCredentials()
     * @codeCoverageIgnore
     */
    public function eraseCredentials()
    {
    }

    /**
     * @see Symfony\Component\Security\Core\User\AccountInterface::equals()
     */
    public function equals(AccountInterface $account)
    {
        if (!$account instanceof LdapUser) {
            return false;
        }

        if ($this->username !== $account->getUsername()) {
            return false;
        }

        return true;
    }
}
