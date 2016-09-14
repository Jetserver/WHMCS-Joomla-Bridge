<?php

if (!defined("WHMCS"))
	die("This file cannot be accessed directly");

class joomlaBridge
{
	var $config = array();

	function joomlaBridge()
	{
		$this->config = $this->getConfig();
	}

	function getConfig()
	{
		$config = array();

		$sql = "SELECT *
			FROM mod_joomlabridge_config";
		$result = mysql_query($sql);

		while($data = mysql_fetch_assoc($result))
		{
			$config[$data['name']] = $data['value'];
		}
		mysql_free_result($result);

		return $config;
	}

	function setConfig($key, $value)
	{
		if(isset($this->config[$key]))
		{
			$sql = "UPDATE mod_joomlabridge_config
				SET value = '" . mysql_escape_string($value) . "'
				WHERE name = '{$key}'";
			mysql_query($sql);
		}
		else
		{
			$sql = "INSERT INTO mod_joomlabridge_config (`name`,`value`) VALUES
				('" . mysql_escape_string($key) . "','" . mysql_escape_string($value) . "')";
			mysql_query($sql);
		}

		$this->config[$key] = $value;
	}

	function filterModulesData($module_id, $module_data)
	{
		$sql = "SELECT *
			FROM mod_joomlabridge_modules_attrs
			WHERE module_id = '{$module_id}'
			ORDER BY attr_order ASC";
		$result = mysql_query($sql);

		while($filter = mysql_fetch_assoc($result))
		{
			$module_data = preg_replace(htmlspecialchars_decode($filter['attr_find']), htmlspecialchars_decode($filter['attr_replace']), $module_data);
		}
		mysql_free_result($result);		

		return $module_data;
	}

	function getModulesData($module_id = null, $force_cache = false)
	{
		$output = $module_id ? '' : array();

		if($this->config['cache_enabled'] && !$force_cache)
		{
			$sql = "SELECT d.module_content, d.module_id
				FROM mod_joomlabridge_modules_data as d
				INNER JOIN mod_joomlabridge_modules as m
				ON m.id = d.module_id
				WHERE m.visible = 1
				" . ($module_id ? "AND d.module_id = '{$module_id}'" : '');
			$result = mysql_query($sql);

			if($module_id)
			{
				$module_details = mysql_fetch_assoc($result);
				$output = $this->filterModulesData($module_id, ($module_details['module_content'] ? $module_details['module_content'] : $this->getModulesData($module_id, true)));
			}
			else
			{
				while($module_details = mysql_fetch_assoc($result))
				{
					$output[$module_details['module_id']] = $this->filterModulesData($module_details['module_id'], ($module_details['module_content'] ? $module_details['module_content'] : $this->getModulesData($module_details['module_id'], true)));
				}
				mysql_free_result($result);
			}
		}
		else
		{
			if($module_id)
			{
				$sql = "SELECT *
					FROM mod_joomlabridge_modules
					WHERE id = '{$module_id}'";
					//."AND visible = 1";
				$result = mysql_query($sql);
				$module_details = mysql_fetch_assoc($result);

				if($module_details)
				{
					$response = $this->request(array(
						'view'		=> 'modules',
						'action'	=> 'moduledata',
						'module_id'	=> $module_details['module_id'],
					));

					if($response['status'] && $response['data'])
					{
						$sql = "SELECT id
							FROM mod_joomlabridge_modules_data
							WHERE module_id = '{$module_id}'";
						$result = mysql_query($sql);
						$module_cache_details = mysql_fetch_assoc($result);

						if($module_cache_details)
						{
							$sql = "UPDATE mod_joomlabridge_modules_data
								SET module_content = '" . trim(mysql_escape_string($response['data'])) . "'
								WHERE id = '{$module_cache_details['id']}'";
							mysql_query($sql);
						}
						else
						{
							$sql = "INSERT INTO mod_joomlabridge_modules_data (`module_id`,`module_content`) VALUES
								('" . intval($module_id) . "', '" . trim(mysql_escape_string($response['data'])) . "')";
							mysql_query($sql);
						}

						$output = $this->filterModulesData($module_id, $response['data']);
					}
				}
			}
			else
			{
				$delete = array();

				$modules = $this->getModules();

				foreach($modules as $module)
				{
					$response = $this->request(array(
						'view'		=> 'modules',
						'action'	=> 'moduledata',
						'module_id'	=> $module['module_id'],
					));

					if($response['status'] && $response['data'])
					{
						$sql = "SELECT id
							FROM mod_joomlabridge_modules_data
							WHERE module_id = '{$module['id']}'";
						$result = mysql_query($sql);
						$module_cache_details = mysql_fetch_assoc($result);

						if($module_cache_details)
						{
							$sql = "UPDATE mod_joomlabridge_modules_data
								SET module_content = '" . mysql_escape_string($response['data']) . "'
								WHERE id = '{$module_cache_details['id']}'";
							mysql_query($sql);
						}
						else
						{
							$sql = "INSERT INTO mod_joomlabridge_modules (`module_id`,`module_content`) VALUES
								('" . intval($module['id']) . "', '" . mysql_escape_string($response['data']) . "')";
							mysql_query($sql);
						}

						$delete[] = $module['id'];

						if($module['visible']) $output[$module['id']] = $this->filterModulesData($module['id'], $response['data']);
					}
				}

				if(sizeof($delete))
				{
					$sql = "DELETE
						FROM mod_joomlabridge_modules_data
						WHERE module_id NOT IN('" . implode("','", $delete) . "')";
					mysql_query($sql);
				}
			}
		}

		return $output;
	}

