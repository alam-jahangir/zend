<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Sql;
use Application\Model\ConfigCache as Cache;

class Countries extends AbstractTableGateway {

    protected $table = 'countries';
    
    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
        $this->initialize();
    }
    
    public function getCountries() {
        
        $cache = new Cache();
        
        if (!$countryList = $cache->getItem('countries')) {
        
            $sql = new Sql($this->adapter);
            $select = $sql->select();
            $select->from($this->table);
            $statement = $sql->prepareStatementForSqlObject($select);
            $result = $statement->execute();
            
            $resultSet = new ResultSet;
            $resultSet->initialize($result);
            
            $countryList = $resultSet->toArray();
            $cache->setItem('countries', serialize($countryList));
            
            return $countryList;
        } else {
            return unserialize($countryList);
        }                
    }  
    
    public function getCountriesByColumn($columnName = 'ccode', $columnValue = '') {
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
               
    }   
    
}

