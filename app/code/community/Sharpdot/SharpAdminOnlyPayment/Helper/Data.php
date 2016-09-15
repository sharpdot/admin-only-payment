<?php
/**
 * Magento
 *
 *
 * @category    Sharpdot
 * @package     Sharpdot_SharpAdminOnlyPayment
 * @copyright   Copyright (c) 2010 Sharpdot Inc. (http://www.sharpdotinc.com)
 * @license     
 */

/**
 *
 * @category   Sharpdot
 * @package    Sharpdot_SharpAdminOnlyPayment
 * @author     Michael Dost <mdost@sharpdotinc.com>
 */
class Sharpdot_SharpAdminOnlyPayment_Helper_Data extends Mage_Payment_Helper_Data
{
	public function getStoreMethods($store=null, $quote=null)
    {    	
    	$res = parent::getStoreMethods($store=null, $quote=null);
    	
    	//Only continue if this is an admin created order.
    	//Could check based on Session or controller action. Went with session
    	//if (preg_match("|^/index.php/admin/sales_order_create/|", $_SERVER['REQUEST_URI'])) {
    	if(Mage::getSingleton('admin/session')->isLoggedIn()){
	    	$methods = Mage::getStoreConfig(self::XML_PATH_PAYMENT_METHODS, $store);    	
	    	//print_r($methods);die("here");
	    	foreach ($methods as $code => $methodConfig) {
	            $prefix = self::XML_PATH_PAYMENT_METHODS.'/'.$code.'/';
	
	            //MRD If it is all ready active no need to check for admin override
	    		if (Mage::getStoreConfigFlag($prefix.'active', $store)) {
	                continue;
	            }
	            //check for admin override
	            if (!Mage::getStoreConfigFlag($prefix.'admin_active', $store)) {
	                continue;
	            }
	            if (!$model = Mage::getStoreConfig($prefix.'model', $store)) {
	                continue;
	            }
	
	            $methodInstance = Mage::getModel($model);			            
	            if ($methodInstance instanceof Mage_Payment_Model_Method_Cc && !Mage::getStoreConfig($prefix.'cctypes')) {
	                /* if the payment method has credit card types configuration option
	                   and no credit card type is enabled in configuration */
	                continue;
	            }
	
	            if ( !$methodInstance->isAvailable($quote) ) {
	                /* if the payment method can not be used at this time */
	                continue;
	            }
	
	            $sortOrder = (int)Mage::getStoreConfig($prefix.'sort_order', $store);
	            $methodInstance->setSortOrder($sortOrder);
	            $methodInstance->setStore($store);

	            $res[] = $methodInstance;
	        }
	        
	        //Sort based on sort order
	        usort($res, array($this, '_sortMethods'));
    	}
        return $res;
    }
}
