<?php

define('ROOT_PATH', '/webroot/iick/');
define('SITE_DOMAIN', 'iick.com');

function load($class, $args)
{
    require(ROOT_PATH . 'models/' . $class . '.php');
    
    $obj = new $class;
    $obj->load($args);
    
    return $obj;
}

function loadMany($class, $args)
{
    require(ROOT_PATH . 'models/' . $class . '.php');
    
    $obj = new $class;

    return $obj->loadMany($args);
}

function add($class, $input)
{
    require(ROOT_PATH . 'models/' . $class . '.php');
    
    $obj = new $class;

    return $obj->add($input);
}

function __autoload($class)
{
    if (file_exists(ROOT_PATH . 'models/' . $class . '.php'))
    {
        require_once(ROOT_PATH . 'models/' . $class . '.php');
    }
    
}

?>