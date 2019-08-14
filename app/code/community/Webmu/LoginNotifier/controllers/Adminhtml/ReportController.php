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
class Webmu_LoginNotifier_Adminhtml_ReportController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction()
	{
		$this->loadLayout()->_setActiveMenu("loginnotifier/report")->_addBreadcrumb(Mage::helper("adminhtml")->__("Report Backend Login"), Mage::helper("adminhtml")->__("Report Backend Login"));
		return $this;
	}
	
	public function indexAction() 
	{
		$this->_title($this->__("LoginNotifier"));
		$this->_title($this->__("Report Backend Login"));

		$this->_initAction();
		$this->renderLayout();
	}

	/**
	 * Export order grid to CSV format
	 */
	public function exportCsvAction()
	{
		$fileName   = 'report.csv';
		$grid       = $this->getLayout()->createBlock('loginnotifier/adminhtml_report_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
	} 
	/**
	 *  Export order grid to Excel XML format
	 */
	public function exportExcelAction()
	{
		$fileName   = 'report.xml';
		$grid       = $this->getLayout()->createBlock('loginnotifier/adminhtml_report_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
	}
}
