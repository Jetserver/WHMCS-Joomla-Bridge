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

class JbridgeViewWhitelist extends JViewLegacy
{
	public $whitelist_items = array();
	public $item_details = array();
	public $isNew = true;
	public $item_id = 0;

	public function display($tpl = null)
	{
		//$db =& JFactory::getDBO(); /** php 5.4 issues **/
		$db = JFactory::getDBO();
		$document = JFactory::getDocument();

		$document->addScriptDeclaration('jQuery(document).ready(function(){ jQuery(\'.hasTooltip\').tooltip({"container": false}); });');

		$layout = JRequest::getVar('layout', 'default');
		$this->item_id = JRequest::getInt('id', 0);

		switch($layout)
		{
			default:
				$sql = "SELECT *
						FROM #__jbridge_whitelist";
				$db->setQuery($sql);
				$this->whitelist_items = $db->loadAssocList();
			break;

			case 'edit':

				$document->addScriptDeclaration('window.addEvent(\'domready\', function() {Calendar.setup({inputField: "expiry_calendar",ifFormat: "%Y-%m-%d %H:%M:%S",button: "expiry_calendar_img",align: "Tl",singleClick: true,firstDay: 0 });});');

				if($this->item_id)
				{
					$sql = "SELECT *
							FROM #__jbridge_whitelist
							WHERE id = '{$this->item_id}'";
					$db->setQuery($sql);
					$item_details = $db->loadAssoc();

					if($item_details)
					{
						$this->isNew = false;
						$this->item_details = $item_details;
					}
					else
					{
						// error
					}
				} 
				else 
				{
				
					$this->item_details['ip'] = "";
					$this->item_details['expiry'] = "";
				
				}
			break;
		}

		$this->addToolbar($layout);
		parent::display($tpl);
	}

	protected function addToolbar($layout)
	{
		JToolBarHelper::title(JText::_('COM_JBRIDGE_ADMINISTRATOR'), 'jbridge.png');

		switch($layout)
		{
			default:
				JToolBarHelper::addNew('addnew');
				JToolBarHelper::deleteList('', 'delete');
			break;

			case 'edit':

				JRequest::setVar('hidemainmenu', 1);

				JToolBarHelper::apply('apply');
				JToolBarHelper::save('save');
				JToolBarHelper::save2new('save2new');

				if($this->isNew)
				{
					JToolBarHelper::cancel('cancel');
				}
				else
				{
					JToolBarHelper::cancel('cancel', 'JTOOLBAR_CLOSE');
				}

			break;
		}
	}
}
