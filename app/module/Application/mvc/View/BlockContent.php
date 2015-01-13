<?php
namespace Application\View;
use Zend\View\Helper\AbstractHelper;

class BlockContent extends AbstractHelper
{ 
    
    public function __construct($adapter) {
        $this->adapter = $adapter;
    }
    
    public function footerBlock($block_id = 0) {
        
        $CmsBlockModel = new \Admin\Model\CmsBlock($this->adapter);
        $getBlockContent = $CmsBlockModel->getData($block_id, 1)->toArray();
                
        return $getBlockContent;
    }
    
    public function sidebarBlock($resource_name) {
        
        $CmsBlockModel = new \Admin\Model\CmsBlock($this->adapter);
        $getBlockItems = $CmsBlockModel->getBlockListForSidebar($resource_name)->toArray();
        return $getBlockItems;
    }
    
}