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
 
class JbridgeControllerWhitelist extends JController
{
	public function display()
	{
		$vName = JRequest::getCmd('view', 'whitelist');
		JRequest::setVar('view', $vName);
		JbridgeHelper::addSubmenu($vName);

		parent::display();
	}

	public function delete()
	{
		$db =& JFactory::getDBO();

		$post = JRequest::get('post');

		if(is_array($post['wid']) && sizeof($post['wid']))
		{
			$sql = "DELETE
				FROM #__jbridge_whitelist
				WHERE id IN('" . implode("','", $post['wid']) . "')";
			$db->setQuery($sql);
			$db->query();

			$this->setRedirect('index.php?option=com_jbridge&view=whitelist', JText::_('COM_JBRIDGE_WHITELIST_DELETED_SUCCESSFULLY'), 'message');
			return;
		}
		else
		{
			$this->setRedirect('index.php?option=com_jbridge&view=whitelist', JText::_('COM_JBRIDGE_WHITELIST_DELETED_ERROR'), 'error');
			return;
		}
	}

	public function addnew()
	{
		$this->setRedirect('index.php?option=com_jbridge&view=whitelist&layout=edit');
		return;
	}

	public function apply()
	{
		$this->_saveChanges('apply');
	}

	public function save()
	{
		$this->_saveChanges('save');
	}

	public function save2new()
	{
		$this->_saveChanges('save2new');
	}

	public function cancel()
	{
		$this->setRedirect('index.php?option=com_jbridge&view=whitelist');
		return;
	}

	function _saveChanges($type)
	{
		$db =& JFactory::getDBO();

		$item_id = JRequest::getInt('id', 0);

		$ipaddress = JRequest::getVar('ipaddress', '');
		$expiry = JRequest::getVar('expiry', '');
		$expiry_unix = $expiry ? strtotime($expiry) : 0;

		if($ipaddress && preg_match("/^([\d]{1,3}\.){3}[\d]{1,3}$/", $ipaddress) && (($expiry && $expiry_unix) || !$expiry))
		{
			if($item_id)
			{
				$sql = "UPDATE #__jbridge_whitelist
					SET ip = '{$ipaddress}', expiry = '{$expiry_unix}'
					WHERE id = '{$item_id}'";
				$db->setQuery($sql);
				$db->query();

				switch($type)
				{
					case 'apply': 		$redirect = "index.php?option=com_jbridge&view=whitelist&layout=edit&id={$item_id}"; break;
					case 'save': 		$redirect = "index.php?option=com_jbridge&view=whitelist"; break;
					case 'save2new': 	$redirect = "index.php?option=com_jbridge&view=whitelist&layout=edit"; break;
				}

				$this->setRedirect($redirect, "Whitelist item edited successfully", 'message');
				return;
			}
			else
			{
				$sql = "INSERT INTO #__jbridge_whitelist (`ip`,`expiry`) VALUES
					('{$ipaddress}','{$expiry_unix}')";
				$db->setQuery($sql);
				$db->query();

				$item_id = $db->insertid();

				switch($type)
				{
					case 'apply': 		$redirect = "index.php?option=com_jbridge&view=whitelist&layout=edit&id={$item_id}"; break;
					case 'save': 		$redirect = "index.php?option=com_jbridge&view=whitelist"; break;
					case 'save2new': 	$redirect = "index.php?option=com_jbridge&view=whitelist&layout=edit"; break;
				}

				$this->setRedirect($redirect, "Whitelist item added successfully", 'message');
				return;
			}
		}
		else
		{
			if($expiry && !$expiry_unix) $error = "Invalid Expiration Date provided";
			elseif(!$ipaddress) $error = "You must provide IP Address";
			elseif(!preg_match("/^([\d]{1,3}\.){3}[\d]{1,3}$/", $ipaddress)) $error = "Invalid IP Address provided";

			$this->setRedirect('index.php?option=com_jbridge&view=whitelist&layout=edit' . ($item_id ? "&id={$item_id}" : ''), $error, 'error');
			return;
		}
	}
}

?>