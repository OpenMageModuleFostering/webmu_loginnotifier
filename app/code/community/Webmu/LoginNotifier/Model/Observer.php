<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    Webmu
 * @package     Webmu_LoginNotifier
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Webmu_LoginNotifier_Model_Observer {

    public function successLogin( $observer) {
    
    	if ($observer->getEvent()->getUser()) {
			$this->_addLoginNotification(array(
					"username" => $observer->getEvent()->getUser()->getUsername(),
					"result" => 1
				)
			);
				
			if( Mage::getStoreConfig('loginnotifier/success/general_success_login') == 'enabled'){
				$this->sendSuccessNotificationEmail($observer->getEvent()->getUser()->getUsername());
			}
		}
    }
    
    public function failedLogin( $observer) {
    	
    	if ($observer['user_name']) {
			$this->_addLoginNotification(array(
					"username" => $observer['user_name'],
					"result" => 0
				)
			);
				
			if( Mage::getStoreConfig('loginnotifier/failed/general_failed_login') == 'enabled'){
				$this->sendFailedNotificationEmail($observer['user_name']);
			}
		}
    }
    
	protected function _addLoginNotification( $data_params = array()){
    	
    	$data = array_merge( $data_params, array(
			'ip' => Mage::helper('core/http')->getRemoteAddr(),
			'user_agent' => Mage::helper('core/http')->getHttpUserAgent(),
			'http_referer' => Mage::helper('core/http')->getHttpReferer()
		));
		$model = Mage::getModel('loginnotifier/report')->setData($data);
		
		try {
    		$insertId = $model->save()->getId();
		} catch (Exception $error){
			Mage::getSingleton('core/session')->addError($error->getMessage());
	 		return false; 
		}
	}
    
    public function sendFailedNotificationEmail( $username)
    {
		$template_id = 'loginnotifier_notificationemail_failed';
		
		$recipients = Mage::getStoreConfig('loginnotifier/failed/general_failed_login_email');
		if( strstr( $recipients, ",")){
			$recipients = array_map('trim', explode(',', $recipients));
		}
		
		$senderName = Mage::getStoreConfig('trans_email/ident_general/name');
		$senderEmail = Mage::getStoreConfig('trans_email/ident_general/email');
		
		$emailTemplate  = Mage::getModel('core/email_template')->loadDefault($template_id);
		$emailTemplateVariables = array(
			'username' => $username,
			'date' => Mage::getModel('core/date')->date('Y-m-d'),
			'hour' => Mage::getModel('core/date')->date('H:i'),
			'ip' => Mage::helper('core/http')->getRemoteAddr(),
			'base_url' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB)
		);

		//Appending the Custom Variables to Template.
		$processedTemplate = $emailTemplate->getProcessedTemplate($emailTemplateVariables);
		
		//Sending E-Mail to Customers.
		$mail = Mage::getModel('core/email')
 		->setToEmail($recipients)
 		->setSubject($emailTemplate->getTemplateSubject())
 		->setBody($processedTemplate)
 		->setFromEmail($senderEmail)
 		->setFromName($senderName)
 		->setType('html');
 
	 	try{
	 		//Confimation E-Mail Send
	 		$mail->send();
	 	}
	 	catch(Exception $error)
	 	{
	 		Mage::getSingleton('core/session')->addError($error->getMessage());
	 		return false;
		}
	}
	
	public function sendSuccessNotificationEmail( $username)
    {
		$template_id = 'loginnotifier_notificationemail_success';
		
		$recipients = Mage::getStoreConfig('loginnotifier/success/general_success_login_email');
		if( strstr( $recipients, ",")){
			$recipients = array_map('trim', explode(',', $recipients));
		}
		
		$senderName = Mage::getStoreConfig('trans_email/ident_general/name');
		$senderEmail = Mage::getStoreConfig('trans_email/ident_general/email');
		
		$emailTemplate  = Mage::getModel('core/email_template')->loadDefault($template_id);
		
		$senderName = Mage::getStoreConfig('trans_email/ident_general/name');
		$senderEmail = Mage::getStoreConfig('trans_email/ident_general/email');

		$emailTemplateVariables = array(
			'username' => $username,
			'date' => Mage::getModel('core/date')->date('Y-m-d'),
			'hour' => Mage::getModel('core/date')->date('H:i'),
			'ip' => Mage::helper('core/http')->getRemoteAddr(),
			'base_url' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB)
		);
		
		//Appending the Custom Variables to Template.
		$processedTemplate = $emailTemplate->getProcessedTemplate($emailTemplateVariables);
		
		//Sending E-Mail to Customers.
		$mail = Mage::getModel('core/email')
 		->setToEmail($recipients)
 		->setSubject($emailTemplate->getTemplateSubject())
 		->setBody($processedTemplate)
 		->setFromEmail($senderEmail)
 		->setFromName($senderName)
 		->setType('html');
 
	 	try{
	 		//Confimation E-Mail Send
	 		$mail->send();
	 	}
	 	catch(Exception $error)
	 	{
	 		Mage::getSingleton('core/session')->addError($error->getMessage());
	 		return false;
		}
	}
}