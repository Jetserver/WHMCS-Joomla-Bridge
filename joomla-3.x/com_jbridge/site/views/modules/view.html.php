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

jimport('joomla.application.component.view');

/**
 * View to edit
 */
class JbridgeViewModules extends JViewLegacy 
{
	public function display($tpl = null) 
	{
		$db = JFactory::getDBO();

		$config = array();

		$sql = "SELECT *
			FROM #__jbridge_config";
		$db->setQuery($sql);
		$config_values = $db->loadAssocList();

		foreach($config_values as $row)
		{
			$config[$row['name']] = $row['value'];
		}

		if(!$config['modulesyncenabled'])
		{
			echo json_encode(array(
				'status'	=> false,
				'message'	=> 'Modules Sync is disabled in the system',
			));
			exit;
		}

		$action = JRequest::getVar('action');

		switch($action)
		{
			case 'modulelist':

				$sql = "SELECT id,title,position,module
					FROM #__modules
					WHERE published = 1
					AND access = 1
					AND client_id = 0
					ORDER BY id ASC";
				$db->setQuery($sql);

				$output = array(
					'status'	=> true,
					'data'		=> $db->loadAssocList(),
					'message'	=> '',
				);

				echo json_encode($output);
			exit;

			case 'moduledata':

				$output = array('status' => false, 'data' => null, 'message' => 'Unknown Error');

				$module_id = JRequest::getInt('module_id');

				$sql = "SELECT *
					FROM #__modules
					WHERE published = 1
					AND access = 1
					AND client_id = 0
					AND id = '{$module_id}'
					ORDER BY id ASC";
				$db->setQuery($sql);
				$module_data = $db->loadAssoc();

				if($module_data)
				{
					$output['status'] = true;
					$output['data'] = JModuleHelper::renderModule((object) $module_data);
					$output['message'] = '';
				}
				else
				{
					$output['message'] = "No module was found for the id {$module_id}";
				}

				echo json_encode($output);
			exit;

			default:

				echo json_encode(array(
					'status'	=> false,
					'message'	=> 'Invalid action provided',
				));
			exit;
		}

		parent::display($tpl);
	}
}

?>