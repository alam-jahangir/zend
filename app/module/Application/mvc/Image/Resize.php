<?php
namespace Application\Image;
use Zend\Filter\FilterInterface as FilterInterface;
use Zend\Filter\Exception\ExceptionInterface as ExceptionInterface;
use Application\Image\ResizeAdapterGd as ResizeAdapterGd;
 
/**
 * Resizes a given file and saves the created file
 *
 * @category   Application_Image
 * @package    Application_Image_Filter
 */
class Resize implements FilterInterface
{
    protected $width = null;
    protected $height = null;
    protected $keepRatio = true;
    protected $keepSmaller = true;
    protected $adapter = null;
    protected $baseDir = null;
 
    /**
     * Create a new resize filter with the given options
     *
     * @param Zend_Config|array $options Some options. You may specify: width, 
     * height, keepRatio, keepSmaller (do not resize image if it is smaller than
     * expected), directory (save thumbnail to another directory),
     * adapter (the name or an instance of the desired adapter)
     * @return Skoch_Filter_File_Resize An instance of this filter
     */
    public function __construct($options = array())
    {

        $this->baseDir = str_replace('/', DS , BASE_PATH).DS;

        $this->adapter = new ResizeAdapterGd();
        return $this;
    }
    
    /**
     * Set Resize Image Configaration Information
     * @param   int     $width
     * @param   int|null    $height
     */ 
    public function setResizeOption($options = array()) {
                
        if (isset($options['width'])) {
            $this->width = $options['width'];
        }
        if (isset($options['height'])) {
            $this->height = $options['height'];
        }
        if (isset($options['keepRatio'])) {
            $this->keepRatio = $options['keepRatio'];
        }
        if (isset($options['keepSmaller'])) {
            $this->keepSmaller = $options['keepSmaller'];
        }
    }

 
    /**
     * Defined by Zend\Filter\FilterInterface
     *
     * Resizes the file $file according to the defined settings
     *
     * @param  string $file Full path of file to change
     * @return string The filename which has been set, or false when there were errors
     */
    public function filter($file)
    {
        if (!isset($this->width) && !isset($this->height)) {
            throw new \Exception('At least one of width or height must be defined');
        }
        
        $target = dirname($file);
        
        if (!$this->fileExists($this->baseDir . $file)) {
            throw new \Exception('Image file was not found.');
        }
         
        if (!$this->checkMemory($this->baseDir . $file)) {
            throw new \Exception('Memory limit exceed.');
        }
         
        //$filePath = pathinfo($file);
        if ($this->checkMemory($file)) {
            return  $this->adapter->resize($this->width, $this->height,
                $this->keepRatio, $file, $target, $this->keepSmaller);
        }
    }
    
    
    /**
     * First check this file on FS
     * If it doesn't exist - try to download it from DB
     *
     * @param string $filename
     * @return bool
     */
    protected function fileExists($filename) {
        if (file_exists($filename)) {
            return true;
        }
        return false;
    }
    
    /**
     * Check Memory
     * @param   string $file
     */
    protected function checkMemory($file = null)
    {
//        print '$this->_getMemoryLimit() = '.$this->_getMemoryLimit();
//        print '$this->_getMemoryUsage() = '.$this->_getMemoryUsage();
//        print '$this->_getNeedMemoryForBaseFile() = '.$this->_getNeedMemoryForBaseFile();

        return $this->getMemoryLimit() > ($this->getMemoryUsage() + $this->getNeedMemoryForFile($file)) || $this->getMemoryLimit() == -1;
    }

    protected function getMemoryLimit()
    {
        $memoryLimit = trim(strtoupper(ini_get('memory_limit')));

        if (!isSet($memoryLimit[0])){
            $memoryLimit = "128M";
        }

        if (substr($memoryLimit, -1) == 'K') {
            return substr($memoryLimit, 0, -1) * 1024;
        }
        if (substr($memoryLimit, -1) == 'M') {
            return substr($memoryLimit, 0, -1) * 1024 * 1024;
        }
        if (substr($memoryLimit, -1) == 'G') {
            return substr($memoryLimit, 0, -1) * 1024 * 1024 * 1024;
        }
        return $memoryLimit;
    }

    protected function getMemoryUsage()
    {
        if (function_exists('memory_get_usage')) {
            return memory_get_usage();
        }
        return 0;
    }

    protected function getNeedMemoryForFile($file = null)
    {
        if (!$file) {
            return 0;
        }

        if (!file_exists($file) || !is_file($file)) {
            return 0;
        }

        $imageInfo = getimagesize($file);

        if (!isset($imageInfo[0]) || !isset($imageInfo[1])) {
            return 0;
        }
        if (!isset($imageInfo['channels'])) {
            // if there is no info about this parameter lets set it for maximum
            $imageInfo['channels'] = 4;
        }
        if (!isset($imageInfo['bits'])) {
            // if there is no info about this parameter lets set it for maximum
            $imageInfo['bits'] = 8;
        }
        return round(($imageInfo[0] * $imageInfo[1] * $imageInfo['bits'] * $imageInfo['channels'] / 8 + Pow(2, 16)) * 1.65);
    }
}