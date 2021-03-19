<?php

return [
    'database_path' => __DIR__ . './webform.db',
    'table_name' => 'submissions',
    'primary_key' => 'ID',
    'fields_to_save' => [
        'name' => [
            'type' => 'TEXT',
            'required' => true,
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
            'alias' => 'Random numeral'
        ],        
        'random_boolean' => [
            'type' => 'INTEGER',
            'required' => false, 
            'alias' => 'True of false'
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