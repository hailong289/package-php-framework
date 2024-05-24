<?php

namespace System\Core;

class Files {

    public static function instance(){
        return new Files();
    }


    public function upload($file, $path, $filename = '', $extension = '')
    {
        try{
            $uploadDir = __DIR__ROOT .'/' . $path;
            if(empty($path)){
                throw new \Exception('Path must not be empty');
            }
            if(!file_exists($uploadDir)){
                mkdir($uploadDir, 0777, true);
            }
            $extensionName = !empty($extension) ? $extension:$file->extension();
            // Create file name
            if(!empty($filename)){
                $fileName = $filename.".$extensionName";
            } else {
                $fileName = time().".$extensionName";
            }
            $uploadDir = endsWith($uploadDir,'/') ? $uploadDir:$uploadDir.'/';
            $target_file = $uploadDir . basename($fileName);
            if (!move_uploaded_file($file->tmp_name(), $target_file)) {
                throw new \Exception('Sorry, there was an error uploading your file.');
            }
            return [
                'uploaded' => 1,
                'url' => $target_file,
                'filename' => $fileName,
                'msg' => 'Success'
            ];
        }catch (\Exception $e){
            return [
                'uploaded' => 0,
                'url' => null,
                'filename' => null,
                'msg' => $e->getMessage() ?? 'Error upload file'
            ];
        }
    }

    public function uploadAndResizeImage(
        $files = [
            'file' => null,
            'path' => null,
            'filename' => null,
            'extension' => null
        ],
        $resize = [
            'width' => 0,
            'height' => 0,
            'quality' => 75,
        ]
    )
    {
        try {
            $upload = $this->upload($files['file'], $files['path'], $files['filename'], $files['extension']);
            if(!$upload['uploaded']) {
                throw new \Exception($upload['msg']);
            }
            $resize = $this->resizeImage(
                $upload['url'],
                $files['path'],
                $upload['filename'] ?? time(),
                $resize['width'],
                $resize['height'],
                $resize['quality']
            );
            if(!$resize) {
                throw new \Exception('Resize image failed');
            }
            return [
                'uploaded' => 1,
                'url' => $upload['url'],
                'msg' => 'Success'
            ];
        }catch (\Exception $e) {
            return [
                'uploaded' => 0,
                'url' => null,
                'msg' => $e->getMessage() ?? 'Error'
            ];
        }
    }

    public function make_thumb($src, $dest, $desired_width) {
        $info = getimagesize($src);
        $imgtype = image_type_to_mime_type($info[2]);

        /* read the source image */
        switch ($imgtype) {
            case 'image/jpeg':
                $source_image = imagecreatefromjpeg($src);
                break;
            case 'image/gif':
                $source_image = imagecreatefromgif($src);
                break;
            case 'image/png':
                $source_image = imagecreatefrompng($src);
                break;
            default:
                die('Invalid image type.');
        }

        $width = imagesx($source_image);
        $height = imagesy($source_image);

        /* find the "desired height" of this thumbnail, relative to the desired width  */
        $desired_height = floor($height * ($desired_width / $width));

        /* create a new, "virtual" image */
        $virtual_image = imagecreatetruecolor($desired_width, $desired_height);

        /* copy source image at a resized size */
        imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);

