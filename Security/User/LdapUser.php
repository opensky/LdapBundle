<?php

namespace OpenSky\Bundle\LdapBundle\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * LdapUser is the user implementation used by the LDAP user provider.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class LdapUser implements UserInterface
{
    protected $username;
    protected $roles;

    /**
     * Constructor.
     *
     * @param string $username
     * @param array  $roles
     */
    public function __construct($username, array $roles = [])
    {
        if (empty($username)) {
            throw new \InvalidArgumentException('The username cannot be empty.');
        }

        $this->username = $username;
        $this->roles = $roles;
    }

    /**
     * @see Symfony\Component\Security\Core\User\UserInterface::__toString()
     */
    public function __toString()
    {
        return $this->username;
    }

    /**
     * @see Symfony\Component\Security\Core\User\UserInterface::getRoles()
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @see Symfony\Component\Security\Core\User\UserInterface::getPassword()
     */
    public function getPassword()
    {
        return null;
    }

    /**
     * @see Symfony\Component\Security\Core\User\UserInterface::getSalt()
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * @see Symfony\Component\Security\Core\User\UserInterface::getUsername()
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @see Symfony\Component\Security\Core\User\UserInterface::eraseCredentials()
     * @codeCoverageIgnore
     */
    public function eraseCredentials()
    {
    }

    /**
     * @see Symfony\Component\Security\Core\User\UserInterface::equals()
     */
    public function equals(UserInterface $account)
    {
        if (!$account instanceof self) {
            return false;
        }

        if ($this->username !== $account->getUsername()) {
            return false;
        }

        return true;
    }
}
