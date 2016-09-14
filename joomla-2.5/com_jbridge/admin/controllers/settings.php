<?php
/**
 * @version	$Id: settings.php 20196 2012-02-23 12:00:00Z idanbe $
 * @package	Joomla.Administrator
 * @subpackage	com_jbridge
 * @copyright	Copyright (C) Jetserver.
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');
 
class JbridgeControllerSettings extends JController
{
	public function display()
	{
		$vName = JRequest::getCmd('view', 'settings');
		JRequest::setVar('view', $vName);
		JbridgeHelper::addSubmenu($vName);

		parent::display();
	}

	public function apply()
	{
		$db =& JFactory::getDBO();

		$post = JRequest::get('post');
		$config_data = $post['config'];
		
		if(is_array($config_data) && sizeof($config_data))
		{
			$config = array();

			$sql = "SELECT *
				FROM #__jbridge_config";
			$db->setQuery($sql);
			$config_values = $db->loadAssocList();

			foreach($config_values as $row)
			{
				$config[$row['name']] = $row['value'];
			}

			foreach($config_data as $key => $value)
			{
				if(isset($config[$key]))
				{
					$sql = "UPDATE #__jbridge_config
						SET value = '" . mysql_escape_string($value) . "'
						WHERE name = '" . mysql_escape_string($key) . "'";
					$db->setQuery($sql);
					$db->query();
				}
				else
				{
					$sql = "INSERT INTO #__jbridge_config (`name`,`value`) VALUES
						('" . mysql_escape_string($key) . "','" . mysql_escape_string($value) . "')";
					$db->setQuery($sql);
					$db->query();
				}
			}
		}

		$this->setRedirect('index.php?option=com_jbridge&view=settings', JText::_('COM_JBRIDGE_SETTINGS_SAVED_SUCCESSFULLY'), 'message');
	}
}

?>