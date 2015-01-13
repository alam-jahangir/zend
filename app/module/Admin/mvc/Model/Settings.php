<?php
namespace Admin\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Sql;
use Application\Model\ConfigCache as Cache;

class Settings extends AbstractTableGateway {
    
    /**
     * Table Name
     */
    protected $table = 'settings';
    
    /**
     * Initialize Database Adapter
     */
    public function __construct(Adapter $adapter) 
    {
        $this->adapter = $adapter;
        $this->initialize();
    }
    
    public function setDataByColumn($columnName = '', $columnValue = null, $data = array()) 
    {

        if ($columnValue) {
            $this->update(
                    $data,
                    array(
                        $columnName => $columnValue,
                    )
            );
            return  $columnValue;
        } else {
            throw new \Exception("Failed to save data.");
        }
    }
    
    public function getDataByColumn($columnName = '', $columnValue = null) 
    {

        if ($columnValue) {
            $rowset = $this->select(
                    array(
                        $columnName => $columnValue
                    )
                );
                
            $row = $rowset->current();
            if (!$row) {
                return array();
            }
            return $row;
        } else {
            throw new \Exception("Failed to save data.");
        }
    }
    
    /**
     * Get Settins Information
     */
    public function getData() 
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from(array('s' => $this->table));
               
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        $resultSet = new ResultSet;
        $resultSet->initialize($result);
         return $resultSet;
    }
    
    public function getCacheData($code  = null) 
    {
        $configaration = array();
        $cache = new Cache();
        $result = $cache->getItem('configaration');
        if ($code || !$result) {
            
            $resultSet = $this->getData(0, $code);
            foreach ($resultSet as $config) {
               $configaration[$config->code] = $config->value;
            }
            if (!$code)
                $cache->setItem('configaration', serialize($configaration));
                
        } else {
            $configaration = unserialize($result);
        }
        
        return $configaration;   
    }
    
}

