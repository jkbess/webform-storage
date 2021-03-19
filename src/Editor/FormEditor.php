<?php

namespace Webform\Editor;

use Webform\Fetcher\FieldFetcher;

class FormEditor implements FormEditorInterface
{
    private $db_connector;
    private $column_name;
    private $is_numeric;

    public function __construct ($db_connector, $post_data)
    {        
        $this->db_connector = $db_connector;
        $this->column_name = $post_data['column_name'] ?? null;        
        $this->is_numeric = $post_data['is_numeric'] ?? null;
    }
    
    public function addField()
    {
        $result = ['success' => false ];
        
        if (!$this->column_name) {
            $result['message'] = 'Could not add column: no name was provided.';
            return $result;
        }
        $new_column = preg_replace('/[^\w\s_]/', '', $this->column_name);
        $new_column = preg_replace('/\s+/', '_', $new_column);

        if (in_array($new_column, DISALLOWED_COLUMN_NAMES)) {
            $result['message'] = 'Sorry, that cannot be used as a field name.';
            return $result;
        }
        
        // check if column name exists already
        $field_fetcher = new FieldFetcher(['include_default' => true]);
        $table_data = $field_fetcher->getFormFields(['include_default' => true]);
        if (!$table_data['success']) {
            error_log('Webform - error in FormEditor: could not verify column names.');
            $result['message'] = 'Sorry, there was an database error.';
            return $result;
        }
        foreach($table_data['data'] as $table_column) {
            if ($table_column['columnName'] === $new_column) {
                $result['message'] = 'Sorry, there is already a field named ' . $new_column . '.';
                return $result;
            }
        }

        $alter_sql = 'ALTER TABLE entries ADD COLUMN ' . $new_column;
        if ($this->is_numeric === 'true') {
            $alter_sql .= ' INTEGER';
        } else {
            $alter_sql .= ' TEXT';
        }
        $alter_result = $this->db_connector->doQuery($alter_sql);
        if (!$alter_result) {
            $result['message'] = 'New field could not be added: ' . $this->db_connector->lastErrorMsg();
            return $result;
        }
        $result['success'] = true;
        $result['message'] = $new_column . ' added.';
        return $result;
    }
}

?>