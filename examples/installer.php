<?php 

$config = include (__DIR__ . './config.php');

$db_path = $config['database_path'];

if (file_exists($db_path) && filesize($db_path) > 0) {
    echo 'Please delete the existing database manually before reinstalling.';
    exit();
}

$database = new SQLite3($db_path);
$fields = $config['fields_to_save'];

try {
    $sql = "CREATE TABLE " . $config['table_name'];
    $sql .= " (" . $config['primary_key'];
    $sql .= " INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, ";
    foreach($fields as $field => $field_data) {
        $sql .= $field . " " . $field_data['type'];
        if (isset($field_data['type']) && $field_data['type'] === true) {
          $sql . " NOT NULL";
        }
        $sql .= ", ";
    }
    if (!empty($config['submission_date_column'])) {
      $sql .= $config['submission_date_column'] . " TEXT, ";
    }
    if (!empty($config['attachments']['column_name'])) {
      $sql .= $config['attachments']['column_name'] . " TEXT, ";
    }
    $sql = substr($sql, 0, -2);
    $sql .= ");";

    $query = $database->query($sql);
    if ($query === false) {
        echo 'Could not create database: ' . $database->lastErrorMsg(); 
    } else {
        echo 'Database installed successfully.';
    }
} catch (Exception $exc) {
  echo $exc;
}

?>