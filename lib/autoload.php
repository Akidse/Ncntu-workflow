<?php
include_once "Autoloader.php";

spl_autoload_register(function($class)
{
    $autoloader = new Autoloader($class);
    if($autoloader->isExistsClass())
    {
        include_once $autoloader->getPath();
    }
});
