<?php
namespace User\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Sql,
    Zend\Db\Sql\Expression;

class User extends AbstractTableGateway {

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
                        'user_id' => $id,
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
            'user_id' => $id
        ));
        
    }
    
    public function getData($userid = 0, $user_status = 0) 
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from(array('u' => $this->table))
               ->join(
                        array('ad' => 'address'), 
                        new Expression('u.user_id = ad.user_id AND ad.address_type=1'),
                        array(
                            'address_id', 'address_type', 'name', 'phone_number', 
                            'street_address', 'options', 'town', 'country_id', 'postcode'
                        ),
                        $select::JOIN_LEFT
              )->join(
                        array('c' => 'countries'), 
                        'ad.country_id = c.country_id',
                        array('ccode', 'country'),
                        $select::JOIN_LEFT
              )->join(
                        array('u1' => $this->table),
                        'u1.invite_user_id=u.user_id',
                        array('invite_username' => 'username'),
                        $select::JOIN_LEFT
              );
              
        if ($userid)
            $select->where(array('u.user_id' => $userid));
        if ($user_status)
            $select->where(array('u.is_active' => $user_status));

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
                        'total_users' => new Expression('count(user_id)')
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
    
    // Get user details with user total deposit and balance
    public function getUserDepositBalance($userid = 0) 
    {
        $sql = " SELECT `u`.*, SUM(u_t.amount) AS `total_deposits`, temp.total_balance 
                 FROM `users` AS `u`
                 LEFT JOIN `users_transaction` AS `u_t` ON `u_t`.`user_id` = `u`.`user_id`
                 LEFT JOIN (
                    SELECT user_id, SUM(u_b.price_per_bid * u_b.bid_left) AS `total_balance` FROM `users_balance` AS `u_b` GROUP BY user_id
                 ) AS temp on temp.user_id=u.user_id
                
                 GROUP BY `u`.`user_id`";
                
        $statement = $this->adapter->query($sql);
        
        $result = $statement->execute();
        
        $resultSet = new ResultSet;
        $resultSet->initialize($result);
        if (!$userid) {
            $resultSet->buffer();
            $resultSet->next();
        }
        return $resultSet;     
    }
    
    
    public function getSubscribeUser($timePeriod = 'weekly') 
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from(array('u' => $this->table));
        $select->where(array('u.is_subscribe' => 1));
        
        if ($timePeriod == 'one-month'){
            $select->where("u.last_login <= (DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND u.last_login > (DATE_SUB(CURDATE(), INTERVAL 3 MONTH))");
        }
        if ($timePeriod == 'three-month'){
            $select->where("u.last_login <= (DATE_SUB(CURDATE(), INTERVAL 3 MONTH)) AND u.last_login > (DATE_SUB(CURDATE(), INTERVAL 6 MONTH))");
        }
        if ($timePeriod == 'six-month'){
            $select->where("u.last_login <= (DATE_SUB(CURDATE(), INTERVAL 6 MONTH))");
        }

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        $resultSet = new ResultSet;
        $resultSet->initialize($result);

        return $resultSet;
    }
    

}

