<?php
namespace Account\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Sql;

class Transactions extends AbstractTableGateway {

    protected $table = 'users_transaction';
    
    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
        $this->initialize();
    } 
        
    public function getData($transaction_id = 0) {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->table);
        
        if ($transaction_id)
            $select->where(array('transaction_id' => $transaction_id));

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        $resultSet = new ResultSet;
        $resultSet->initialize($result);
        
        return $resultSet;
    }
        
    public function getTransactionList($userid = 0) {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->table);
        $select->where(array('user_id' => $userid));

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        $resultSet = new ResultSet;
        $resultSet->initialize($result);
        
        return $resultSet;
    }
    
}

