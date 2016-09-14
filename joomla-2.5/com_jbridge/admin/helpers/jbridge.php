<?php
/**
 * @version	$Id: jbridge.php 20196 2012-02-23 12:00:00Z idanbe $
 * @package	Joomla.Administrator
 * @subpackage	com_jbridge
 * @copyright	Copyright (C) Jetserver.
 */

// No direct access to this file
defined('_JEXEC') or die;

class JbridgeHelper
{
	public static function addSubmenu($submenu) 
	{
		JSubMenuHelper::addEntry(JText::_('COM_JBRIDGE_SUBMENU_SETTINGS'), 'index.php?option=com_jbridge', $submenu == 'settings');
		JSubMenuHelper::addEntry(JText::_('COM_JBRIDGE_SUBMENU_WHITELIST'), 'index.php?option=com_jbridge&view=whitelist', $submenu == 'whitelist');

		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_JBRIDGE_ADMINISTRATOR_' . strtoupper($submenu)));
	}
}

?>