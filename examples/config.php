<?php

return [
    'database_path' => __DIR__ . './webform.db',
    'table_name' => 'submissions', // name of table in database
    'primary_key' => 'ID',
    'fields_to_save' => [
        // for each field...
        'name' => [
            'type' => 'TEXT', // INTEGER, REAL, TEXT - no NULL or BLOB
            'required' => true, // boolean value
            'alias' => null
        ],        
        'email' => [
            'type' => 'TEXT',
            'required' => true, 
            'alias' => null
        ],        
        'random_number' => [
            'type' => 'INTEGER',
            'required' => false, 
            'alias' => null
        ],        
        'random_boolean' => [
            'type' => 'INTEGER',
            'required' => false, 
            'alias' => null
        ]
    ],
    'submission_date_column' => 'submitted_on', // column in which to save datetime on form submission
    'open_data_access' => false,
    'attachments' => [
        'column_name' => null,
        'allowed_file_types' => ['pdf', 'png', 'jpg', 'jpeg', 'gif']
    ]
];

?>