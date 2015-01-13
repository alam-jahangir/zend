<?php
namespace Admin\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Sql;

class Address extends AbstractTableGateway {

    protected $table = 'address';
    
    public function __construct(Adapter $adapter) 
	{
        $this->adapter = $adapter;
        $this->initialize();
    }    
	
	public function setData($id = 0, $data = array()) 
    {

        if ($id == 0) {
            if ($this->insert($data)) {
                $id = $this->getLastInsertValue();
            }
            return 0;
        } elseif ($id) {
        	
			if (isset($data['address_id'])) {
				unset($data['address_id']);
			}
			
			$this->update(
                    $data,
                    array(
                        'address_id' => $id,
                        'user_id' => $data['user_id']
                    )
            );
            return  $id;
        } else {
            throw new \Exception("Failed to save data.");
        }
    }
    
    public function getData($userid = 0) 
	{
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from(array('a' => $this->table))
        	   ->join(
                        array('a1' => 'address'), 
                        new \Zend\Db\Sql\Expression('a1.user_id = a.user_id AND a1.address_type=2'),
                        array(
							'business_id' => 'address_id', 'business_name' => 'name',
							'business_surname' => 'surname', 'business_street' => 'street',
							'business_city' => 'city', 'business_state' => 'state',
							'business_zipcode' => 'zipcode', 'business_phone' => 'phone',
							'business_email' => 'email', 'business_country' => 'country'
						),
                        $select::JOIN_INNER
                );
				             
        $select->where(array('a.user_id' => $userid));            
        $select->order('address_type ASC');
		$statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        $resultSet = new ResultSet;
        $resultSet->initialize($result);
        
        return $resultSet;
    }
    
}

