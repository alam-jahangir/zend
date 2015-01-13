<?php
namespace Admin\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Sql;

class DealerUpload extends AbstractTableGateway {

    protected $table = 'dealer_upload';
    
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
    
    public function getUploadData($id = 0) 
	{
		$rowset = $this->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            return array();
        }
        return $row;
	}
	
	public function setPendingStatusForRunningOut() 
	{
		$uquery = 'UPDATE '.$this->table.'  AS d SET d.is_active=0 WHERE DATE_ADD( d.renew_date, INTERVAL 30 DAY )<=now( )';
		$results = $this->adapter->query($uquery);
		return $results;
	}
	
    public function getData($id = 0, $isActive = 0) 
	{
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from(array('d' => $this->table))
        		->join(
                    array('u' => 'users'), 
                    'd.user_id=u.id',
                    array('mobile_no')
          		)
        		->join(
                    array('u_a' => 'address'), 
                    new \Zend\Db\Sql\Expression('d.user_id=u_a.user_id AND u_a.address_type=2')
          		);
        if ($id) {
          	$select->join(
                    array('dcp' => 'dealer_cart_price'), 
                    new \Zend\Db\Sql\Expression('dcp.upload_id=d.id AND dcp.last_update=1'),
                    array(
						'upload_price', 
						'cart_price' => new \Zend\Db\Sql\Expression(
										'(SELECT SUM(cart_price) AS cart_price FROM dealer_cart_price WHERE upload_id='.$id.' AND cart_status=0)'
									)
					)
          		);
        }
        $select->join(
                    array('dua' => 'dealer_upload_attribute'), 
                    'dua.upload_id=d.id',
                    array(),
                    $select::JOIN_LEFT 
          		)
          		->join(
                    array('ao' => 'attribute_option'), 
                    'ao.attribute_id=dua.attribute_id AND dua.attribute_option_index=ao.option_index',
                    array('item_name' => new \Zend\Db\Sql\Expression('GROUP_CONCAT(ao.option_name)')),
                    $select::JOIN_LEFT 
          		); 
               
        if ($id)
            $select->where(array('d.id' => $id));
        
        if ($isActive) 
            $select->where(array('d.is_active' => $isActive));
            
		$select->group('d.id');
		
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
    
    public function getDataById($id = 0)
    {
		$sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from(array('d' => $this->table))
        		->join(
                    array('u' => 'users'), 
                    'd.user_id=u.id',
                    array('mobile_no')
          		)
        		->join(
                    array('u_a' => 'address'), 
                    new \Zend\Db\Sql\Expression('d.user_id=u_a.user_id AND u_a.address_type=2')
          		);
        if ($id) {
          	$select->join(
                    array('dcp' => 'dealer_cart_price'), 
                    new \Zend\Db\Sql\Expression('dcp.upload_id=d.id AND dcp.last_update=1'),
                    array(
						'upload_price', 
						'cart_price' => new \Zend\Db\Sql\Expression(
										'(SELECT SUM(cart_price) AS cart_price FROM dealer_cart_price WHERE upload_id='.$id.' AND cart_status=0)'
									)
					)
          		);
        }
        $select->join(
                    array('dua' => 'dealer_upload_attribute'), 
                    'dua.upload_id=d.id',
                    array(),
                    $select::JOIN_LEFT 
          		)
          		->join(
                    array('ao' => 'attribute_option'), 
                    'ao.attribute_id=dua.attribute_id AND dua.attribute_option_index=ao.option_index',
                    array('item_name' => new \Zend\Db\Sql\Expression('GROUP_CONCAT(ao.option_name)')),
                    $select::JOIN_LEFT 
          		); 
               
        if ($id)
            $select->where(array('d.id' => $id));
        
        if ($isActive) 
            $select->where(array('d.is_active' => $isActive));
            
		$select->group('d.id');
		
		$statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        $resultSet = new ResultSet;
        $resultSet->initialize($result);
        return $resultSet;
	}
    
    
    /**
    * Get Dealer Upload Attribute
    * @param $groupId	int
    */
    public function getDealerUploadAttribute($uploadId = 0)
    {
    	if ($uploadId) {
			$sql = 'SELECT * FROM dealer_upload_attribute WHERE upload_id ='.intval($uploadId);    
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
     * Set Dealer Upload Attribute
     * @param   $data   array
     * @return  boolean
     */
    public function setDealerUploadAttribute($data = array()) 
    {
        $query = 'INSERT INTO dealer_upload_attribute(upload_id, attribute_id, attribute_option_index, attribute_option_value, attribute_option_subitem) VALUES(:upload_id, :attribute_id, :attribute_option_index, :attribute_option_value, :attribute_option_subitem)';
        $results = $this->adapter->query($query, $data);
        return true;
    }
    
    /**
    * Delete Dealer Upload Attribute
    * @param int	$uploadId
    * @return	boolean
    */
    public function deleteDealerUploadAttribute($uploadId = 0) 
    {        
    	if ($uploadId) {
	        $query = 'DELETE FROM dealer_upload_attribute WHERE upload_id = :upload_id';                
	        $this->adapter->query($query, array('upload_id' => $uploadId));
	        return true;
		}
		return false;       
    }
    
    /**
     * Set Upload Gallery
     * @param   $data   array
     * @return  boolean
     */
    public function setUploadGallery($isNew = 1, $id = 0, $data = array()) 
    {
    	if (!$isNew && $id) {
    		$existData = $this->getUploadGallery($data['upload_id'], $id);
    		if (isset($existData[0]) && $existData[0]['id']) {
				$query = 'UPDATE dealer_upload_gallery SET file_name=:file_name, is_delete=0
                  	  WHERE id='.$id.' AND upload_id=:upload_id';
			} else {
				$query = 'INSERT INTO dealer_upload_gallery(upload_id, file_name, is_delete)
                  	  	  VALUES(:upload_id, :file_name, 0)';
	        }
		} else {
        	$query = 'INSERT INTO dealer_upload_gallery(upload_id, file_name, is_delete)
                  	  VALUES(:upload_id, :file_name, 0)';
        }
        
        $results = $this->adapter->query($query, $data);
        return true;
    }
    
    /**
    * Update Delete Flag For Gallery by Upload Id
    * @param int	$uploadId
    * @return	boolean
    */
    public function setDelFlagAllGallery($uploadId = 0) 
    {        
    	if ($uploadId) {
	        $query = 'UPDATE dealer_upload_gallery SET is_delete=1 WHERE upload_id = :upload_id';                
	        $this->adapter->query($query, array('upload_id' => $uploadId));
	        return true;
		}
		return false;       
    }
    
    /**
    * Delete All Flag Gallery by Upload Id
    * @param int	$uploadId
    * @return	boolean
    */
    public function deleteAllFlagGallery() 
    {        
        $query = 'DELETE FROM dealer_upload_gallery WHERE is_delete=1';                
        $this->adapter->query($query);
        return true;
    }
    
    /**
    * Delete Upload Gallery by Upload Id
    * @param int	$uploadId
    * @return	boolean
    */
    public function deleteAllGallery($uploadId = 0) 
    {        
    	if ($uploadId) {
	        $query = 'DELETE FROM dealer_upload_gallery WHERE upload_id = :upload_id';                
	        $this->adapter->query($query, array('upload_id' => $uploadId));
	        return true;
		}
		return false;       
    }
    
    /**
    * Delete Upload Gallery by Gallery Id
    * @param int	$uploadId
    * @return	boolean
    */
    public function deleteGallery($id = 0) 
    {        
    	if ($uploadId) {
	        $query = 'DELETE FROM dealer_upload_gallery WHERE id = :id';                
	        $this->adapter->query($query, array('id' => $id));
	        return true;
		}
		return false;       
    }
    
    /**
    * Get Upload Gallery
    * @param $groupId	int
    */
    public function getUploadGallery($uploadId = 0, $id = 0)
    {
    	if ($uploadId) {
			$sql = 'SELECT * FROM dealer_upload_gallery WHERE upload_id ='.intval($uploadId);  
			if ($id) {
				$sql .= ' AND id='.intval($id);	
			} else {  
				$sql .= ' AND is_delete=0';
			}
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
    * Get Dealer Cart Price
    * @param $groupId	int
    */
    public function getDealerCartPrice($uploadId = 0)
    {
    	if ($uploadId) {
			$sql = 'SELECT * FROM dealer_cart_price WHERE upload_id ='.intval($uploadId);    
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
    * Get Last Update Dealer Cart Price
    * @param $groupId	int
    */
    public function getLastUpdateCartPrice($uploadId = 0)
    {
    	if ($uploadId) {
			$sql = 'SELECT * FROM dealer_cart_price WHERE last_update=1 AND upload_id ='.intval($uploadId);    
	        $statement = $this->adapter->query($sql);
	        $rowset = $statement->execute();
	        $row = $rowset->current();
	        if (!$row) {
	            return array();
	        }
	        return $row;
	    } else {
			return array();
		}
	}
	
	/**
     * Set Dealer Cart Price
     * @param   $data   array
     * @return  boolean
     */
    public function setDealerCartPrice($data = array(), $isRenew = 0) 
    {
    	$uquery = 'UPDATE dealer_cart_price SET last_update=0 WHERE upload_id=:upload_id';
		$results = $this->adapter->query($uquery, array('upload_id' => $data['upload_id']));
		
		if ($isRenew) {
			$this->setData($data['upload_id'], array('renew_date' => date('Y-m-d H:i:s')));
			$query = 'INSERT INTO dealer_cart_price(upload_id, upload_price, cart_price, cart_status, last_update, updated_date,is_renew)
                  VALUES(:upload_id, :upload_price, :cart_price, :cart_status, 1, :updated_date, 1)';
		} else {
			$query = 'INSERT INTO dealer_cart_price(upload_id, upload_price, cart_price, cart_status, last_update, updated_date)
                  VALUES(:upload_id, :upload_price, :cart_price, :cart_status, 1, :updated_date)';
		}	            
        
        $results = $this->adapter->query($query, $data);
        
        return true;
    }
    
    /**
     * Update Dealer Cart Price after Payment
     * @param   $data   array
     * @return  boolean
     */
    public function updateDealerCartPriceStatus($cartid = 0, $userId = 0, $transactionId = '', $status = 0) 
    {
        $query = 'UPDATE dealer_cart_price AS dcp
				  JOIN '.$this->table.' AS d ON d.id = dcp.upload_id AND d.user_id =:user_id
				  SET cart_status=:cart_status, transaction_id=:transaction_id 
				  WHERE cart_id<=:cart_id AND cart_status=0';
        $results = $this->adapter->query(
					$query, 
					array(
						'cart_id' => $cartid, 
						'cart_status' => $status, 
						'user_id' => $userId,
						'transaction_id' => $transactionId
					)
				);
        return true;
    }
    
    /**
    * Delete Dealer Cart Price
    * @param int	$uploadId
    * @return	boolean
    */
    public function deletedealerCartPrice($uploadId = 0) 
    {        
    	if ($uploadId) {
	        $query = 'DELETE FROM dealer_cart_price WHERE upload_id = :upload_id';                
	        $this->adapter->query($query, array('upload_id' => $uploadId));
	        return true;
		}
		return false;       
    }
    
    
    public function getDataByUserId($userId = 0, $sort = '', $cond = '')
    {
		$sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from(array('d' => $this->table))
        		->join(
                    array('dug' => 'dealer_upload_gallery'), 
                    new \Zend\Db\Sql\Expression('dug.upload_id=d.id AND dug.is_delete=0'),
                    array('image' => 'file_name'),
                    $select::JOIN_LEFT 
          		)
          		->join(
                    array('duc' => 'dealer_upload_comment'), 
                    'duc.upload_id=d.id',
                    array('rating' => new \Zend\Db\Sql\Expression('AVG(duc.rating)'), 'total_comment' => new \Zend\Db\Sql\Expression('COUNT(duc.id)')),
                    $select::JOIN_LEFT 
          		)
				->join(
                    array('dua' => 'dealer_upload_attribute'), 
                    'dua.upload_id=d.id',
                    array(),
                    $select::JOIN_LEFT 
          		)
          		->join(
                    array('ao' => 'attribute_option'), 
                    'ao.attribute_id=dua.attribute_id AND dua.attribute_option_index=ao.option_index',
                    array('name' => new \Zend\Db\Sql\Expression("GROUP_CONCAT(DISTINCT ao.option_name SEPARATOR ', ')")),
                    $select::JOIN_LEFT 
          		); 
        $select->where(array('d.user_id' => $userId));
        if ($cond)
        	$select->where($cond);
        	
        if ($sort)
        	$select->order($sort);
        else
        	$select->order('d.id DESC');
        	
		$select->group('d.id');
		
		//$selectString = $sql->getSqlStringForSqlObject($select);  
        //echo $selectString; exit;
        
		$statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        $resultSet = new ResultSet;
        $resultSet->initialize($result);
        $resultSet->buffer();
        $resultSet->next();
        return $resultSet;//->toArray();
		
	}
	
	
	public function getAttributeInfo($id = 0)
	{
		$sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from(array('dua' => 'dealer_upload_attribute'))
        		->join(
                    array('a' => 'attribute'), 
                    'a.id=dua.attribute_id',
                    array('attr_name' => 'name'),
                    $select::JOIN_INNER //$select::JOIN_LEFT 
          		)
				->join(
                    array('ao' => 'attribute_option'), 
                    'ao.attribute_id=dua.attribute_id AND dua.attribute_option_index=ao.option_index',
                    array('option_name' => 'option_name'),
                    $select::JOIN_LEFT 
          		); 
        $select->where(array('dua.upload_id' => $id));
        $select->group(array('dua.attribute_id'));
 		$statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        $resultSet = new ResultSet;
        $resultSet->initialize($result);
        return $resultSet;
	}
	
	public function searchResult($term = '')
	{
		$term = $this->adapter->getPlatform()->quoteValue('%'.$term.'%'); 
		
		$sql = "SELECT `d`.*, `dug`.`file_name` AS `image`, GROUP_CONCAT(DISTINCT ao.option_name SEPARATOR ', ') AS `name` 
		FROM `dealer_upload` AS `d` 
		INNER JOIN ( 
			(
				SELECT upload_id AS id FROM `dealer_upload_attribute` AS dua
				INNER JOIN (
					SELECT attribute_id,option_index FROM `attribute_option` WHERE LOWER(`option_name`) LIKE ".$term." 
				) AS temp ON temp.attribute_id=dua.attribute_id AND temp.option_index=dua.attribute_option_index
			)
			
			UNION 
			( 
				SELECT upload_id AS id FROM `dealer_upload_attribute` WHERE LOWER(`attribute_option_value`) LIKE ".$term." 
			)
			UNION
			(
				SELECT d.id AS id FROM `dealer_upload` AS `d` 
				INNER JOIN  `category` as c on c.id=d.category_id AND LOWER(`c`.`name`) LIKE ".$term." 
			)
			UNION 
			( 
				SELECT id AS id FROM `dealer_upload` WHERE LOWER(`description`) LIKE ".$term." 
			)
		) as attr on attr.id=d.id
		LEFT JOIN `dealer_upload_gallery` AS `dug` ON `dug`.`upload_id`=`d`.`id` AND `dug`.is_delete =0 
		LEFT JOIN `dealer_upload_attribute` AS `dua` ON `dua`.`upload_id`=`d`.`id` 
		LEFT JOIN `attribute_option` AS `ao` ON `ao`.`attribute_id`=`dua`.`attribute_id` AND `dua`.`attribute_option_index`=`ao`.`option_index`
		WHERE d.is_active=1
		GROUP BY `d`.`id` 
		ORDER BY `d`.`id` DESC";
		
		/*
		$sql = "SELECT `d`.*, `dug`.`file_name` AS `image`, GROUP_CONCAT(DISTINCT ao.option_name SEPARATOR ', ') AS `name` 
		FROM `dealer_upload` AS `d` 
		INNER JOIN  `category` as c on c.id=d.category_id
		LEFT JOIN `dealer_upload_gallery` AS `dug` ON `dug`.`upload_id`=`d`.`id` AND `dug`.`is_delete`=0
		LEFT JOIN `dealer_upload_attribute` AS `dua` ON `dua`.`upload_id`=`d`.`id` 
		LEFT JOIN `attribute_option` AS `ao` ON `ao`.`attribute_id`=`dua`.`attribute_id` AND `dua`.`attribute_option_index`=`ao`.`option_index`
		WHERE d.is_active=1 AND LOWER(`c`.`name`) LIKE ".$term." 
		GROUP BY `d`.`id` 
		ORDER BY `d`.`id` DESC"; 
		*/   
        $statement = $this->adapter->query($sql);
        $result = $statement->execute();
        $resultSet = new ResultSet;
        $resultSet->initialize($result);
        $resultSet->buffer();
        $resultSet->next();
        return $resultSet;
	}
	
	
	public function getCartSummary($dealerId = 0)
	{
		$dealerId = intval($dealerId);
		$sql = 'SELECT COUNT(upload_id) AS total_item, SUM(cart_price) AS cart_price FROM `dealer_upload`  AS du
				INNER JOIN `dealer_cart_price` AS dcp ON dcp.upload_id=du.id
				WHERE du.user_id ='.$dealerId.' AND dcp.cart_status=0';
		
		$statement = $this->adapter->query($sql);
        $rowset = $statement->execute();
        $row = $rowset->current();
        if (!$row) {
            return array();
        }
        return $row;
	}
    
    public function getCartInformation($dealerId = 0)
	{
		$dealerId = intval($dealerId);
		$sql = "SELECT `du` . * , `temp`.`cart_price` , `dcp`.`upload_price` AS `upload_price` , `dcp`.`cart_id` AS `cart_id` , 
					   `dug`.`file_name` AS `image` , GROUP_CONCAT( DISTINCT ao.option_name SEPARATOR ', ' ) AS `name`
				FROM `dealer_upload` AS `du`
				INNER JOIN (
					SELECT `du`.`id` , SUM( dcp.cart_price ) AS `cart_price`
					FROM `dealer_upload` AS `du`
					INNER JOIN `dealer_cart_price` AS `dcp` ON `dcp`.`upload_id` = `du`.`id`
					AND `dcp`.`cart_status` = '0'
					GROUP BY `du`.`id`
				) AS temp ON temp.id = du.id
				INNER JOIN `dealer_cart_price` AS `dcp` ON `dcp`.`upload_id` = `du`.`id`
				AND `dcp`.`last_update` = 1 AND `dcp`.`cart_status` = '0'
				LEFT JOIN `dealer_upload_gallery` AS `dug` ON dug.upload_id = du.id
				AND dug.is_delete =0
				LEFT JOIN `dealer_upload_attribute` AS `dua` ON `dua`.`upload_id` = `du`.`id`
				LEFT JOIN `attribute_option` AS `ao` ON `ao`.`attribute_id` = `dua`.`attribute_id`
				AND `dua`.`attribute_option_index` = `ao`.`option_index`
				WHERE `du`.`user_id` = ".$dealerId."
				GROUP BY `du`.`id`";
        
        $statement = $this->adapter->query($sql);
        $result = $statement->execute();
        $resultSet = new ResultSet;
        $resultSet->initialize($result);
        return $resultSet;
	}
	
	public function getFavouriteList($dealerId = 0)
    {
		$sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from(array('d' => $this->table))
        		->join(
                    array('dw' => 'dealer_wishlist'), 
                    'dw.upload_id=d.id',
                    array(),
                    $select::JOIN_INNER
          		)
        		->join(
                    array('dug' => 'dealer_upload_gallery'), 
                    new \Zend\Db\Sql\Expression('dug.upload_id=d.id AND dug.is_delete=0'),
                    array('image' => 'file_name'),
                    $select::JOIN_LEFT 
          		)
				->join(
                    array('dua' => 'dealer_upload_attribute'), 
                    'dua.upload_id=d.id',
                    array(),
                    $select::JOIN_LEFT 
          		)
          		->join(
                    array('ao' => 'attribute_option'), 
                    'ao.attribute_id=dua.attribute_id AND dua.attribute_option_index=ao.option_index',
                    array('name' => new \Zend\Db\Sql\Expression("GROUP_CONCAT(DISTINCT ao.option_name SEPARATOR ', ')")),
                    $select::JOIN_LEFT 
          		); 
        $select->where(array('dw.dealer_id' => $dealerId));
       	$select->order('d.id DESC');
		$select->group('d.id');
		
		//$selectString = $sql->getSqlStringForSqlObject($select);  
        //echo $selectString; exit;
        
		$statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        $resultSet = new ResultSet;
        $resultSet->initialize($result);
        $resultSet->buffer();
        $resultSet->next();
        return $resultSet;//->toArray();
	}
    
}

