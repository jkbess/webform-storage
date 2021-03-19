<?php

namespace Webform\Fetcher;

class FieldFetcher implements FieldFetcherInterface
{
    private $db_connector;
    private $include_default;
    
    public function __construct($db_connector, $params)
    {
        $this->db_connector = $db_connector;
        $this->include_default = $this->includeDefaultColumns($params);
    }

    private function includeDefaultColumns ($params)
    {
       if (!$params || !is_array($params)) {
            return false;
        }
        if (!isset($params['include_default'])) {
            return false;
        }
        return !!$params['include_default'];
    }

    public function getFormFields()
    {
        $pragma_sql = 'PRAGMA table_info (entries)';
        $query_data = $this->db_connector->doQuery($pragma_sql);
        if (!$query_data) {
            return [
                'success' => false,
                'message' => 'Sorry, there was an error getting the form fields.'
            ];
        }

        $table_data = [];
        foreach ($query_data as $row) {
            if (
                $this->include_default 
                || !in_array($row['name'], DISALLOWED_COLUMN_NAMES)
            ) {
                array_push($table_data, [
                    'columnName' => $row['name'],
                    'isNumeric' => $row['type'] === 'INTEGER' ? true : false
                ]);
            }
        }

        return [
            'success' => true,
            'message' => 'Form fields successfully retrieved.',
            'data' => $table_data
        ];
    }
}

?>