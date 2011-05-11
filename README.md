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

    open_sky_ldap:
        client_options:
            host: ldap.example.com
        userDnTemplate:     uid=%s,ou=Users,dc=example,dc=com
        roleFilterTemplate: (memberuid=%s)
        roleBaseDn:         ou=Groups,dc=example,dc=com
        roleAttribute:      cn
        rolePrefix:         ROLE_LDAP_
        defaultRoles:       [ROLE_LDAP]

These settings are explained below:

 * `client_options` corresponds to an array that will be passed to the ZF2 Ldap
    constructor.  A host is likely the minimum requirement, but a base DN should
    not be necessary, as the user/role queries each specify their own DN.
 * `userDnTemplate` is an `sprintf()` template string used to check the existence
   of a user entry in LDAP.  This template should contain "%s", which will be
   replaced with the username.
 * `roleFilterTemplate` is also an `sprintf()` template, but is used when searching
   LDAP groups containing a given user.  "%s" will also be replaced with the username.
 * `roleBaseDn` is the base DN when searching LDAP groups.
 * `roleAttribute` should be a single attribute name from the group entry.  This
   attribute will be used to derive a role identifier for the security component.
 * `rolePrefix` is a prefix to apply when transforming LDAP group names into roles.
   This is discussed in *Deriving Symfony2 Roles from LDAP Groups*.
 * `defaultRoles` is an array of default roles to be assigned to all LDAP users,
   before roles are assigned based on group memberships.

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
