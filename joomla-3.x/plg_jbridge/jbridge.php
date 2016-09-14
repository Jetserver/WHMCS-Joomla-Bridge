<?php
/**
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Plugin class for logout redirect handling.
 *
 * @package		Joomla.Plugin
 * @subpackage	System.logout
 */
class plgSystemJbridge extends JPlugin
{

	function plgSystemJbridge(&$subject, $config)
	{
		parent::__construct($subject, $config);
	}

	public function onAfterRoute()
	{
		$option = JRequest::getVar('option', '');

		if($option == 'com_users')
		{
			$view = JRequest::getVar('view', '');

			$redirect = '';

			switch($view)
			{
				case 'login': 		$redirect = $this->params->get('loginurl'); break;
				case 'reset': 		$redirect = $this->params->get('passwordreseturl'); break;
				case 'remind': 		$redirect = $this->params->get('usernamereminderurl'); break;
				case 'registration': 	$redirect = $this->params->get('registerurl'); break;
				case 'profile': 	$redirect = $this->params->get('editprofileurl'); break;
			}

			$task = JRequest::getVar('task', '');

			switch($task)
			{
				case 'user.logout':  	$redirect = $this->params->get('logouturl'); break;
			}

			if($redirect)
			{
				header("Location: {$redirect}");
				exit;
			}
		}
	}
}

?>