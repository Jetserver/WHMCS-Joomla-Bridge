<?php

if (!defined("WHMCS"))
	die("This file cannot be accessed directly");

include(dirname(__FILE__) . '/joomla.php');

function joomlabrigde_hook_login($vars)
{
	global $whmcs;

	$joomlaBridge = new joomlaBridge;

	if(!$joomlaBridge->config['sync_enabled']) return;

	$password = $_REQUEST['password'];

	$sql = "SELECT id, firstname, lastname, email
		FROM tblclients
		WHERE id = '{$vars['userid']}'";
	$result = mysql_query($sql);
	$client_details = mysql_fetch_assoc($result);

	$sql = "SELECT joomla_id
		FROM mod_joomlabridge_clients
		WHERE client_id = '{$vars['userid']}'";
	$result = mysql_query($sql);
	$joomla_details = mysql_fetch_assoc($result);
	$joomla_id = $joomla_details['joomla_id'];

	if(!$joomla_id && $password && $client_details)
	{
		$response = $joomlaBridge->request(array(
			'view'		=> 'usersmanager',
			'action'	=> 'register',
			'name'		=> "{$client_details['firstname']} {$client_details['lastname']}",
			'email'		=> $client_details['email'],
			'username'	=> $joomlaBridge->getUsername($client_details),
			'password'	=> $password,
		));

		if($response['status'] == '1')
		{
			$sql = "INSERT INTO mod_joomlabridge_clients (`client_id`,`joomla_id`) VALUES
				(" . intval($vars['userid']) . "," . intval($response['userid']) . ")";
			mysql_query($sql);

			$joomla_id = intval($response['userid']);
		}
	}
	elseif($joomla_id)
	{
		$joomlaBridge->request(array(
			'view'		=> 'usersmanager',
			'action'	=> 'edit',
			'user_id'	=> $joomla_id,
			'name'		=> "{$client_details['firstname']} {$client_details['lastname']}",
			'username'	=> $joomlaBridge->getUsername($client_details),
			'email'		=> $client_details['email'],
			'enabled'	=> $client_details['status'] != 'Closed' ? 1 : 0,
			'password'	=> $password,
		));
	}

	if($joomla_id)
	{
		// login into joomla
		$response = $joomlaBridge->request(array(
			'view'		=> 'usersmanager',
			'action'	=> 'token',
			'user_id'	=> $joomla_id,
		));

		if($response['status'] == '1')
		{
			echo $joomlaBridge->login($joomla_id, $joomlaBridge->getUsername($client_details), $password, $response['token']);
			exit;
		}
	}
}

function joomlabrigde_hook_logout($vars)
{
	$joomlaBridge = new joomlaBridge;

	if(!$joomlaBridge->config['sync_enabled']) return;

	$sql = "SELECT joomla_id
		FROM mod_joomlabridge_clients
		WHERE client_id = '{$vars['userid']}'";
	$result = mysql_query($sql);
	$joomla_details = mysql_fetch_assoc($result);
	$joomla_id = $joomla_details['joomla_id'];

	if($joomla_id)
	{
		// login into joomla
		$response = $joomlaBridge->request(array(
			'view'		=> 'usersmanager',
			'action'	=> 'token',
			'user_id'	=> $joomla_id,
		));
		
		if($response['status'] == '1')
		{
			$iframe = $joomlaBridge->logout($joomla_id, $response['token']);

			echo $iframe;
		}
	}
}

function joomlabrigde_hook_add($vars) 
{
	$joomlaBridge = new joomlaBridge;

	if(!$joomlaBridge->config['sync_enabled']) return;

	$response = $joomlaBridge->request(array(
		'view'		=> 'usersmanager',
		'action'	=> 'register',
		'name'		=> "{$vars['firstname']} {$vars['lastname']}",
		'email'		=> $vars['email'],
		'username'	=> $joomlaBridge->getUsername($vars),
		'password'	=> $vars['password'],
	));

	if($response['status'] == '1')
	{
		$sql = "INSERT INTO mod_joomlabridge_clients (`client_id`,`joomla_id`) VALUES
			(" . intval($vars['userid']) . "," . intval($response['userid']) . ")";
		mysql_query($sql);
	}
}

