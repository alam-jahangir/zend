<?php
namespace Account\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Sql;

class Packages extends AbstractTableGateway {

    protected $table = 'bid_packages';
    
    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
        $this->initialize();
    } 
        
    public function getData($package_id = 0, $package_type = 1) {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->table);
        
        if ($package_id) 
        {
            $select->where(array('package_id' => $package_id, 'package_type' => $package_type));
        } else {
            $select->where(array('package_type' => $package_type));    
        }            

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        $resultSet = new ResultSet;
        $resultSet->initialize($result);
        
        return $resultSet;
    }
        
    public function getPackagesList() {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->table);
        $select->where(array('package_type' => 1));

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        $resultSet = new ResultSet;
        $resultSet->initialize($result);
        
        return $resultSet;
    }
    
}

