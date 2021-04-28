<?php

return [
    'database_path' => __DIR__ . './webform.db', // path to the SQLite database itself
    'table_name' => 'submissions', // name of table in database
    'primary_key' => 'ID', // table column with autoincrementing primary key
    'fields_to_save' => [
        // for each field...
        'field_name' => [
            'type' => '', //NULL, INTEGER, REAL, TEXT, BLOB
            'required' => true, // boolean value
            'alias' => '' // optional: enter name of form field when different that table's field_name
        ]
    ],
    // column in which to save datetime on form submission; set to false to not save this date
    'submission_date_column' => 'field_name',
    // settings for handling files attached to web forms
    'attachments' => [
        'save_path' => '/path/to/attachments/', // path to folder where form attachments should be saved, starting from the root folder
        'column_name' => 'attachment_urls', // table column in which to store the URLs of uploaded files 
        'required' => false, // whether attachments must be included with form submission
        'allowed_file_types' => ['pdf', 'png', 'jpg', 'jpeg', 'gif'], // file types not listed will be rejected
        'process_images' => true, // whether to resize JPGs and PNG to reduce files or dimensions per settings below
        'jpg_quality' => 90, // compression level for shrinking JPGs
        'max_image_dimensions' => [ // will reduce images to fit, keeping the aspect ratio the same
            'width' => 640,
            'height' => 720
        ]
    ]
];

?>