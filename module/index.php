<?php
$title = "Головна";

$sessionAlerts = new SessionAlerts();

if($authorizeSession->isAuthorized())$router->redirect($router->url("", "panel"));