	function getModules($force_cache = false)
	{
		$modulelist = array();

		if($this->config['cache_enabled'] && !$force_cache)
		{
			$sql = "SELECT *
				FROM mod_joomlabridge_modules
				ORDER BY visible DESC, id ASC";
			$result = mysql_query($sql);

			while($module_details = mysql_fetch_assoc($result))
			{
				$modulelist[] = array_merge($module_details, array('smarty' => $this->getSmarty($module_details)));
			}
			mysql_free_result($result);
		}
		else
		{
			$response = $this->request(array(
				'view'		=> 'modules',
				'action'	=> 'modulelist',
			));

			if($response['status'] && sizeof($response['data']))
			{
				$delete = array();
				$count = 0;

				foreach($response['data'] as $module_details)
				{
					$delete[] = $module_details['id'];

					$modulelist[$count] = array(
						'module_id' 		=> $module_details['id'],
						'module_name' 		=> $module_details['title'],
						'module_position' 	=> $module_details['position'],
						'module_type' 		=> $module_details['module'],
					);

					$sql = "SELECT *
						FROM mod_joomlabridge_modules
						WHERE module_id = '{$module_details['id']}'";
					$result = mysql_query($sql);
					$module_cache_details = mysql_fetch_assoc($result);

					if($module_cache_details)
					{
						$sql = "UPDATE mod_joomlabridge_modules
							SET 
								module_name = '" . mysql_escape_string($module_details['title']) . "',
								module_position = '" . mysql_escape_string($module_details['position']) . "',
								module_type = '" . mysql_escape_string($module_details['module']) . "',
							WHERE id = '{$module_cache_details['id']}'";
						mysql_query($sql);

						$modulelist[$count]['id'] = $module_cache_details['id'];
						$modulelist[$count]['smarty'] = $module_cache_details['smarty'];
						$modulelist[$count]['visible'] = $module_cache_details['visible'];
					}
					else
					{
						$sql = "INSERT INTO mod_joomlabridge_modules (`module_id`,`module_name`,`module_position`,`module_type`,`smarty`) VALUES
							('" . intval($module_details['id']) . "', '" . mysql_escape_string($module_details['title']) . "', '" . mysql_escape_string($module_details['position']) . "', '" . mysql_escape_string($module_details['module']) . "','')";
						mysql_query($sql);

						$modulelist[$count]['id'] = mysql_insert_id();
						$modulelist[$count]['smarty'] = '';
						$modulelist[$count]['visible'] = 0;
					}

					$modulelist[$count]['smarty'] = $this->getSmarty($modulelist[$count]);

					$count++;
				}

				if(sizeof($delete))
				{
					$sql = "DELETE
						FROM mod_joomlabridge_modules
						WHERE module_id NOT IN('" . implode("','", $delete) . "')";
					mysql_query($sql);
				}
			}
		}

		return $modulelist;
	}

