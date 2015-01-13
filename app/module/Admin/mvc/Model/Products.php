<?php
namespace Admin\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Sql,
    Zend\Db\Sql\Expression;

class Products extends AbstractTableGateway {

    /**
     * @var string
     * Database Table Name for this Model
     */
    protected $table = 'products';
    
    /**
     * @param   $adapter   Zend\Db\Adapter\Adapter
     */
    public function __construct(Adapter $adapter) 
    {
        $this->adapter = $adapter;
        $this->initialize();
    }
    
    /**
     * Save product
     * If $productid is zero, a product will be create as new product
     * Otherwise Update information of a product 
     * @param   $productid      int
     * @param   $data           array
     * @param   $productPrice   array
     * @return  $productid      int
     */
    public function setData($productid = 0, $data = array(), $productPrice = array(), $isStatusChange = 0) 
    {
        
        if ($productid == 0) {
            if ($this->insert($data)) {
                $productid = $this->getLastInsertValue();
                $this->setProductPrice($productid, $productPrice);
                return $productid;
            }
            return 0;
        } elseif ($productid) {
        	$this->update(
                    $data,
                    array(
                        'id' => $productid,
                    )
            );
            
            if (!$isStatusChange)
            	$this->setProductPrice($productid, $productPrice);
            
            return  $productid;
        } else {
            throw new \Exception("Failed to save data.");
        }
    }
    
    
    /**
     * Set price for a product
     * @param   $productid      int
     * @param   $productPrice    array
     * @return  bool
     */
    public function setProductPrice($productid = 0, $productPrice = array()) 
    {
    	$this->deleteProductPrice($productid);
    	
    	if ($productPrice) {
    		$query = 'INSERT IGNORE INTO product_price(product_id,shape_id,group_id,option_id,price) 
					  VALUES (:product_id, :shape_id, :group_id, :option_id, :price)';
	        foreach ($productPrice as $key => $price) {
	        	
	        	if ($price['option_name']) {
	        		
		        	$productOption = new \Admin\Model\ProductOption($this->adapter);
	        		$optionId = $productOption->setData($price['option_name']);
	        		
	        		if ($optionId) {
			            $this->adapter->query(
							$query, 
							array(
								'product_id' => $productid, 
								'shape_id' => $price['shape_id'],
								'group_id' => $price['group_id'],
								'option_id'	=> $optionId,
								'price' => $price['price']
							)
						);
					}
				}
	        }
        }
        return true;
    }
    
    /**
     * Delete Product
     * @param   $productid      int
     * @return  bool
     */
    public function deleteData($productid = 0) 
    {
		
		$this->deleteProductPrice($productid);
		
        return $this->delete(array(
            'id' => $productid
        ));
        
    }
    
    /**
     * Delete Product Price Information
     * @param   $productid      int
     * @return  bool
     */
    public function deleteProductPrice($productid = 0) 
    {

        $sql = 'DELETE product_price WHERE product_id='.$productid;                
        $this->adapter->query($sql); 
        return true;
        
    }
    
    /**
     * Sql For Get Product List/a Specific Product
     * @param   $productid      int
     * @param   $isFeatured     int
     * @param   $isActive       int
     * @param   $categoryid     int
     * @param   $ptype          string
     * @return  $resultSet      Zend\Db\ResultSet\ResultSet
     */
    public function getData($productId = 0, $isActive = 0, $modelId = 0, $brandId = 0, $orderBy = 'created_date DESC', $categoryId = 0) 
    {
        
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from(array('p' => $this->table))
        		->join(
                        array('m' => 'models'), 
                        'm.id = p.model_id',
                        array('model_name' => 'name', 'model_desc' => 'description'),
                        $select::JOIN_LEFT
                )
                ->join(
                        array('c' => 'categories'), 
                        'c.category_id = p.category_id',
                        array('category_name' => 'category_name', 'identifier' => 'identifier'),
                        $select::JOIN_LEFT
                )
				->join(
                        array('b' => 'brands'), 
                        'b.id = p.brand_id',
                        array('brand_name' => 'name', 'brand_desc' => 'description'),
                        $select::JOIN_LEFT
                );
               
        if ($productId)
            $select->where(array('p.id' => $productId));
        
        if ($modelId)
            $select->where(array('p.model_id' => $modelId));
        
		if ($brandId)
            $select->where(array('p.brand_id' => $brandId));
        
        if ($categoryId)
            $select->where(array('p.category_id' => $categoryId));
            
        if ($isActive) 
            $select->where(array('p.status' => $isActive));
        
		$select->order($orderBy);
		
		//$selectString = $sql->getSqlStringForSqlObject($select);  
        //echo $selectString; exit;
		        
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        $resultSet = new ResultSet;
        $resultSet->initialize($result);
        
        if (!$productId || !$modelId) {
            $resultSet->buffer();
            $resultSet->next();
        }
        
        return $resultSet;
        
    }
    
