<?php
namespace Admin\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Sql;
    //Zend\Db\Sql\Expression;

class AdminUsers extends AbstractTableGateway {

    protected $table = 'admin_users';
    
    public function __construct(Adapter $adapter) 
    {
        $this->adapter = $adapter;
        $this->initialize();
    }
    

    public function setData($id = 0, $data = array()) 
    {
        if ($id == 0) {
            if ($this->insert($data)) {
                return $this->getLastInsertValue();
            }
            return 0;
        } elseif ($id) {
            $this->update(
                    $data,
                    array(
                        'user_id' => $id,
                    )
            );
            return  $id;
        } else {
            throw new \Exception("Failed to save data.");
        }
    }

    public function deleteData($id = 0) 
    {

        return $this->delete(array(
            'user_id' => $id
        ));
        
    }
    
    public function getData($userid = 0, $roleid = 0) 
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from(array('a_u' => $this->table));
              
        if ($userid)
            $select->where(array('a_u.user_id' => $userid));
            
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        $resultSet = new ResultSet;
        $resultSet->initialize($result);
        if (!$userid) {
            $resultSet->buffer();
            $resultSet->next();
        }
        return $resultSet;
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
    
    

}

