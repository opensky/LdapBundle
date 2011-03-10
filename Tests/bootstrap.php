<?php

require_once $_SERVER['SYMFONY_SRC'].'/Symfony/Component/ClassLoader/UniversalClassLoader.php';

$loader = new Symfony\Component\ClassLoader\UniversalClassLoader();
$loader->registerNamespace('Symfony', $_SERVER['SYMFONY_SRC']);
$loader->registerNamespace('Zend', $_SERVER['ZEND_LIB']);
$loader->register();

spl_autoload_register(function($class)
{
    if (0 === strpos($class, 'OpenSky\\Bundle\\LdapBundle\\')) {
        $path = implode('/', array_slice(explode('\\', $class), 3)).'.php';
        require_once __DIR__.'/../'.$path;
        return true;
    }
});