	function setVisiblity($module_id)
	{
		$output = array('status' => false, 'message' => 'Unknown Error');

		$sql = "SELECT visible
			FROM mod_joomlabridge_modules
			WHERE id = '{$module_id}'";
		$result = mysql_query($sql);
		$module_details = mysql_fetch_assoc($result);

		if($module_details)
		{
			$sql = "UPDATE mod_joomlabridge_modules
				SET visible = '" . ($module_details['visible'] ? 0 : 1) . "'
				WHERE id = '{$module_id}'";

			if(mysql_query($sql))
			{
				if(!$module_details['visible'])
				{
					$this->getModulesData($module_id, true);
				}
				else
				{
					$sql = "DELETE
						FROM mod_joomlabridge_modules_data
						WHERE module_id = '{$module_id}'";
					mysql_query($sql);
				}

				$output['status'] = true;
				$output['message'] = 'The module is now ' . ($module_details['visible'] ? 'Invisible' : 'Visible');
			}
			else
			{
				$output['message'] = 'SQL Error: ' . mysql_error();
			}
		}
		else
		{
			$output['message'] = 'The module id you provided is not exists';
		}

		return $output;
	}

	function getSmarty($module_data)
	{
		if($module_data['smarty'])
		{
			$smarty = $module_data['smarty'];
		}
		else
		{                                                                                                                               
			$smarty = $module_data['module_type'] . '_' . $module_data['module_id'];
		}

		return $smarty;
	}

	function clearCache()
	{
		$this->getModules(true);
		$this->getModulesData(null, true);
	}

	function getUsername($client_details)
	{
		if(!isset($client_details['id']))
		{
			$client_details['id'] = $client_details['userid'];
		}

		switch($this->config['username_format'])
		{
			default:
			case 0: $username = preg_replace("/[^\w\d\_]/", '', trim($client_details['firstname']) . "_{$client_details['id']}"); break;
			case 1: $username = preg_replace("/[^\w\d\_]/", '', trim($client_details['firstname']) . trim($client_details['lastname']) . "_{$client_details['id']}"); break;
			case 2: $username = preg_replace("/[^\w\d\_]/", '', trim($client_details['firstname']) . "{$client_details['id']}"); break;
			case 3: $username = preg_replace("/[^\w\d\_]/", '', trim($client_details['firstname']) . trim($client_details['lastname']) . "{$client_details['id']}"); break;
			case 4: $username = trim($client_details['email']); break;
		}

		$username = strtolower($username);

		return $username;
	}

	function login($user_id, $username, $password, $token)
	{
		$html = "";

		$iframeUrl = "{$this->config['joomla_url']}/index.php?option=com_jbridge&view=usersmanager&action=login&user_id={$user_id}&username={$username}&password={$password}&token={$token}";
		$html .= "<iframe src='{$iframeUrl}' id='joomlalogin' style='display: none;'></iframe>";
		$html .= "<script type='text/javascript'>document.getElementById('joomlalogin').onload = function() { window.location='clientarea.php'; };</script>";

		return $html;
	}

	function logout($user_id, $token)
	{
		$iframeUrl = "{$this->config['joomla_url']}/index.php?option=com_jbridge&view=usersmanager&action=logout&user_id={$user_id}&token={$token}";
		$iframe = "<iframe src='{$iframeUrl}' style='display: none;'></iframe>";

		return $iframe;
	}

	function request($params)      
	{
		$output = array('status' => false, 'data' => null, 'message' => 'Unknown Error');

		if($this->config['joomla_url'] && $this->config['joomla_admin_username'] && $this->config['joomla_admin_password'])
		{
			$params_ary = array_merge($params, array(
				'login'		=> "{$this->config['joomla_admin_username']}:{$this->config['joomla_admin_password']}",
				'option'	=> 'com_jbridge',
			));

			$params = array();

			foreach($params_ary as $param_key => $param_value)
			{
				$params[] = "{$param_key}={$param_value}";
			}

			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $this->config['joomla_url'] . '/index.php');
			curl_setopt($ch, CURLOPT_POSTFIELDS, implode("&", $params));
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$response = curl_exec($ch);

			curl_close($ch);

			logModuleCall('jbridge', $params_ary['action'], $this->config['joomla_url'] . '/index.php?' . implode("&", $params), $response, $output_decode);

			$output_decode = json_decode($response, true);

			if($output_decode !== false)
			{
				$output = $output_decode;
			}
			else
			{
				$output['data'] = $response;
				$output['message'] = 'The response is not JSON format';
			}
		}
		else
		{
			if(!$this->config['joomla_url']) $output['message'] = 'No joomla URL was provided';
			elseif(!$this->config['joomla_admin_username']) $output['message'] = 'No joomla admin username was provided';
			elseif(!$this->config['joomla_admin_password']) $output['message'] = 'No joomla admin password was provided';
		}

		return $output;
	}
}
?>