<?php
namespace Admin\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Sql;

class AttributeGroup extends AbstractTableGateway {

    protected $table = 'attribute_group';
    
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

    public function deleteData($id = 0, $column = 'id') 
	{

        return $this->delete(array(
            $column => $id
        ));
        
    }
    
    public function getData($id = 0, $isActive = 0) 
	{
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from(array('ag' => $this->table))
               ->join(
                        array('c' => 'category'), 
                        'c.id = ag.category_id',
                        array('c_name' => 'name'),
                        $select::JOIN_LEFT 
              		); 
        if ($id)
            $select->where(array('ag.id' => $id));
        
        if ($isActive) 
            $select->where(array('ag.is_active' => $isActive));
            
		//$select->where(array('c.parent_id IS NULL'));

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
    
    public function getDataByCategoryId($categoryId = 0, $isActive = 0)
    {
		if ($categoryId) {
			$sql = new Sql($this->adapter);
	        $select = $sql->select();
	        $select->from(array('ag' => $this->table))
	               ->join(
	                        array('ago' => 'attribute_group_option'), 
	                        'ago.group_id = ag.id',
	                        array('option_name', 'option_index'),
	                        $select::JOIN_LEFT 
	              		); 
	        if ($categoryId)
	            $select->where(array('ag.category_id' => $categoryId));
	        
	        if ($isActive) 
	            $select->where(array('ag.is_active' => $isActive));
	
			$statement = $sql->prepareStatementForSqlObject($select);
	        $result = $statement->execute();
	        
	        $resultSet = new ResultSet;
	        $resultSet->initialize($result);
	        return $resultSet->toArray();
		} else {
			return array();
		}
	}
    
    /**
    * Get Attribute Group Option Information
    * @param $groupId	int
    */
    public function getAttributeGroupOption($groupId = 0)
    {
    	if ($groupId) {
			$sql = 'SELECT option_name,option_index FROM attribute_group_option
	                WHERE group_id ='.intval($groupId);    
	        $statement = $this->adapter->query($sql);
	        $result = $statement->execute();
	        $resultSet = new ResultSet;
	        $resultSet->initialize($result);
	        return $resultSet->toArray();
	    } else {
			return array();
		}
	}
	
	/**
     * Set Attribute Group Option Information
     * @param   $data   array
     * @return  boolean
     */
    public function setAttributeGroupOption($data = array()) 
    {
        $query = 'INSERT INTO attribute_group_option(group_id, option_name, option_index)
                  VALUES(:group_id, :option_name, :option_index)';
        $results = $this->adapter->query($query, $data);
        return true;
    }
    
    public function deleteAttributeGroupOption($groupId = 0) 
    {        
    	if ($groupId) {
	        $query = 'DELETE FROM attribute_group_option WHERE group_id = :group_id';                
	        $this->adapter->query($query, array('group_id' => $groupId)); 
	        return true;
		}       
		return false;
    }
    
    
   
    
}

