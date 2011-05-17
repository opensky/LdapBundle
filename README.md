# LdapBundle

This bundle implements an LDAP-based UserProvider for Symfony2's Security component.

When used in conjunction with Symfony2's HTTP basic authentication listener, this
bundle can verify usernames against an LDAP server and populate roles based on
groups to which the LDAP user belongs.

## Installation

### Dependencies

This bundle depends on the [Zend Framework 2](https://github.com/zendframework/zf2)
LDAP client.

If you don't already have the ZF2 codebase available in the vendor path of your
Symfony2 application, you may be interested in using [git-subtree](https://github.com/apenwarr/git-subtree)
to pull in the LDAP client by itself.  Instructions for this process are documented
in [this thread](https://groups.google.com/d/msg/symfony-devs/N-uIGhEWgs8/CrFmiLFYQbEJ)
from the symfony-devs mailing list.

### Submodule Creation

Add LdapBundle to your `src/` directory:

    $ git submodule add https://github.com/opensky/LdapBundle.git vendor/bundles/OpenSky/Bundle/LdapBundle

### Class Autoloading

If the `src/` directory is already configured in your project's `autoload.php`
via `registerNamespaceFallback()`, no changes should be necessary.  Otherwise,
either define the fallback directory or explicitly add the "OpenSky" namespace:

    # app/autoload.php

    $loader->registerNamespaces(array(
        'OpenSky' => __DIR__'/../vendor/bundles',
    ));

Additionally, ensure that the "Zend" namespace is also configured for autoloading.

### Application Kernel

Add LdapBundle to the `registerBundles()` method of your application kernel:

    public function registerBundles()
    {
        return array(
            new OpenSky\Bundle\LdapBundle\OpenSkyLdapBundle(),
        );
    }

## Configuration

### LdapBundle Extension

The LDAP UserProvider may be configured with the following:

    # app/config/config.yml

    opensky_ldap:
        client:
            host: ldap.example.com
        user_base_dn:        ou=Users,dc=example,dc=com
        user_filter:         (objectClass=employee)
        username_attribute:  uid
        role_base_dn:        ou=Groups,dc=example,dc=com
        role_filter:         (objectClass=role)
        role_name_attribute: cn
        role_user_attribute: memberuid
        security:
            role_prefix:   ROLE_LDAP_
            default_roles: [ROLE_ADMIN, ROLE_LDAP]

These settings are explained below:

 * `client`: array of options for the ZF2 LDAP client. Any options may be
   specified, although host is likely a minimum requirement.
 * `user_base_dn`: base DN when searching for users is LDAP.
 * `user_filter`: filter to apply when searching for users in LDAP.
 * `username_attribute`: user entry attribute to use as a username.
 * `role_base_dn`: base DN when searching for roles in LDAP.
 * `role_filter`: filter to apply when searching for roles in LDAP.
 * `role_name_attribute`: role entry attribute to use as the role name.
 * `role_user_attribute`: role entry attribute to use for inferring user
    relationships. Its value should be a set of user identifiers, which
    correspond to `usernameAttribute` values of user entries.
 * `security.role_prefix`: prefix to apply when transforming role names from LDAP
   entries into security roles. See: *Deriving Symfony2 Roles from LDAP Groups*
 * `security.default_roles`: array of default roles to be assigned to all LDAP
   users, before roles are inferred from user/role entry relationships.

See also:

 * [Zend_Ldap config documentation](http://framework.zend.com/manual/en/zend.ldap.api.html)

### Security Component

This bundle is currently intended to be used alongside Apache's mod_auth_ldap.
As such, it must be configured to operate with a PreAuthenticatedAuthenticationProvider.
A pre-auth provider for HTTP basic authentication is included, and may be
configured as follows:

    # app/config/security.yml

    security.config:
        providers:
            ldap:
                id: opensky.ldap.user_provider
        firewalls:
            backend:
                provider:            ldap
                pattern:             /admin(/.*)?
                http_basic_pre_auth: true
                stateless:           true
        factories:
            - %kernel.root_dir%/../vendor/bundles/OpenSky/Bundle/LdapBundle/Resources/config/security_factories.xml

Note: a future enhancement for this bundle will be a UserAuthenticationProvider
to allow for authentication against an LDAP server, which will remove the need
to use mod_auth_ldap for pre-authentication.

See also:

 * [mod_auth_ldap documentation](http://httpd.apache.org/docs/2.0/mod/mod_auth_ldap.html)

## The LdapUser Object ##

Users provided by the LDAP UserProvider will be instances of LdapUser, which is
a lightweight implementation of Symfony2's UserInterface.  This user object
stores only a username and array of roles.

## Deriving Symfony2 Roles from LDAP Groups

LdapBundle will attempt to create Symfony2 security roles based on an attribute
from the group entry.  By default, the group's common name ("cn") will be used.

In general, a group's name will be slugified (using an underscore), uppercased
and prefixed with a configurable string ("ROLE_LDAP_" by default).  For example,
if your user exists within the LDAP group named "Admin", the provided LdapUser
object will have the "ROLE_LDAP_ADMIN" role. The full implementation can be
found within the LdapUserProvider class.
