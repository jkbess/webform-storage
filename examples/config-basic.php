<?php

return [
    'database_path' => __DIR__ . './webform.db',
    'table_name' => 'submissions', // name of table in database
    'primary_key' => 'ID',
    'fields_to_save' => [
        // for each field...
        'field_name' => [
            'type' => '', //NULL, INTEGER, REAL, TEXT, BLOB
            'required' => true, // boolean value
            'alias' => '' // optional: enter name of form field when different that table's field_name
        ]
    ],
    'include_submission_date' => 'field_name', // column in which to save datetime on form submission
    'open_data_access' => false,
    'attachments' => [
        'column_name' => null,
        'allowed_file_types' => ['pdf', 'png', 'jpg', 'jpeg', 'gif']
    ]
];

?>