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
class Webmu_LoginNotifier_Model_Admin_Session extends Mage_Admin_Model_Session
{
    /**
     * Pull out information from session whether there is currently the first page after log in
     *
     * The idea is to set this value on login(), then redirect happens,
     * after that on next request the value is grabbed once the session is initialized
     * Since the session is used as a singleton, the value will be in $_isFirstPageAfterLogin until the end of request,
     * unless it is reset intentionally from somewhere
     *
     * @param string $namespace
     * @param string $sessionName
     * @return Mage_Admin_Model_Session
     * @see self::login()
     */
    public function init($namespace, $sessionName = null)
    {
        parent::init($namespace, $sessionName);
        $this->isFirstPageAfterLogin();
        return $this;
    }

    /**
     * Try to login user in admin
     *
     * @param  string $username
     * @param  string $password
     * @param  Mage_Core_Controller_Request_Http $request
     * @return Mage_Admin_Model_User|null
     */
    public function login($username, $password, $request = null)
    {
        if (empty($username) || empty($password)) {
            return;
        }
		
		$result = 0;
		
        try {
            /** @var $user Mage_Admin_Model_User */
            $user = $this->_factory->getModel('admin/user');
            $user->login($username, $password);
            if ($user->getId()) {
            
            	$this->_addLoginNotification(array(
            		"username" => $username,
            		"result" => 1
            		)
            	);
            	
            	if( Mage::getStoreConfig('loginnotifier/success/general_success_login') == 'enabled'){
            		$this->sendSuccessNotificationEmail($username, $password);
            	}
            	
                $this->renewSession();

                if (Mage::getSingleton('adminhtml/url')->useSecretKey()) {
                    Mage::getSingleton('adminhtml/url')->renewSecretUrls();
                }
                $this->setIsFirstPageAfterLogin(true);
                $this->setUser($user);
                $this->setAcl(Mage::getResourceModel('admin/acl')->loadAcl());

                $alternativeUrl = $this->_getRequestUri($request);
                $redirectUrl = $this->_urlPolicy->getRedirectUrl($user, $request, $alternativeUrl);
                if ($redirectUrl) {
                    Mage::dispatchEvent('admin_session_user_login_success', array('user' => $user));
                    $this->_response->clearHeaders()
                        ->setRedirect($redirectUrl)
                        ->sendHeadersAndExit();
                }
            } else {
            
            	$this->_addLoginNotification(array(
            		"username" => $username,
            		"result" => 0
            		)
            	);
            	
            	if( Mage::getStoreConfig('loginnotifier/failed/general_failed_login') == 'enabled'){
            		$this->sendFailedNotificationEmail($username, $password);
            	}
                Mage::throwException(Mage::helper('adminhtml')->__('Invalid User Name or Password.'));
            }
        } catch (Mage_Core_Exception $e) {
            Mage::dispatchEvent('admin_session_user_login_failed',
                array('user_name' => $username, 'exception' => $e));
            if ($request && !$request->getParam('messageSent')) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $request->setParam('messageSent', true);
            }
        }
		
        return $user;
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
    
    public function sendFailedNotificationEmail( $username, $password)
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
			'ip' => Mage::helper('core/http')->getRemoteAddr()
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
	
	public function sendSuccessNotificationEmail( $username, $password)
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
			'ip' => Mage::helper('core/http')->getRemoteAddr()
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
