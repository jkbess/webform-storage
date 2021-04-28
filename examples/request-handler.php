<?php

if (($_SERVER['REQUEST_METHOD'] !== 'POST')) {
    exit();
}

require (__DIR__ . '/../Webform.php');

$config = include (__DIR__ . './config-example.php'); 

$form_interface = new Webform($config);

if ($_POST['request_type'] === 'get_entries') {
    /*  Test for user access if you wish to keep submitted information secure.
        If user not permitted to view results, return array like:
        ['success' => false, 'message' => 'You do not have permission to use this feature.'];
    */
}

echo json_encode($form_interface->handleRequest());

?>