<?php

require_once(ROOT_PATH . 'config/startup.php');

$ROUTER = new Router;
$ROUTER->load($_SERVER['REQUEST_URI'])->set_urls($urls)->parse()->handle();

?>