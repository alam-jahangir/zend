<?php
namespace Application\View;
use Zend\View\Helper\AbstractHelper;
use Application\Model\ConfigCache as Cache;

class Navigation extends AbstractHelper
{ 
    
    public function __construct($adapter) 
	{
        $this->adapter = $adapter;
    }
    
    public function __invoke($value = '') 
	{
        $cache = new Cache();
        
        //if (!$menuHtml = $cache->getItem('navigation')) {
        	$categories = new \Admin\Model\Categories($this->adapter);
        	$categoryList = $categories->getData(0, 1, 1);
        	$menuHtml = $this->_drawHtml($categoryList, $categories);
            $cache->setItem('navigation', $menuHtml);
	    //}
	    
	    return $menuHtml;
		    	
	}
	
	private function _drawHtml($categoryList = array(), $categories)
	{
		$menuHtml = '';
		$mobileMenu = '';
		
		$view = $this->getView();
        //$baseUrl = $view->basePath('');
        
		foreach ($categoryList as $category) {
			
			$mobileMenu .= '<option value="#">'.$category->category_name.'</option>';
			
			
			//if ($category->submenu_item) {
				
				//$submenu_item = explode(',', $category->submenu_item);
				$menuHtml .= '<li class="dropdown">
		            		<a class="dropdown-toggle disabled" data-toggle="dropdown" href="'.$view->url('category', array('action' => 'category', 'identifier' => $category->identifier, 'catid' =>  $category->category_id)).'">
							'.$category->category_name;
				
				$subCategories = $categories->getSubCategory($category->category_id);
							
				if (count($subCategories)) {
					
					$menuHtml .= '<b class="caret"></b></a>';
					$menuHtml .= '<ul class="dropdown-menu">';
					
					foreach($subCategories as $subCategory) {
						
						//$submenu_item = explode(',', $subCategory->submenu_item);
						
						$mobileMenu .= '<option value="'.$view->url('category', array('action' => 'category', 'identifier' => $subCategory->identifier, 'catid' => $subCategory->category_id)).'">- '.$subCategory->category_name.'</option>';
						
						$menuHtml .= '<li><a href="'.$view->url('category', array('action' => 'category', 'identifier' => $subCategory->identifier, 'catid' => $subCategory->category_id)).'">'.$subCategory->category_name.'</a></li>';
						
					}
					
					$menuHtml .= '</ul>';
					
				} else {
					$menuHtml .= '</a>';
				}
				
				
				
			//}
		    
		}
		return array($menuHtml, $mobileMenu);
	}
    
}