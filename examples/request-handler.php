<?php

if (($_SERVER['REQUEST_METHOD'] !== 'POST')) {
    exit();
}

require (__DIR__ . '/../Webform.php');

$config = include (__DIR__ . './config.php'); 

$form_interface = new Webform($config);

if ($_POST['request_type'] === 'get_entries') {
    // test for user access
    /* if false return ['success' => false, 'message' => 'You do not have permission to use this feature.'];*/
}

echo json_encode($form_interface->handleRequest());

?>