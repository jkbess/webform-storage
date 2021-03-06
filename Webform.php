<?php

require __DIR__ . '/vendor/autoload.php';
// require __DIR__ . '/src/autoload.php';

use Webform\Connector\DatabaseConnector;
use Webform\Fetcher\EntryFetcher;
use Webform\Submitter\FormSubmitter;
use Webform\Editor\FormEditor;

class Webform
{
    private $db_path;
    private $db_connector;
    private $config;
    private $post_data;
    private $request_type;

    public function __construct($config)
    {        
        $this->config = $config;
        $this->db_path = $_SERVER['DOCUMENT_ROOT'] . $config['database_path'];
        $this->db_connector = new DatabaseConnector($this->db_path);
        $this->post_data = $_POST;
        $this->request_type = $_POST['request_type'] ?? null;
    }

    public function handleRequest()
    {
        $db_connector = $this->db_connector;
        $post = $this->post_data;
        $config = $this->config;

        switch ($this->request_type) {
            case 'save_form':
                $submitter = new FormSubmitter($db_connector, $config);
                return $submitter->storeSubmission($post);
                
            case 'get_entries':
                $fetcher = new EntryFetcher($db_connector, $config, $post);
                return $fetcher->getSubmitted();

            default:
                return [
                    'success' => false,
                    'message' => 'This is not a valid request.'
                ];
        }
    }
        
}

?>