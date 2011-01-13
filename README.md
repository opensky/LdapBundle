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

### Application Kernel

Add SimpleCASBundle to the `registerBundles()` method of your application kernel:

    public function registerBundles()
    {
        return array(
            new Bundle\OpenSky\LdapBundle\OpenSkyLdapBundle(),
        );
    }

### Class Autoloading

This step should already be done if your Symfony2 application is using ZF2, but
make sure the Zend namespace appears in your project's `autoload.php` file:

    $loader->registerNamespaces(array(
        'Zend' => __DIR__.'/vendor/zend/library',
    ));

## Configuration

### LdapBundle Extension

The LDAP UserProvider may be configured with the following:

    # app/config/config_dev.yml

    os_security.ldap:
        client_options:
            host: ldap.example.com
        userDnTemplate:     uid=%s,ou=Users,dc=example,dc=com
        roleFilterTemplate: (memberuid=%s)
        roleBaseDn:         ou=Groups,dc=example,dc=com
        roleAttribute:      cn

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

See also:

 * [Zend_Ldap config documentation](http://framework.zend.com/manual/en/zend.ldap.api.html)

### Security Component

Additionally, the LDAP UserProvider should be added as a provider for a firewall:

    # app/config/security.yml

    security.config:
        providers:
            ldap:
                id: os_security.user.provider.ldap
        firewalls:
            backend:
                provider:   ldap
                pattern:    /admin/.*
                http_basic: true
                stateless:  true

In the above example, stateless, HTTP basic authentication is configured for the
backend portion of an application.  While you certainly *could*, you probably
wouldn't want to use the LDAP UserProvider on your frontend.

## The LdapUser Object ##

Users provided by the LDAP UserProvider will be instances of LdapUser, which is
a lightweight implementation of Symfony2's AccountInterface.  This user object
stores only a username and array of roles.

## Deriving Symfony2 Roles from LDAP Groups

LdapBundle will attempt to create Symfony2 security roles based on an attribute
from the group entry.  By default, the group's common name ("cn") will be used.

In general, a group's name will be slugified (using an underscore), uppercased
and prefixed with "ROLE_".  For example, if your user exists within the LDAP
group named "Admin", the provided LdapUser object will have the "ROLE_ADMIN" role.
The full implementation can be found within the LdapUserProvider class.