function joomlabrigde_hook_edit($vars) 
{
	$joomlaBridge = new joomlaBridge;

	if(!$joomlaBridge->config['sync_enabled']) return;

	$sql = "SELECT joomla_id
		FROM mod_joomlabridge_clients
		WHERE client_id = '{$vars['userid']}'";
	$result = mysql_query($sql);
	$joomla_details = mysql_fetch_assoc($result);
	$joomla_id = $joomla_details['joomla_id'];

	if($joomla_id)
	{
		$response = $joomlaBridge->request(array(
			'view'		=> 'usersmanager',
			'action'	=> 'edit',
			'user_id'	=> $joomla_id,
			'name'		=> "{$vars['firstname']} {$vars['lastname']}",
			'username'	=> $joomlaBridge->getUsername($vars),
			'email'		=> $vars['email'],
			'enabled'	=> $vars['status'] != 'Closed' ? 1 : 0,
		));
	}
}

function joomlabrigde_hook_changepwd($vars) 
{
	$joomlaBridge = new joomlaBridge;

	if(!$joomlaBridge->config['sync_enabled']) return;

	$sql = "SELECT joomla_id
		FROM mod_joomlabridge_clients
		WHERE client_id = '{$vars['userid']}'";
	$result = mysql_query($sql);
	$joomla_details = mysql_fetch_assoc($result);
	$joomla_id = $joomla_details['joomla_id'];

	if($joomla_id)
	{
		$response = $joomlaBridge->request(array(
			'view'		=> 'usersmanager',
			'action'	=> 'changepwd',
			'user_id'	=> $joomla_id,
			'password'	=> $vars['password'],
		));
	}
}

function joomlabrigde_hook_delete($vars) 
{
	$joomlaBridge = new joomlaBridge;

	if(!$joomlaBridge->config['sync_enabled']) return;

	$sql = "SELECT joomla_id
		FROM mod_joomlabridge_clients
		WHERE client_id = '{$vars['userid']}'";
	$result = mysql_query($sql);
	$joomla_details = mysql_fetch_assoc($result);
	$joomla_id = $joomla_details['joomla_id'];

	if($joomla_id)
	{
		$response = $joomlaBridge->request(array(
			'view'		=> 'usersmanager',
			'action'	=> 'delete',
			'user_id'	=> $joomla_id,
		));

		if($response['status'] == '1')
		{
			$sql = "DELETE
				FROM mod_joomlabridge_clients
				WHERE client_id = '{$vars['userid']}'";
			mysql_query($sql);
		}
	}
}

function joomlabrigde_hook_disable($vars)
{
	$joomlaBridge = new joomlaBridge;

	if(!$joomlaBridge->config['sync_enabled']) return;

	$sql = "SELECT joomla_id
		FROM mod_joomlabridge_clients
		WHERE client_id = '{$vars['userid']}'";
	$result = mysql_query($sql);
	$joomla_details = mysql_fetch_assoc($result);
	$joomla_id = $joomla_details['joomla_id'];

	if($joomla_id)
	{
		$response = $joomlaBridge->request(array(
			'view'		=> 'usersmanager',
			'action'	=> 'disable',
			'user_id'	=> $joomla_id,
		));
	}
}

function joomlabrigde_hook_smarty()
{
	global $smarty;

	$joomlaBridge = new joomlaBridge;

	$smarty_data = array();
	$modules = $joomlaBridge->getModules();

	if($modules['success'])
	{
		foreach($modules['data'] as $module_datails)
		{
			if(!$module_datails['visible']) continue;

			$module_data = $joomlaBridge->getModulesData($module_datails['id']);

			if($module_data['success'])
			{
				$smarty_data[$joomlaBridge->getSmarty($module_datails)] = $module_data['data'];
			}
		}

		$smarty->assign('jbridge', $smarty_data);
	}
}

function joomlabrigde_hook_cron()
{
	$joomlaBridge = new joomlaBridge;

	$joomlaBridge->clearCache();
}

add_hook("ClientAdd", 			1, "joomlabrigde_hook_add");
add_hook("ClientEdit", 			1, "joomlabrigde_hook_edit");
add_hook("ClientDelete", 		1, "joomlabrigde_hook_delete");
add_hook("ClientChangePassword", 	1, "joomlabrigde_hook_changepwd");
add_hook("ClientClose", 		1, "joomlabrigde_hook_disable");
add_hook("ClientLogin", 		1, "joomlabrigde_hook_login");
add_hook("ClientLogout", 		1, "joomlabrigde_hook_logout");
add_hook("ClientAreaPage", 		1, "joomlabrigde_hook_smarty");
add_hook("ClientAreaHomepage", 		1, "joomlabrigde_hook_smarty");
add_hook("DailyCronJob", 		1, "joomlabrigde_hook_cron");

?>