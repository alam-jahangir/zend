<?php
namespace Application\Model;

class CostCalculation
{
	private static $priceList = array(
			array(
				'price_from' => 1, 
				'price_to' => 99, 
				'two_img_price' => 2, 
				'ten_img_price' => 0, 
				'ten_img_vid_price' => 0,
				'org_price' => 2
			),
			array(
				'price_from' => 100, 
				'price_to' => 499, 
				'two_img_price' => 5, 
				'ten_img_price' => 0, 
				'ten_img_vid_price' => 0,
				'org_price' => 5
			),
			array(
				'price_from' => 500, 
				'price_to' => 999, 
				'two_img_price' => 9, 
				'ten_img_price' => 0, 
				'ten_img_vid_price' => 0,
				'org_price' => 9
			),
			array(
				'price_from' => 1000, 
				'price_to' => 2999, 
				'two_img_price' => 19, 
				'ten_img_price' => 0, 
				'ten_img_vid_price' => 0,
				'org_price' => 19
			),
			array(
				'price_from' => 3000, 
				'price_to' => 4999, 
				'two_img_price' => 59, 
				'ten_img_price' => 0, 
				'ten_img_vid_price' => 0,
				'org_price' => 59
			),
			array(
				'price_from' => 5000, 
				'price_to' => 9999, 
				'two_img_price' => 99, 
				'ten_img_price' => 0, 
				'ten_img_vid_price' => 0,
				'org_price' => 99
			),
			array(
				'price_from' => 10000, 
				'price_to' => 19999, 
				'two_img_price' => 0, 
				'ten_img_price' => 199, 
				'ten_img_vid_price' => 0,
				'org_price' => 199
			),
			array(
				'price_from' => 20000, 
				'price_to' => 49999, 
				'two_img_price' => 0, 
				'ten_img_price' => 399, 
				'ten_img_vid_price' => 0,
				'org_price' => 399
			),
			array(
				'price_from' => 50000, 
				'price_to' => 99999, 
				'two_img_price' => 0, 
				'ten_img_price' => 999, 
				'ten_img_vid_price' => 0,
				'org_price' => 999
			),
			array(
				'price_from' => 100000, 
				'price_to' => 499000, 
				'two_img_price' => 0, 
				'ten_img_price' => 0, 
				'ten_img_vid_price' => 1999,
				'org_price' => 1999
			),
			array(
				'price_from' => 500000, 
				'price_to' => 999999, 
				'two_img_price' => 0, 
				'ten_img_price' => 0, 
				'ten_img_vid_price' => 9999,
				'org_price' => 9999
			),
			array(
				'price_from' => 1000000, 
				'price_to' => 1000000000, 
				'two_img_price' => 0, 
				'ten_img_price' => 0, 
				'ten_img_vid_price' => 19999,
				'org_price' => 19999
			)
		);
		
    public static function get(\Zend\Db\Adapter\Adapter $dbAdapter = null, $id, $recomendedPrice = 0, $numberImage = 0, $video = 0) {
    	$dealerUpload = new \Admin\Model\DealerUpload($dbAdapter);
		$cartInfo = $dealerUpload->getLastUpdateCartPrice($id);
		if ($cartInfo) {
	    	if (intval($cartInfo['upload_price']) < intval($recomendedPrice)) {
				$recomendedPrice = intval($recomendedPrice)-intval($cartInfo['upload_price']);
			} else {
				return null;
			}
		}
        
		
		$costPrice = 0;
		
		foreach (self::$priceList as $price) {
			if (($recomendedPrice >= $price['price_from'] && $recomendedPrice <= $price['price_to'])) {
				/*if ($numberImage <= 3) {
					$costPrice = $price['two_img_price'];
				} elseif ($numberImage <= 10 && $video) {
					$costPrice = $price['ten_img_vid_price'];
				} elseif ($numberImage <= 10) {
					$costPrice = $price['ten_img_price'];
				}
				*/
				$costPrice = $price['org_price'];
			}
		}
		
		return $costPrice;
        
    }  
    
    public static function getCurrentCost(\Zend\Db\Adapter\Adapter $dbAdapter = null, $id) {
    	
    	$dealerUpload = new \Admin\Model\DealerUpload($dbAdapter);
		$cartInfo = $dealerUpload->getLastUpdateCartPrice($id);
		
		$gallery = $dealerUpload->getUploadGallery($id);
		$numberImage = count($gallery);
		
		$upload = $dealerUpload->getUploadData($id);
		$video = $upload['video_filename'];
		$recomendedPrice = $upload['recommanded_price'];
		$costPrice = 0;
		
		foreach (self::$priceList as $price) {
			if (($recomendedPrice >= $price['price_from'] && $recomendedPrice <= $price['price_to'])) {
				/*
				if ($numberImage <= 3) {
					$costPrice = $price['two_img_price'];
				} elseif ($numberImage <= 10 && $video) {
					$costPrice = $price['ten_img_vid_price'];
				} elseif ($numberImage <= 10) {
					$costPrice = $price['ten_img_price'];
				}
				*/
				$costPrice = $price['org_price'];
			}
		}
		$cartPrice = array(
					'upload_id' => $id,
					'upload_price' => $recomendedPrice,
					'cart_price' => round($costPrice, 2),
					'cart_status' => 0,
					'updated_date' => date('Y-m-d h:i:s')
				);
		$dealerUpload->setDealerCartPrice($cartPrice, 1);
		return true;
	}
} 
