<?php
namespace Admin\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Sql,
    Zend\Db\Sql\Expression;

class Users extends AbstractTableGateway {

    protected $table = 'users';
    
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

    public function deleteData($id = 0) 
    {

        return $this->delete(array(
            'id' => $id
        ));
        
    }
    
    public function getData($userid = 0, $user_status = 0) 
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from(array('u' => $this->table));
              
        if ($userid)
            $select->where(array('u.id' => $userid));
            
        if ($user_status) {
        	$user_status = $user_status==3 ? 0 : $user_status;
        	$select->where(array('u.is_active' => $user_status));
        }
            
		$select->order('u.id DESC');
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
    
    // Get total users
    public function getTotalUsers($is_newest = 0) 
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->table)
               ->columns(
                    array(
                        'total_users' => new Expression('count(id)')
                    )
               );
                    
        if ($is_newest)
            $select->where("DATE_FORMAT(created_date,'%Y-%m') = DATE_FORMAT(NOW(),'%Y-%m')");
                  
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        $resultSet = new ResultSet;
        $resultSet->initialize($result);
        
        return $resultSet;
    }
    
    
    
    public function getNewestUser() 
    {
        // get last 3 newest user
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->table)
              ->order('user_id DESC')
              ->limit(3,0);
              //echo $selectString = $sql->getSqlStringForSqlObject($select); exit;  
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        $resultSet = new ResultSet;
        $resultSet->initialize($result);
        return $resultSet;
        
    }
    
    public function getUserWithoutLatLang()
    {
		$sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from(array('u' => $this->table))
        		->join(
                    array('a' => 'address'), 
                    new Expression('a.user_id=u.id AND (a.lat=0 OR a.lng=0)'),
                    array('*'),
                    $select::JOIN_INNER
          		)
				->join(
                    array('c' => 'countries'), 
                    'c.country_id=a.country',
                    array('country_name' => 'country'),
                    $select::JOIN_INNER
          		); 
        $select->order('u.id DESC');
		$statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        $resultSet = new ResultSet;
        $resultSet->initialize($result);
        return $resultSet;//->toArray();
	}
	
	public function getLocatorUser($lat = '', $lng = '', $radious = '')
    {
    	$lat = $this->adapter->getPlatform()->quoteValue($lat); 
    	$lng = $this->adapter->getPlatform()->quoteValue($lng); 
    	$radious = $this->adapter->getPlatform()->quoteValue($radious); 
    	$sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from(array('u' => $this->table))
        		->join(
                    array('a' => 'address'), 
                    new Expression('a.user_id=u.id AND (a.lat!=0 OR a.lng!=0)'),
                    array(
						'lat', 'lng', 'name', 'surname', 'formatted_address',
						'distance' => new Expression("( 3959 * acos( cos( radians(".$lat.") ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(".$lng.") ) + sin( radians(".$lat.") ) * sin( radians( lat ) ) ) )")
					),
                    $select::JOIN_INNER
          		);
				   
        $select->having('distance < '.$radious);
        $select->group('u.id');
        //echo $selectString = $sql->getSqlStringForSqlObject($select); exit; 
		$statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        $resultSet = new ResultSet;
        $resultSet->initialize($result);
        return $resultSet->toArray();

	}
  
}

