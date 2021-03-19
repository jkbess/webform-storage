<?php

if (($_SERVER['REQUEST_METHOD'] !== 'POST')) {
    exit();
}

require (__DIR__ . '/../Webform.php');

$config = include (__DIR__ . './config.php'); 

$form_interface = new Webform($config);

echo json_encode($form_interface->handleRequest());

?>