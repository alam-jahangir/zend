<?php
namespace Admin\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Sql;

class DealerComment extends AbstractTableGateway {

    protected $table = 'dealer_upload_comment';
    
    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
        $this->initialize();
    }
    

    public function setData($data = array()) {
		if ($this->getRowData($data['upload_id'], $data['dealer_id'])) {
			return 0;
		} else {
			if ($this->insert($data)) {
		        return $this->getLastInsertValue();
		    }
		}
	    return 0;
	}

    public function deleteData($id = 0) {
		return $this->delete(array(
            'id' => $id
        ));
        
    }
    
    public function getRowData($uploadId = 0) {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->table)
               ->columns(
                    array(
                        'total_comment' => new \Zend\Db\Sql\Expression('COUNT(id)'),
                        'rating' => new \Zend\Db\Sql\Expression('AVG(rating)')
                    )
               );
        $select->where(array('upload_id' => $uploadId));  
        $statement = $sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $row = $rowset->current();
        if (!$row) {
            return array();
        }
        return $row;
        
    }
    
}