        /* create the physical thumbnail image to its destination */
        imagejpeg($virtual_image, $dest);
    }

    /*
	 	Creates and returns a thumbnail, keeping the aspect ratio of the source image.
	 */
    function create_thumb($src, $desired_width) {
        $info = getimagesize($src);
        $imgtype = image_type_to_mime_type($info[2]);

        /* read the source image */
        switch ($imgtype) {
            case 'image/jpeg':
                $source_image = imagecreatefromjpeg($src);
                break;
            case 'image/gif':
                $source_image = imagecreatefromgif($src);
                break;
            case 'image/png':
                $source_image = imagecreatefrompng($src);
                break;
            default:
                die('Invalid image type.');
        }

        $width = imagesx($source_image);
        $height = imagesy($source_image);

        /* find the "desired height" of this thumbnail, relative to the desired width  */
        $desired_height = floor($height * ($desired_width / $width));

        /* create a new, "virtual" image */
        $virtual_image = imagecreatetruecolor($desired_width, $desired_height);

        /* copy source image at a resized size */
        imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);

        /*
         * imageXXX() only has two options, save as a file, or send to the browser.
         * It does not provide you the oppurtunity to manipulate the final GIF/JPG/PNG file stream
         * So I start the output buffering, use imageXXX() to output the data stream to the browser,
         * get the contents of the stream, and use clean to silently discard the buffered contents.
        */
        ob_start();

        /* create the thumbnail image */
        imagejpeg($virtual_image, null);

        $final_image = ob_get_contents();

        ob_end_clean();

        return $final_image;
    }

    /*
        Crops an image to the specified thumbnail size, starting the cropping from $x and $y.
    */
    public function cropImageToSquare($imgSrc, $pathDest, $fileName, $thumbSize, $x = 0, $y = 0, $quality = 100) {
        //getting the image dimensions
        $info = getimagesize($imgSrc);
        $width = $info[0];
        $height = $info[1];
        $type = $info['mime'];
        //saving the image into memory (for manipulation with GD Library)
        switch ($type) {
            case 'image/jpeg':
                $myImage = imagecreatefromjpeg($imgSrc);
                break;
            case 'image/gif':
                $myImage = imagecreatefromgif($imgSrc);
                break;
            case 'image/png':
                $myImage = imagecreatefrompng($imgSrc);
                break;
            case 'image/webp':
                $myImage = imagecreatefromwebp($imgSrc);
                break;
            default:
                die('Invalid image type.');
        }

        // calculating the part of the image to use for thumbnail
        $smallestSide = min($height, $width);

        // copying the part into thumbnail
        $thumb = imagecreatetruecolor($thumbSize, $thumbSize);
        imagecopyresampled($thumb, $myImage, 0, 0, $x, $y, $thumbSize, $thumbSize, $smallestSide, $smallestSide);
        if(!file_exists($pathDest)){
            mkdir($pathDest, 0777, true);
        }
        switch ($type) {
            case 'image/jpeg':
                $status = imagejpeg($thumb, $pathDest.'/'.$fileName.'.jpg', $quality);
                break;
            case 'image/gif':
                $status = imagegif($thumb, $pathDest.'/'.$fileName.'.gif', $quality);
                break;
            case 'image/png':
                $status = imagepng($thumb, $pathDest.'/'.$fileName.'.png', $quality);
                break;
            case 'image/webp':
                $status = imagewebp($thumb, $pathDest.'/'.$fileName.'.webp', $quality);
                break;
            default:
                $status = false;
        }
        return $status;
    }

    /*
        Resizes an image and puts it on top of a white, square background.
        Returns true if successful, false otherwise.
    */
    public function resizeImage($source_image, $path_resize, $file_name_resize, $tn_w = 0, $tn_h = 0, $quality = 75) {
        $info = getimagesize($source_image);
        $imgtype = $info['mime'];

        #assuming the mime type is correct
        switch ($imgtype) {
            case 'image/jpeg':
                $source = imagecreatefromjpeg($source_image);
                break;
            case 'image/gif':
                $source = imagecreatefromgif($source_image);
                break;
            case 'image/png':
                $source = imagecreatefrompng($source_image);
                break;
            case 'image/webp':
                $source = imagecreatefromwebp($source_image);
                break;
            default:
                die('Invalid image type.');
        }

        #Figure out the dimensions of the image and the dimensions of the desired thumbnail
        $src_w = imagesx($source);
        $src_h = imagesy($source);

        #Do some math to figure out which way we'll need to crop the image
        #to get it proportional to the new size, then crop or adjust as needed

        $x_ratio = $tn_w / $src_w;
        $y_ratio = $tn_h / $src_h;

        if (($src_w <= $tn_w) && ($src_h <= $tn_h)) {
            $new_w = $src_w;
            $new_h = $src_h;
        } elseif (($x_ratio * $src_h) < $tn_h) {
            $new_h = ceil($x_ratio * $src_h);
            $new_w = $tn_w;
        } else {
            $new_w = ceil($y_ratio * $src_w);
            $new_h = $tn_h;
        }

        $newpic = imagecreatetruecolor(round($new_w), round($new_h));
        imagecopyresampled($newpic, $source, 0, 0, 0, 0, $new_w, $new_h, $src_w, $src_h);
        $final = imagecreatetruecolor($tn_w, $tn_h);
        $backgroundColor = imagecolorallocate($final, 255, 255, 255);
        imagefill($final, 0, 0, $backgroundColor);
//        imagecopyresampled($final, $newpic, 0, 0, ($x_mid - ($tn_w / 2)), ($y_mid - ($tn_h / 2)), $tn_w, $tn_h, $tn_w, $tn_h);
        imagecopy($final, $newpic, (($tn_w - $new_w)/ 2), (($tn_h - $new_h) / 2), 0, 0, $new_w, $new_h);
        if(!file_exists($path_resize)){
            mkdir($path_resize, 0777, true);
        }
        switch ($imgtype) {
            case 'image/jpeg':
                $status = imagejpeg($final, $path_resize.'/'.$file_name_resize.'.jpg', $quality);
                break;
            case 'image/gif':
                $status = imagegif($final, $path_resize.'/'.$file_name_resize.'.gif', $quality);
                break;
            case 'image/png':
                $status = imagepng($final, $path_resize.'/'.$file_name_resize.'.png', $quality);
                break;
            case 'image/webp':
                $status = imagewebp($final, $path_resize.'/'.$file_name_resize.'.webp', $quality);
                break;
            default:
                $status = false;
        }
        return $status;
    }

    public function copy($file, $newfile)
    {
        if (!copy($file, $newfile)) {
            return false;
        }
        return true;
    }
}