<?php

if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] == '67.164.186.56')
{
    ini_set('display_errors', 'on');
    error_reporting(E_ALL);
}

// die('hi');

require_once('/webroot/iick/config/startup.php');
require_once(ROOT_PATH . 'classes/Router.php');

Timer::start('TOTAL');

$routes = array(
    '^$'                    => array('Index->index'),
    '^announce/?$'          => array('Torrent->announce'),
    '^details/(?P<id>\w+)$'  => array('Torrent->details'),
    '^list/?$'              => array('Torrent->_list'),
    '^login/?$'              => array('User->login'),
    '^signup/?$'             => array('User->signup'),
    '^profile/?$'           => array('User->profile'),
);

$ROUTER = new Router;
$ROUTER->load($_SERVER['REQUEST_URI'])->set_routes($routes)->route();

?>