<?php
namespace Application\Image;

use Application\Image\ResizeAdapter as ResizeAdapter;
 
/**
 * Resizes a given file with the gd adapter and saves the created file
 *
 * @category   Application_Image
 * @package    Application_Image_Filter
 */
class ResizeAdapterGd extends ResizeAdapter
{
    public function resize($width, $height, $keepRatio, $file, $target, $keepSmaller = true)
    {
        list($oldWidth, $oldHeight, $type) = getimagesize($file);
 
        switch ($type) {
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($file);
                break;
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($file);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($file);
                break;
            default:
                return $file;
                break; 
        }
        
        $target = str_replace(DS, '/', $target).'/'.$width.'x'.$height.'/'.basename($file);
        
        if (!$keepSmaller || $oldWidth > $width || $oldHeight > $height) {
            if ($keepRatio) {
                list($width, $height) = $this->_calculateWidth($oldWidth, $oldHeight, $width, $height);
            }
        } else {
            $width = $oldWidth;
            $height = $oldHeight;
        }
        
        
        $thumb = imagecreatetruecolor($width, $height);
 
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
 
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $width, $height, $oldWidth, $oldHeight);
        
        
        if( !@is_dir( dirname($target))) {
			@mkdir( dirname($target) );
            @chmod(dirname($target) , 0777);
		}
        
        switch ($type) {
            case IMAGETYPE_PNG:
                imagepng($thumb, $target);
                break;
            case IMAGETYPE_JPEG:
                imagejpeg($thumb, $target, 100);
                break;
            case IMAGETYPE_GIF:
                imagegif($thumb, $target);
                break;
        }
        
        imagedestroy($thumb);
        imagedestroy($source);
        
        return $target;
    }
}