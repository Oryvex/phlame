<?php 
session_start();
require_once '.config/_init.php'; 

$router = new Router();

$router->addRoute('/', function() {
  Source::set("index");
});

$router->route();
?>
  