<?php
/**
 * @version     1.0.0
 * @package     com_jbridge
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Idan Ben-Ezra <admin@jetserver.co.il> - http://www.jetserver.net
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Jbridge helper.
 */
class JbridgeHelper
{
	/**
	 * Configure the Linkbar.
	 */
	public static function addSubmenu($submenu)
	{
		JSubMenuHelper::addEntry(JText::_('COM_JBRIDGE_SUBMENU_SETTINGS'), 'index.php?option=com_jbridge', $submenu == 'settings');
		JSubMenuHelper::addEntry(JText::_('COM_JBRIDGE_SUBMENU_WHITELIST'), 'index.php?option=com_jbridge&view=whitelist', $submenu == 'whitelist');

		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_JBRIDGE_ADMINISTRATOR_' . strtoupper($submenu)));
	}
}
