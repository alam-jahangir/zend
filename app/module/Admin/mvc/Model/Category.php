<?php
namespace Admin\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Sql;

class Category extends AbstractTableGateway {

    protected $table = 'category';
    
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
                        'id' => $id,
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
            'id' => $id
        ));
        
    }
    
    public function getData($id = 0, $isActive = 0) 
	{
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from(array('c' => $this->table));
               
        if ($id)
            $select->where(array('c.id' => $id));
        
        if ($isActive) 
            $select->where(array('c.is_active' => $isActive));

		$statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        $resultSet = new ResultSet;
        $resultSet->initialize($result);
        if (!$id) {
            $resultSet->buffer();
            $resultSet->next();
        }
        return $resultSet;
    }
    
    
}

