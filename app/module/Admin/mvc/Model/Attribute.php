<?php
namespace Admin\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Sql;

class Attribute extends AbstractTableGateway {

    protected $table = 'attribute';
    
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
        $select->from(array('c' => $this->table))
               ->join(
                    array('ag' => 'attribute_group'), 
                    'c.group_id = ag.id',
                    array('group_name' => 'name'),
                    $select::JOIN_LEFT 
          		)
          		->join(
                    array('ago' => 'attribute_group_option'), 
                    'ago.option_index = c.option_id AND ago.group_id=c.group_id',
                    array('group_option_name' => 'option_name'),
                    $select::JOIN_LEFT 
          		);
          		
		if ($id)
            $select->where(array('c.id' => $id));
        
        if ($isActive) 
            $select->where(array('c.is_active' => $isActive));
            
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
    
    /**
    * Get Attribute Group Option Information
    * @param $groupId	int
    */
    public function getOptionData($attributeId = 0)
    {
    	if ($attributeId) {
			$sql = 'SELECT option_name,option_index,subitem FROM attribute_option
	                WHERE attribute_id ='.intval($attributeId);    
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
    public function setAttributeOption($data = array()) 
    {
        $query = 'INSERT INTO attribute_option(attribute_id, option_name, option_index, subitem)
                  VALUES(:attribute_id, :option_name, :option_index, :subitem)';
        $results = $this->adapter->query($query, $data);
        return true;
    }
    
    public function deleteAttributeOption($attributeId = 0) 
    {        
    	if ($attributeId) {
	        $query = 'DELETE FROM attribute_option WHERE attribute_id = :attribute_id';                
	        $this->adapter->query($query, array('attribute_id' => $attributeId));
	        return true;
		}
		return false;       
    }
    
    public function getDataByGroupId($groupId = 0, $optionId  = 0, $isActive = 0)
    {
		if ($groupId && $optionId) {
			$sql = new Sql($this->adapter);
	        $select = $sql->select();
	        $select->from(array('a' => $this->table))
	               ->join(
	                        array('ao' => 'attribute_option'), 
	                        'ao.attribute_id = a.id',
	                        array('option_name', 'option_index', 'subitem'),
	                        $select::JOIN_LEFT 
	              		); 
	        $select->where(array('a.group_id' => $groupId));
	        $select->where(array('a.option_id' => $optionId));
	        
	        if ($isActive) 
	            $select->where(array('a.is_active' => $isActive));
			
			$select->order('a.id ASC');
			
			$statement = $sql->prepareStatementForSqlObject($select);
	        $result = $statement->execute();
	        
	        $resultSet = new ResultSet;
	        $resultSet->initialize($result);
	        return $resultSet->toArray();
		} else {
			return array();
		}
	}
    
    
   
    
}

