<?php
/**
 * @version	$Id: view.html.php 20196 2012-02-23 12:00:00Z idanbe $
 * @package	Joomla.Administrator
 * @subpackage	com_jbridge
 * @copyright	Copyright (C) Jetserver.
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class JbridgeViewSettings extends JView
{
	var $config = array();

	public function display($tpl = null)
	{
		$db =& JFactory::getDBO();

		$sql = "SELECT *
			FROM #__jbridge_config";
		$db->setQuery($sql);
		$config_values = $db->loadAssocList();

		foreach($config_values as $row)
		{
			$this->config[$row['name']] = $row['value'];
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar()
	{
		JToolBarHelper::title(JText::_('COM_JBRIDGE_ADMINISTRATOR_SETTINGS'), 'jbridge.png');
		JToolBarHelper::apply('apply');
	}
}
