<?php
namespace Application\Image;
 
 
/**
 * Resizes a given file and saves the created file
 *
 * @category   Skoch
 * @package    Skoch_Filter
 */
abstract class ResizeAdapter
{
    abstract public function resize($width, $height, $keepRatio, $file, $target, $keepSmaller = true);
 
    protected function _calculateWidth($oldWidth, $oldHeight, $width, $height)
    {
        // now we need the resize factor
        // use the bigger one of both and apply them on both
        $factor = max(($oldWidth/$width), ($oldHeight/$height));
        return array($oldWidth/$factor, $oldHeight/$factor);
    }
}