<?php
namespace Application\View;
use Zend\View\Helper\AbstractHelper;

class ParsePageContent extends AbstractHelper
{ 
    public function __invoke($content = '') 
    {
        //echo $this->getView()->url('home'); exit;
        $view = $this->getView();
        $baseUrl = $view->basePath('');
        //$content = $view->escapeHtml(str_replace('{{base_url}}', $baseUrl, $content));
        $content = str_replace('{{base_url}}', $baseUrl, $content);
        return $content;
    }
    
}