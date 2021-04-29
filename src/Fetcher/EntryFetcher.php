<?php

namespace Webform\Fetcher;

class EntryFetcher implements EntryFetcherInterface
{

    private $db_connector;
    private $fields;
    private $filters;
    private $config;
    private $columns_to_fetch;
    private $sort_by;
    private $valid_comparators = [
        'EQUALS', '=',
        'LESS THAN', '<',
        'GREATER THAN', '>',
        '<=', '>=',
        '<>', '!=', 'NOT EQUAL',
        'LIKE', 'INCLUDES'
    ];
    
    public function __construct($db_connector, $config, $form_data)
    {
        $this->db_connector = $db_connector;
        $this->config = $config;
        $this->sql_filters = $this->getSQLFilters($form_data);
        $this->fields = $config['fields_to_save'] ?? [];
        $this->columns_to_fetch = $this->getFetchColumns($form_data, $this->fields);
        $this->sort_by = $this->getSortColumn($form_data, $this->fields);
    }
    
    private function checkFilterArray ($filter)
    {
        $has_necessary = 
            isset($filter->column_name)
            && isset($filter->comparator)
            && isset($filter->filter_value);
        $comparator_is_valid = in_array(strtoupper($filter->comparator), $this->valid_comparators);
        $result = $has_necessary && $comparator_is_valid;
        return $result;
    }    

    
    private function getFetchColumns ($form_data, $fields)
    {
        $default = '*';
        if (
            !isset($form_data['fields'])
            || gettype($form_data['fields']) !== 'string'
            || trim($form_data['fields']) === '*'
        ) {
            return $default;
        }
        $valid_columns = [];
        $columns = explode(',', $form_data['fields']);
        foreach ($columns as $column) {
            $col_name = trim($column);
            if (isset($fields[$col_name])) {
                array_push($valid_columns, $col_name);
            } else {
                foreach ($fields as $field => $data) {
                    if (!empty($data['alias']) && $data['alias'] === $col_name) {
                        array_push($valid_columns, $field);
                    }
                }
            }
        }
        if (count($valid_columns) > 0) {
            return implode(',', $valid_columns);
        }
        return $default;
    }

    private function getSortColumn ($form_data, $fields)
    {
        if (
            !isset($form_data['sort_by'])
            || gettype($form_data['sort_by']) !== 'string'
        ) {
            return null;
        }
        $sort_by = trim($form_data['sort_by']);
        if (!empty($fields[$sort_by])) {
            return $sort_by;
        }
        foreach ($fields as $field => $data) {
            if (!empty($data['alias']) && $data['alias'] === $sort_by) {
                return $field;
            }
        }
        return null;
    }

    private function getSQLFilters ($form_data)
    {
        if (!isset($form_data['filters'])) {
            return [];
        }
        $fields = $this->fields;
        $submitted_filters = json_decode($form_data['filters']);
        $verified_filters = [];
        foreach($submitted_filters as $filter) {
            $submitted_name = $filter->column_name;
            if ($this->checkFilterArray($filter) === true) {
                $column = null;
                if (!empty($fields[$submitted_name])) {
                    $column = $submitted_name;
                    $data_type = strtoupper($fields[$submitted_name]['type']);
                } else if ($this->config['submission_date_column']) {
                    $column = $submitted_name;
                    $data_type = 'TEXT';
                } else {
                    // find field name alias if it doesn't match a column name
                    foreach ($fields as $config_field => $config_data) {
                        if (isset($config_data['alias']) && $config_data['alias'] === $submitted_name) {
                            $column = $config_field;
                            $data_type = strtoupper($config_data['type']);
                            break;
                        }
                    }
                }
                if (!$column) {
                    continue;
                }
                $comparator = $this->reduceComparator($filter->comparator);
                $value = $data_type === 'INTEGER'
                    ? intval($filter->filter_value)
                    : "'" . trim($filter->filter_value) . "'";
                if ($data_type === 'INTEGER') {
                    if ($comparator === 'LIKE') {
                        $comparator === '=';
                    }
                    $value = intval($filter->filter_value);
                } else if ($comparator === 'LIKE') {
                    $value = "'%" . trim($filter->filter_value) . "%'";
                } else {
                    $value = "'" . trim($filter->filter_value) . "'";
                }
                $sql_fragment = $column . " " . $comparator . " " . $value;
                array_push($verified_filters, $sql_fragment);
            }
        }
        return $verified_filters;
    }

    private function reduceComparator ($comp)
    {
        $comp = strtoupper($comp);
        switch ($comp) {
            case 'EQUALS':
                return '=';
            case 'LESS THAN':
                return '<';
            case 'GREATER THAN':
                return '>';
            case 'NOT EQUAL':
                return '<>';
            case 'INCLUDES':
                return 'LIKE';
            default:
                return $comp;
        }
    }

    public function getSubmitted()
    {
        $sql = "SELECT " . $this->columns_to_fetch . " FROM " . $this->config['table_name'];
        if (count($this->sql_filters) > 0) {
            $sql .= " WHERE ";
            foreach($this->sql_filters as $filter_statement) {
                $sql .= $filter_statement . " AND ";
            }
            $sql = substr($sql, 0, -5);
        }
        if ($this->sort_by) {
            $sql .= ' ORDER BY ' . $this->sort_by;
        }
        $data_rows = $this->db_connector->doQuery($sql);
        if ($data_rows === false) {
            return [
                'success' => false,
                'message' => 'Could not retrieve forms from database: '
            ];
        }
        $result = [
            'success' => true
        ];
        if (count($data_rows) === 0) {
            $result['message'] = 'No matching records were found.';
        } else {            
            $result['message'] = count($data_rows) . ' record[s] found.';
            /* replace column names with alias when used */
            foreach($data_rows as &$row) {
                foreach($row as $col => $val) {
                    if (!empty($this->fields[$col]['alias'])) {
                        $row[$this->fields[$col]['alias']] = $val;
                        unset($row[$col]);
                    }
                }
            }
        }
        $result['data'] = $data_rows;
        return $result;
    }
}
?>