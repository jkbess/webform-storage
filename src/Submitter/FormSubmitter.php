<?php

namespace Webform\Submitter;

use Webform\Submitter\AttachmentHandler;

class FormSubmitter implements FormSubmitterInterface
{
    private $db_connector;
    private $config;
    private $fields;
    
    public function __construct($db_connector, $config)
    {   
        $this->db_connector = $db_connector;
        $this->config = $config;
        $this->fields = $this->getFields();
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
        error_log($sql);
        return $sql;
    }

    private function getColumnAliases ()
    {
        $fields = $this->config['fields_to_save'] ?? [];
        $aliases = [];
        foreach ($fields as $field => $settings) {
            if (!empty($field['alias'])) {
                $aliases[$field['alias']] = $field;
            }
        }
        return $aliases;
    }

    private function getFields ()
    {        
        $fields = $this->config['fields_to_save'] ?? [];
        $attachment_column = $this->config['attachments']['column_name'];
        if ($attachment_column) {
            $fields[$attachment_column] = [
                'type' => 'TEXT',
                'required' => false
            ];
        }
        return $fields;
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
            $data_type = $data['type'];
            if (!isset($form[$field])) {
                $alias = $data['alias'] ?? null;
                if (isset($form[$alias])) {
                    $value = $form[$alias];
                } else {
                    array_push($missing, $field);
                    continue;
                }
            } else {
                $value = $form[$field];
            }
            if (is_array($value)) {
                $value = implode(', ', $value);
            } else if ($value === "on") {
                $value = $data_type === 'TEXT' ? 'true' : 1;
            }
            switch ($data_type) {
                case 'TEXT':
                    $val = trim(stripslashes($value));
                    $val = preg_replace('/\r|\n/i', '', $value); // to prevent injection attacks
                    break;
                case 'INTEGER':
                    if ($val === 'true'){
                        $val = 1;
                    } else if ($value === 'false') {
                        $val = 0;
                    } else {
                        $val = intval($value);
                    }
                    break;
                case 'REAL':
                    $val = floatval($value);
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

        if (!!count($_FILES)) {
            $attachment_settings = $config['attachments'] ?? null;
            if ($attachment_settings && $attachment_settings['column_name']) {
                $file_handler = new AttachmentHandler($attachment_settings);
                $attachment_urls = $file_handler->getAttachmentUrls();
                if ($attachment_urls) {
                    $attach_col = $attachment_settings['column_name'];
                    $entries[$attach_col] = $attachment_urls;
                }
            }
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