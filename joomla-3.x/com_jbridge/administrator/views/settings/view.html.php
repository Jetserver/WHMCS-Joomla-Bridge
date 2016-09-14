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

class JbridgeViewSettings extends JViewLegacy
{
	var $config = array();
	var $groups = array();

	public function display($tpl = null)
	{
		$db = JFactory::getDBO();
		$document = JFactory::getDocument();

		$document->addScriptDeclaration('jQuery(document).ready(function(){ jQuery(\'.hasTooltip\').tooltip({"container": false}); });');

		$sql = "SELECT *
			FROM #__jbridge_config";
		$db->setQuery($sql);
		$config_values = $db->loadAssocList();

		foreach($config_values as $row)
		{
			$this->config[$row['name']] = $row['value'];
		}

		$sql = "SELECT *
			FROM #__usergroups";
		$db->setQuery($sql);
		$group_values = $db->loadAssocList();

		foreach($group_values as $row)
		{
			$this->groups[] = $row;
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar()
	{
		JToolBarHelper::title(JText::_('COM_JBRIDGE_ADMINISTRATOR'), 'jbridge.png');
		JToolBarHelper::apply('apply');
	}
}
