<?php

namespace Webform\Submitter;

use Webform\Submitter\AttachmentHandler;

class FormSubmitter implements FormSubmitterInterface
{
    private $db_connector;
    private $config;
    private $attachment_config;
    private $fields;
    
    public function __construct($db_connector, $config)
    {   
        $this->db_connector = $db_connector;
        $this->config = $config;        
        $this->attachment_config = $config['attachments'] ?? [];
        $this->fields = $config['fields_to_save'] ?? [];
    }

    private function buildSQL ($entries)
    {
        $column_list = "";
        $reference_list = "";
        foreach($entries as $entry => $value) {
            $column_list .= $entry . ",";
            $reference_list .= ":" . $entry . ",";
        }
        $column_list = rtrim($column_list, ',');
        $reference_list = rtrim($reference_list, ',');
        $table = $this->config['table_name'];
        $sql = "INSERT INTO $table ({$column_list}) VALUES ({$reference_list})";
        return $sql;
    }

    /* return array of trimmed/corrected values or an error string */
    private function parseForm($form)
	{
        $fields = $this->fields;
        $config = $this->config;
        $entries = [];
        $missing = [];
        foreach ($fields as $field => $data) {
            //check for required, push to $missing array if not present
            $alias = null;
            $value = '';
            // if $field not present, look for alias
            if (!isset($form[$field])) {
                $alias = $data['alias'] ?? null;
                if ($alias && isset($form[$alias])) {
                    $value = $form[$alias];
                }
            } else {
                $value = $form[$field];
            }
            if (!empty($data['required']) && trim($value) === '') {
                array_push($missing, $alias ?? $field);
                continue;
            }
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            /* type coercion */
            $data_type = strtoupper($data['type']);
            switch ($data_type) {
                case 'TEXT':
                    $value = trim(stripslashes($value));
                    $value = preg_replace('/\r|\n/i', '', $value); // to prevent injection attacks
                    break;
                case 'INTEGER':
                    if ($value === 'true'){
                        $value = 1;
                    } else if ($value === 'false') {
                        $value = 0;
                    } else if ($value === '') {
                        $value = null;
                    } else {
                        $value = intval($value);
                    }
                    break;
                case 'REAL':
                    $value = floatval($value);
            }
            $entries[$field] = $value;
        }
        
        if (count($missing) > 0) {
            return 'Required fields missing: ' . implode(', ', $missing);
        }

        if (!empty($config['submission_date_column'])) {
            $date_column = $config['submission_date_column'];
            $now = new \DateTime();
            $entries[$date_column] = $now->format('Y-m-d H:i:s');
        }
        
        $attachment_config = $this->attachment_config;
        if (!!count($_FILES)) {
            if ($attachment_config['save_path'] && $attachment_config['column_name'] ) {
                $file_handler = new AttachmentHandler($attachment_config);
                $attachment_urls = $file_handler->getAttachmentUrls();
                if ($attachment_urls) {
                    $attach_col = $attachment_config['column_name'];
                    $entries[$attach_col] = $attachment_urls;
                }
            }
        } else if ($attachment_config['required'] === true) {
            return 'Required attachments are missing.'; 
        }

        if (count($entries) === 0) {
            return 'Form submitted without any valid fields!';
        }

        return $entries;
	}

    public function storeSubmission($form)
    {
        $result = [
            'success' => false
        ];
        
        $entries = $this->parseForm($form);

        if (gettype($entries) === 'string') { // is error
            $result['message'] = $entries;
            return $result;
        }
        $sql = $this->buildSQL($entries);

        $result = $this->db_connector->doQuery($sql, $entries);
        $message = $result
            ? 'Form successfully saved.'
            : 'Form could not be saved.';
        return [
            'success' => true,
            'message' => $message
        ];
    }
}

?>