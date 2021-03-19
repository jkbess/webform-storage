<?php

namespace Webform\Verifier;

class UserVerifier implements UserVerifierInterface
{
    public function hasFullAccess()
    {
        $app_folder = substr(getcwd(), strlen($_SERVER['DOCUMENT_ROOT']));
        $app_folder = ltrim($app_folder, "\\");
        $app_folder = str_replace("\\", '/', $app_folder);
        if (!isset($_SESSION['user_name'])) {
            error_log('No user name');
            return false;
        }
        
        error_log($_SESSION['user_name']);
        if ($_SESSION['user_name'] === 'admin') {
            return true;
        }
        if (!isset($_SESSION['editor_access'])) {
            return false;
        }
        $folders = explode(',', $_SESSION['editor_access']);
        $folders = array_map('trim', $folders);
        return in_array($app_folder, $folders);
    }
}

?>