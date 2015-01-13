<?php
namespace Admin\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Sql;

class CmsPages extends AbstractTableGateway {

    protected $table = 'cms_pages';
    
    public function __construct(Adapter $adapter) 
    {
        $this->adapter = $adapter;
        $this->initialize();
    }
    

    public function setData($pageid = 0, $data = array()) 
    {

        if ($pageid == 0) {
            if ($this->insert($data)) {
                return $this->getLastInsertValue();
            }
            return 0;
        } elseif ($pageid) {
            $this->update(
                    $data,
                    array(
                        'page_id' => $pageid,
                    )
            );
            return  $pageid;
        } else {
            throw new \Exception("Failed to save data.");
        }
    }

    public function deleteData($pageid = 0) 
    {

        return $this->delete(array(
            'page_id' => $pageid
        ));
        
    }
    
    public function getData($pageid = 0) 
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from(array('p' => $this->table));
               
        if ($pageid)
            $select->where(array('p.page_id' => $pageid));
     
              
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        $resultSet = new ResultSet;
        $resultSet->initialize($result);
        if (!$pageid) {
            $resultSet->buffer();
            $resultSet->next();
        }
        return $resultSet;
    }
    
    
    public function getDataByIdentifier($identifier = '') 
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from(array('p' => $this->table));
               
        $select->where(array('p.page_identifier' => $identifier));
        $select->where(array('p.is_active' => 1));
              
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        $resultSet = new ResultSet;
        $resultSet->initialize($result);
        return $resultSet;
    }
}

