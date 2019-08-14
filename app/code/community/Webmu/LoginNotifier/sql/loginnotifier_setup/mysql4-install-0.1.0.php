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

$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
CREATE TABLE IF NOT EXISTS `webmu_loginnotifier` (
	`id` int(11) NOT NULL,
	`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`username` varchar(100) NOT NULL,
	`ip` varchar(20) NOT NULL,
	`user_agent` varchar(255) NOT NULL,
	`http_referer` varchar(255) NOT NULL,
	`result` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `webmu_loginnotifier`
--
ALTER TABLE `webmu_loginnotifier`
 ADD PRIMARY KEY (`id`), ADD KEY `result` (`result`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `webmu_loginnotifier`
--
ALTER TABLE `webmu_loginnotifier`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

SQLTEXT;

$installer->run($sql);
$installer->endSetup();
	 