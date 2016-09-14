<?php
/**
 * @version     1.0.0
 * @package     com_jbridge
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Idan Ben-Ezra <admin@jetserver.co.il> - http://www.jetserver.net
 */

defined('_JEXEC') or die;

// Include dependancies
jimport('joomla.application.component.controller');
jimport('joomla.user.helper');

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
				AND token = " .$db->quote($token);
		$db->setQuery($sql);
		$token_exists = $db->loadAssoc();

		if($token_exists)
		{
			$sql = "DELETE
					FROM #__jbridge_tokens
					WHERE token = " .$db->quote($token);
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
				WHERE token = " .$db->quote($token);
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
			AND ip = " .$db->quote($_SERVER['REMOTE_ADDR']);
	$db->setQuery($sql);
	$whitelisted = $db->loadAssoc();

	if(!$whitelisted)
	{
		echo json_encode(array(
			'status'	=> false,
			'message'	=> 'Access Forbidden - IP '.$_SERVER['REMOTE_ADDR'].' is not allowed in joomla bridge',
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
			WHERE username =  ".$db->quote($login_details['username']);
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
	if(!in_array($config['accessgroup'], $admin->groups))
	{
		echo json_encode(array(
			'status'	=> false,
			'message'	=> 'You can\'t acces with the user group.',
		));
		exit;
	}

	$logged = false;

	if(method_exists('JUserHelper', 'verifyPassword'))
	{
		$logged = JUserHelper::verifyPassword($login_details['password'], $admin->password);
	}
	else
	{
		if (substr($admin->password, 0, 4) == '$2y$')
		{
			// BCrypt passwords are always 60 characters, but it is possible that salt is appended although non standard.
			$password60 = substr($admin->password, 0, 60);

			if (JCrypt::hasStrongPasswordSupport())
			{
				$logged = password_verify($login_details['password'], $password60);
			}
		}
		elseif (substr($admin->password, 0, 8) == '{SHA256}')
		{
			// Check the password
			$parts  = explode(':', $admin->password);
			$crypt  = $parts[0];
			$salt   = @$parts[1];
			$testcrypt = JUserHelper::getCryptedPassword($login_details['password'], $salt, 'sha256', false);

			if ($admin->password == $testcrypt)
			{
				$logged = true;
			}
		}
		else
		{
			// Check the password
			$parts  = explode(':', $admin->password);
        	        $crypt  = $parts[0];
			$salt   = @$parts[1];

			$testcrypt = JUserHelper::getCryptedPassword($login_details['password'], $salt, 'md5-hex', false);

			if ($crypt == $testcrypt)
			{
				$logged = true;
			}
		}
	}

	if(!$logged)
	{
		echo json_encode(array(
			'status'	=> false,
			'message'	=> 'Incorrect Username and/or Password',
		));
		exit;
	}
}

// Execute the task.
$controller	= JControllerLegacy::getInstance('Jbridge');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();