    public function getGroupProduct($groupid = array(), $shapeid = array(), $categoryid = 0, $modelid = 0, $optionName = array())
    {

    		
    	$categoryid = (int)$categoryid;
    	$modelid = (int)$modelid;
    	
    	
                
		$sql = "SELECT p.*,c.category_name,c.identifier,m.name AS model_name,m.description AS model_desc,b.name AS brand_name,b.description AS brand_desc   
				FROM ".$this->table." AS p
				LEFT JOIN `categories` AS c ON c.category_id = p.category_id
				LEFT JOIN `models` AS m ON m.id = p.model_id
				LEFT JOIN `brands` AS b ON b.id = p.brand_id
				WHERE p.id IN (
					SELECT p.product_id FROM `product_price` AS p ";
		
		//$groupsql = '(';
		$groupsql = '';
		foreach ($groupid as $key => $group) { 
			//if ($key != 0)
			//$groupsql .= " AND ";
			//$groupsql .= "(`shape_id`=".((int)$shapeid[$key])." AND `group_id`=".((int)$group).")";
			$aliasName = "pp".($key+1);
			//$groupsql .= " INNER JOIN `product_price` AS ".$aliasName." ON ".$aliasName.".shape_id=".((int)$shapeid[$key])." AND ".$aliasName.".group_id=".((int)$group)." AND ".$aliasName.".option_name LIKE ".$this->adapter->platform->quoteValue(trim($optionName[$key]))." AND ".$aliasName.".product_id=p.product_id";
			
			$groupsql .= " INNER JOIN `product_price` AS ".$aliasName." ON ".$aliasName.".shape_id=".((int)$shapeid[$key])." AND ".$aliasName.".group_id=".((int)$group)." AND ".$aliasName.".option_id=".(intval($optionName[$key]))." AND ".$aliasName.".product_id=p.product_id";
		}	 
		//$groupsql .= ')';
		$groupsql .= ' GROUP BY p.product_id';			 
		$sql .= $groupsql;
		
		/*			
		$sql .= " AND (";			
		foreach($optionName as $key => $option) {
			if ($key != 0)
				$sql .= " OR ";
			$sql .= "`option_name` LIKE ".$this->adapter->platform->quoteValue($option);
		}
		$sql .= ")";
		*/
		
		$sql .= ") AND p.category_id=".$categoryid." and p.model_id=".$modelid." AND p.status=1";
		//echo $sql; exit;	
		$statement = $this->adapter->query($sql);
        $result = $statement->execute();
        $resultSet = new ResultSet;
        $resultSet->initialize($result);
        return $resultSet;
	}
    
    
    public function getGroupOption($modelId = 0, $categoryid = 0)
    {
    	$categoryid = (int)$categoryid;
    	$modelId = (int)$modelId;
    	
		$sql = 'SELECT pp.*, pog.group_name, pog.position,po.option_name FROM `product_price` AS pp
				INNER JOIN `product_option` AS po ON po.option_id=pp.option_id
				INNER JOIN `product_option_group` AS pog ON pog.group_id=pp.group_id AND pog.show_frontend=1
				WHERE product_id IN (
				  	SELECT id FROM '.$this->table.' WHERE category_id='.$categoryid.' and model_id='.$modelId.'
				) GROUP BY pp.option_id
				ORDER BY pp.group_id, pog.position ASC';
				
		$statement = $this->adapter->query($sql);
        $result = $statement->execute();
        $resultSet = new ResultSet;
        $resultSet->initialize($result);
        return $resultSet;
	}
	
    
    public function setHitsCount($productid = 0) 
    {
        // get last number of visit value for this product
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from($this->table)
              ->where(array('id' => $productid));
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        $resultSet = new ResultSet;
        $resultSet->initialize($result);
        $result_data = $resultSet->toArray();
        
        // update users_bid_log row
        $product_visit_data = array(
            'number_visit' => $result_data[0]['number_visit']+1
        );  
        $update = $sql->update($this->table)
                  ->set($product_visit_data)
                  ->where(array('product_id' => $productid));
        $statement = $sql->prepareStatementForSqlObject($update);
        $statement->execute(); 
    }
    
}

