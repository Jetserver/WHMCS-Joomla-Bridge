<?php
/**
 * @package	Joomla.Site
 * @subpackage	com_jbridge
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license	GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Require the com_content helper library
jimport('joomla.application.component.controller');
jimport('joomla.user.helper');

$db =& JFactory::getDBO();

$config = array();

$sql = "SELECT *
	FROM #__jbridge_config";
$db->setQuery($sql);
$config_values = $db->loadAssocList();

foreach($config_values as $row)
{
	$config[$row['name']] = $row['value'];
}

$login = JRequest::getVar('login', '');
$user_id = JRequest::getInt('user_id', 0);
$token = JRequest::getVar('token', '');
$view = JRequest::getVar('view', '');
$action = JRequest::getVar('action', '');

if($view == 'usersmanager' && in_array($action, array('login','logout')))
{
	$sql = "DELETE
		FROM #__jbridge_tokens
		WHERE time <= '" . (time() - (60 * 5)) . "'";
	$db->setQuery($sql);
	$db->query();

	if(!$config['userloginenabled'])
	{
		echo json_encode(array(
			'status'	=> false,
			'message'	=> 'Users Login is disabled in the system',
		));
		exit;
	}

	if($user_id)
	{
		$sql = "SELECT token
			FROM #__jbridge_tokens
			WHERE user_id = '{$user_id}'
			AND token = '" . mysql_escape_string($token) . "'";
		$db->setQuery($sql);
		$token_exists = $db->loadAssoc();

		if($token_exists)
		{
			$sql = "DELETE
				FROM #__jbridge_tokens
				WHERE token = '" . mysql_escape_string($token) . "'";
			$db->setQuery($sql);
			$db->query();
		}
		else
		{
			echo json_encode(array(
				'status'	=> false,
				'message'	=> 'Invalid Token',
			));
			exit;
		}
	}
	else
	{
		$sql = "DELETE
			FROM #__jbridge_tokens
			WHERE token = '" . mysql_escape_string($token) . "'";
		$db->setQuery($sql);
		$db->query();

		echo json_encode(array(
			'status'	=> false,
			'message'	=> 'User not exists',
		));
		exit;
	}
}
else
{
	$sql = "SELECT id
		FROM #__jbridge_whitelist
		WHERE (expiry = 0 OR expiry > '" . time() . "')
		AND ip = '{$_SERVER['REMOTE_ADDR']}'";
	$db->setQuery($sql);
	$whitelisted = $db->loadAssoc();

	if(!$whitelisted)
	{
		echo json_encode(array(
			'status'	=> false,
			'message'	=> 'Access Forbidden',
		));
		exit;
	}

	if(!$login)
	{
		echo json_encode(array(
			'status'	=> false,
			'message'	=> 'No login details was provided',
		));
		exit;
	}

	$login_details = array();
	list($login_details['username'], $login_details['password']) = explode(':', $login);

	if(!$login_details['username'] || !$login_details['password'])
	{
		echo json_encode(array(
			'status'	=> false,
			'message'	=> 'No Username and/or Password was provided',
		));
		exit;
	}

	$sql = "SELECT id
		FROM #__users
		WHERE username = '" . mysql_escape_string($login_details['username']) . "'";
	$db->setQuery($sql);
	$admin_id = $db->loadAssoc();
	$admin_id = $admin_id['id'];

	if(!$admin_id)
	{
		echo json_encode(array(
			'status'	=> false,
			'message'	=> 'Username not exists',
		));
		exit;
	}

	$admin = JFactory::getUser($admin_id);

	// check is user is admin
	$is_admin = false;

	foreach($admin->groups as $group_id)
	{
		$sql = "SELECT id
			FROM #__usergroups
			WHERE id = '{$group_id}'
			AND title = 'Super Users'";
		$db->setQuery($sql);
		$is_admin = $db->loadAssoc();

		if($is_admin) break;
	}

	if(!$is_admin)
	{
		echo json_encode(array(
			'status'	=> false,
			'message'	=> 'You are not Super User',
		));
		exit;
	}

	list($password, $salt) = explode(':', $admin->password);
	$check_password = JUserHelper::getCryptedPassword($login_details['password'], $salt);

	if($password != $check_password)
	{
		echo json_encode(array(
			'status'	=> false,
			'message'	=> 'Incorrect Username and/or Password',
		));
		exit;
	}
}

$controller = JController::getInstance('Jbridge');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();

?>