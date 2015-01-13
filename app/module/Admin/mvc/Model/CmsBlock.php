<?php
namespace Admin\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Sql;

class CmsBlock extends AbstractTableGateway {
    
    /**
     * Table Name
     */
    protected $table = 'cms_block';
    
    /**
     * Constructor
     * @param  $adapter  Zend\Db\Adapter\Adapter
     */
    public function __construct(Adapter $adapter) 
    {
        $this->adapter = $adapter;
        $this->initialize();
    }
    
    /**
     * Save Block Information
     * @param   $blockid    int
     * @param   $data       array
     * @return  boolean|int
     */
    public function setData($blockid = 0, $data = array()) 
    {

        if ($blockid == 0) {
            if ($this->insert($data)) {
                return $this->getLastInsertValue();
            }
            return 0;
        } elseif ($blockid) {
            $this->update(
                    $data,
                    array(
                        'block_id' => $blockid,
                    )
            );
            return  $blockid;
        } else {
            throw new \Exception("Failed to save data.");
        }
    }
    
    /**
     * Delete Block Data
     * @param   $blockid    int
     * @return  boolean|int
     */
    public function deleteData($blockid = 0) 
    {

        return $this->delete(array(
            'block_id' => $blockid
        ));
        
    }
    
    /**
     * Get Block Data
     * @param   $blockid    int
     * @return  array
     */
    public function getData($blockid = 0, $isActive = 0) 
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from(array('b' => $this->table));
               
        if ($blockid)
            $select->where(array('b.block_id' => $blockid));
        
        if ($isActive)
            $select->where(array('b.block_status' => $isActive));
              
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        $resultSet = new ResultSet;
        $resultSet->initialize($result);
        if (!$blockid) {
            $resultSet->buffer();
            $resultSet->next();
        }
        return $resultSet;
    }

}

