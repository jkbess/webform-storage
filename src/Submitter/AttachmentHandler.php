<?php

namespace Webform\Submitter;

class AttachmentHandler implements AttachmentHandlerInterface
{    
    private $allowed_extensions;
    private $jpg_quality;
    private $max_dimensions;
    private $settings;

    public function __construct ($attachments_settings)
    {
        $this->allowed_extensions = $attachments_settings['allowed_file_types'] ?? [];
        $this->process_images = $attachments_settings['process_images'] ?? false;
        $this->jpg_quality = $attachments_settings['jpg_quality'] ?? 90;
        $this->max_dimensions = $attachments_settings['max_image_dimensions'] ?? [];
    }
    
    private function buildUploadUrl($file_name)
    {
        $folder = substr(getcwd(), strlen($_SERVER['DOCUMENT_ROOT']));
        $folder =  dirname($folder, 2);
        $folder = str_replace("\\", '/', $folder);
        $folder = ($folder === '/') ? '' : $folder;
        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
            ? 'https'
            : 'http';
        $url .= '://' . $_SERVER['HTTP_HOST'];
        $url .= $folder . '/uploads/' . $file_name;
        return $url;
    }

    private function processImage($img_path)
    {
        $isPng = (strtolower(pathinfo($img_path)['extension']) === 'png');
        if ($isPng) {
            $source = imagecreatefrompng($img_path);
        } else {
            $source = imagecreatefromjpeg($img_path);
        }
        list($width, $height) = getimagesize($img_path);
        // if too big, resize
        $max = $this->max_dimensions;
        $scale = 1;
        if (isset($max['width']) && isset($max['height'])) {
            if ($width > $max['width']) {
                $scale = $max['width'] / $width;
            } else if ($height > $max['height']) {
                $scale = $max['height'] / $height;
            }
        }
        $new_width = $width * $scale;
        $new_height = $height * $scale;
        $processed_img = imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled($processed_img, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        if ($isPng) {
            imagepng($processed_img, $img_path);
        } else {
            imagejpeg($processed_img, $img_path, $this->jpg_quality);
        }
    }
    
    private function uploadFile($file)
    {        
        $tmp_name = $file['tmp_name'] ?? false;
        $name = $file['name'] ?? false;
         
        if ($file['error']) {
            error_log('Error:' . $file['error']);
            return false;
        }
        if (!$tmp_name) {
            error_log('Blog: UploadHandler::uploadFile called with no $_FILES');
            return false;
        }
        if (!$name) {
            error_log('An attached file has no name.');
            return false;
        }

        $orig_name = explode('.', $name);
        $ext = strtolower(array_pop($orig_name));
        if (
            count($this->allowed_extensions)
            && $ext != 'exe'
            && !in_array($ext, $this->allowed_extensions)
        ) {
            error_log('The file type ' . $ext . ' can not be uploaded.');
            return false;
        }
        $new_name = implode('-', $orig_name);
        $new_name = preg_replace('/\s+/', '-', $new_name);
        $new_name = preg_replace('/[^\w-]/', '', $new_name);
        $file_name = $new_name . '.' . $ext;
        $destination = __DIR__ . '/../../uploads/' . $file_name;
        // if file name exists, append numeral
        $copy_number = 0;
        while (file_exists($destination)) {
            $copy_number += 1;
            $file_name = $new_name . '-' . strval($copy_number) . '.' . $ext;
            $destination = __DIR__ . '/../../uploads/' . $file_name;
        }
        if (!move_uploaded_file($tmp_name, $destination)) {
            error_log('There was an error uploading the file: ' . $tmp_name);
            return false;
        }
        if (in_array($ext, ['jpg', 'jpeg', 'png']) && $this->process_images) {
           $this->processImage($destination);
        }
        $url = $this->buildUploadUrl($file_name);
        return $url;
    }
    
    public function getAttachmentUrls()
    {
        $attachments_urls = [];
        foreach($_FILES as $file) {
            $file_url = $this->uploadFile($file);
            if ($file_url) {
                array_push($attachments_urls, $file_url);
            }
        }
        if (!count($attachments_urls)) {
            return false;
        }
        return implode(',', $attachments_urls);
    }
}

?>