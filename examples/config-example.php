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
            'alias' => 'True or false'
        ]
    ],
    'submission_date_column' => 'submitted_on',
    'open_data_access' => false,
    'attachments' => [        
        'save_path' => '/examples/uploads/',
        'column_name' => 'attached_files',
        'required' => false,
        'allowed_file_types' => ['pdf', 'png', 'jpg', 'jpeg', 'gif'],
        'process_images' => true,
        'jpg_quality' => 80,
        'max_image_dimensions' => [
            'width' => 480,
            'height' => 720
        ]
    ]
];

?>