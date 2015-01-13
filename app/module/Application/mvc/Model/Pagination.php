<?php

namespace Application\Model;

/**
 * Pagination
 */
class Pagination {
    
    /**
     * Load Paginator
     * @param   $data       object
     * @param   $page       int
     * @param   $config     array       
     * @return  Zend\Paginator\Paginator
     */   
    public static function loadPaginator($data = array(), $page = 1, $config = array()) {
        $iteratorAdapter = new \Zend\Paginator\Adapter\Iterator($data);
        $paginator = new \Zend\Paginator\Paginator($iteratorAdapter);
        $paginator->setCurrentPageNumber($page);
        $paginator->setPageRange($config['pagination']['page_range']);
        $paginator->setItemCountPerPage($config['pagination']['per_page']);
        
        return $paginator;
    }
    
}