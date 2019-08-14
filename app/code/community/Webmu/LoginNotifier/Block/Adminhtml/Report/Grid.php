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
class Webmu_LoginNotifier_Block_Adminhtml_Report_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId("reportGrid");
		$this->setDefaultSort("created");
		$this->setDefaultDir("DESC");
		$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel("loginnotifier/report")->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	
	protected function _prepareColumns()
	{			
		$this->addColumn("created", array(
			"header" => Mage::helper("loginnotifier")->__("Created on"),
			"align" =>"left",
			"width" => "150px",
			"type" => "datetime",
			"index" => "created",
		));
		
		$this->addColumn("ip", array(
			"header" => Mage::helper("loginnotifier")->__("IP address"),
			"align" =>"right",
			"index" => "ip",
		));
		
		$this->addColumn("username", array(
			"header" => Mage::helper("loginnotifier")->__("Username"),
			"index" => "username",
		));
		
		$this->addColumn("user_agent", array(
			"header" => Mage::helper("loginnotifier")->__("User agent"),
			"index" => "user_agent",
		));
		
		$this->addColumn("http_referer", array(
			"header" => Mage::helper("loginnotifier")->__("Referer"),
			"width" => "200px",
			"index" => "http_referer",
		));
		
		$this->addColumn('result', array(
			'header' => Mage::helper('loginnotifier')->__('Result'),
			"align" =>"right",
			'index' => 'result',
			'type' => 'options',
			'options'=> Mage::getModel('loginnotifier/resultOption')->toOptionArray()
		));
			
		$this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV')); 
		$this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

		return parent::_prepareColumns();
	}

	public function getRowUrl($row)
	{
		return false;
	}		
}