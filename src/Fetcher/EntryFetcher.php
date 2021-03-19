<?php

namespace Webform\Fetcher;

use Webform\Fetcher\FieldFetcher;

class EntryFetcher implements EntryFetcherInterface
{
    private $db_connector;
    private $filters;
    private $form_columns;
    private $valid_comparators = [
        'EQUALS', '=',
        'LESS THAN', '<',
        'GREATER THAN', '>',
        '<=', '>=',
        '<>', '!=', 'NOT EQUAL',
        'LIKE', 'INCLUDES'
    ];
    
    public function __construct($db_connector, $form_data)
    {
        $this->db_connector = $db_connector;
        $this->form_columns = $this->getColumnData();
        $this->sql_filters = $this->getSQLFilters($form_data);
        $this->sort_by = $this->getSortColumn($form_data);
    }

    private function getColumnData ()
    {        
        $field_fetcher = new FieldFetcher(['include_default' => true]);
        $table_data = $field_fetcher->getFormFields();
        if (!$table_data['success']) {
            error_log('Webform - error in EntryFetcher: could not verify column names.');
            return [];
        }
        $valid_fields = [];
        foreach($table_data['data'] as $entry) {
            $valid_fields[$entry['columnName']] = $entry['isNumeric'];
        }
        return $valid_fields;
    }

    private function getSortColumn ($form_data)
    {
        if (
            !isset($form_data['sort_by'])
            || gettype($form_data['sort_by']) !== 'string'
        ) {
            return null;
        }
        $sort_by = trim(strtolower($form_data['sort_by']));
        if (!array_key_exists($sort_by, $this->form_columns)) {
            return null;
        }
        return $sort_by;
    }

    private function getSQLFilters ($form_data)
    {
        if (!isset($form_data['filters'])) {
            return [];
        }
        $submitted_filters = json_decode($form_data['filters']);
        $verified_filters = [];
        foreach($submitted_filters as $filter) {
            if ($this->testFilter($filter) === true) {
                $column = strtolower($filter->column_name);
                $comparator = $this->reduceComparator($filter->comparator);
                $value = $this->form_columns[$column] === 'INTEGER'
                    ? intval($filter->filter_value)
                    : "'" . trim($filter->filter_value) . "'";
                if ($this->form_columns[$column] === 'INTEGER') {
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
    
    private function testFilter ($filter)
    {
        $has_necessary = 
            isset($filter->column_name)
            && isset($filter->comparator)
            && isset($filter->filter_value);
        $comparator_is_valid = in_array(strtoupper($filter->comparator), $this->valid_comparators);
        $column_name_is_valid = array_key_exists(strtolower($filter->column_name), $this->form_columns);
        $result = $has_necessary && $comparator_is_valid && $column_name_is_valid;
        return $result;
    }

    public function getSubmitted()
    {
        // get request details from $_POST
        $sql = "SELECT * FROM entries";
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
            'success' => true,
            'data' => $data_rows
        ];
        if (count($data_rows) === 0) {
            $result['message'] = 'No matching records were found.';
        } else {            
            $result['message'] = count($data_rows) . ' records found.';
        }
        return $result;
    }
}
?>