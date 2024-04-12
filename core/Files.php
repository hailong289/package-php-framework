<?php

namespace System\Core;

class Files {

    public static function instance(){
        return new Files();
    }


    public function upload($file = null, $path = '', $filename = '', $extension = '')
    {
        try{
            $path = endsWith($path,'/') ? $path:$path.'/';
            $uploadDir = __DIR__ROOT . $path;
            if(!empty($path)){
                throw new \Exception('Path must not be empty');
            }
            if(!file_exists($uploadDir)){
                mkdir($uploadDir, 0777, true);
            }
            $extensionName = !empty($extension) ? $extension:$file->extension();
            // Create file name
            if(!empty($filename)){
                $fileName = $filename.$extensionName;
            } else {
                $fileName = time().'.'.$extensionName;
            }
            $uploadDir = endsWith($uploadDir,'/') ? $uploadDir:$uploadDir.'/';
            $target_file = $uploadDir . basename($fileName);
            if (!move_uploaded_file($file->tmp_name(), $target_file)) {
                throw new \Exception('Sorry, there was an error uploading your file.');
            }
            return [
                'uploaded' => 1,
                'url' => $target_file,
                'msg' => 'Success'
            ];
        }catch (Exception $e){
            return [
                'uploaded' => 0,
                'url' => null,
                'msg' => 'Error'
            ];
        }
    }

    public function uploadCropImage($file = null, $width = 0, $height = 0, $x = 0, $y = 0)
    {
        $src = imagecreatefrompng($file);
        $dest = imagecreatetruecolor($width, $height);
        imagecopy(
            $dest,
            $src,
            0,    // 0x of your destination
            0,    // 0y of your destination
            $x,   // middle x of your source
            $y,   // middle y of your source
            $width,  // 30px of width
            $height   // 20px of height
        );
        imagepng($dest, '2.png');
        imagedestroy($dest);
        imagedestroy($src);
    }
}