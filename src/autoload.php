<?php

spl_autoload_register(function($className) {
	if (strpos($className, 'Webform\\') === 0) {
		$className = substr($className, 8);
	}
	$file = __DIR__ . '\\' . $className . '.php';
	$file = str_replace('\\', DIRECTORY_SEPARATOR, $file);
	if (file_exists($file)) {
		include $file;
	}
});

?>