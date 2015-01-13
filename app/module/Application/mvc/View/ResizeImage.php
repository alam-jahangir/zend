<?php
namespace Application\View;
use Zend\View\Helper\AbstractHelper;
use Application\Model\Resize;

class ResizeImage  extends AbstractHelper
{ 
    
    public function __invoke($options = array(), $filename = '') {
        if (!isset($options['width']) && !isset($options['height'])) {
            throw new \Exception('At least one of width or height must be defined');
        }
        
        if (!$filename) {
            throw new \Exception('Invalid image file name.');
        }
        
        $imageResize = new ImageResize();
        $imageResize->setResizeOption(array(
            'width' => 35,
            'height' => 35,
            'keepRatio' => true,
        ));
        return  $imageResize->filter('media'.DS.'user_avatar'.DS.'Tulips.jpg');
    
    }
    
}