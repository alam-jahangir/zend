<?php
namespace User\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Sql;

class Address extends AbstractTableGateway {

    protected $table = 'address';
    
    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
        $this->initialize();
    }    

    public function setAddressData($userid = 0, $address_id = 0, $data = array()) {

        if ($address_id == 0) {
            if ($this->insert($data)) {
                return $this->getLastInsertValue();
            }
        } elseif ($address_id) {
            $this->update(
                    $data,
                    array(
                        'address_id' => $address_id,
                        'user_id' => $userid,
                    )
            );
            return $address_id;
        } else {
            throw new \Exception("Failed to save data.");
        }
    }

    public function deleteAddressData($id = 0, $settingsid = 0) {

        return $this->delete(array(
            'address_id' => $id
        ));
        
    }
    
    public function getAddressData($id = 0) {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from(array('a_u' => $this->table));
              
        if ($userid)
            $select->where(array('a_u.address_id' => $id));

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        $resultSet = new ResultSet;
        $resultSet->initialize($result);
        
        return $resultSet;
    }
    
}

