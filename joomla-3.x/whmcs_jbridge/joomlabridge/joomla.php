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
				SET value = '" . mysql_real_escape_string($value) . "'
				WHERE name = '{$key}'";
			mysql_query($sql);
		}
		else
		{
			$sql = "INSERT INTO mod_joomlabridge_config (`name`,`value`) VALUES
				('" . mysql_real_escape_string($key) . "','" . mysql_real_escape_string($value) . "')";
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
		$output = array('success' => false, 'data' => ($module_id ? '' : array()), 'message' => '');

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

				$module_content = $module_details['module_content'];

				if(!$module_content)
				{
					$getModulesData = $this->getModulesData($module_id, true);

					if($getModulesData['success'])
					{
						$module_content = $getModulesData['data'];
					}
					else
					{
						$output['message'] = $getModulesData['message'];
						return $output;
					}
				}

				$output['data'] = $this->filterModulesData($module_id, $module_content);
			}
			else
			{
				while($module_details = mysql_fetch_assoc($result))
				{
					$module_content = $module_details['module_content'];

					if(!$module_content)
					{
						$getModulesData = $this->getModulesData($module_details['module_id'], true);

						if($getModulesData['success'])
						{
							$module_content = $getModulesData['data'];
						}
						else
						{
							$output['message'] = $getModulesData['message'];
							return $output;
						}
					}

					$output['data'][$module_details['module_id']] = $this->filterModulesData($module_details['module_id'], $module_content);
				}
				mysql_free_result($result);
			}

			$output['success'] = true;
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
								SET module_content = '" . trim(mysql_real_escape_string($response['data'])) . "'
								WHERE id = '{$module_cache_details['id']}'";
							mysql_query($sql);
						}
						else
						{
							$sql = "INSERT INTO mod_joomlabridge_modules_data (`module_id`,`module_content`) VALUES
								('" . intval($module_id) . "', '" . trim(mysql_real_escape_string($response['data'])) . "')";
							mysql_query($sql);
						}

						$output['data'] = $this->filterModulesData($module_id, $response['data']);
					}

					$output['success'] = true;
				}
				else
				{
					$output['message'] = "The provided module not exists";
				}
			}
			else
			{
				$delete = array();

				$modules = $this->getModules();

				if($modules['success'])
				{
					foreach($modules['data'] as $module)
					{
						$response = $this->request(array(
							'view'		=> 'modules',
							'action'	=> 'moduledata',
							'module_id'	=> $module['module_id'],
						));

						if($response['status'])
						{
							if($response['data'])
							{
								$sql = "SELECT id
									FROM mod_joomlabridge_modules_data
									WHERE module_id = '{$module['id']}'";
								$result = mysql_query($sql);
								$module_cache_details = mysql_fetch_assoc($result);

								if($module_cache_details)
								{
									$sql = "UPDATE mod_joomlabridge_modules_data
										SET module_content = '" . mysql_real_escape_string($response['data']) . "'
										WHERE id = '{$module_cache_details['id']}'";
									mysql_query($sql);
								}
								else
								{
									$sql = "INSERT INTO mod_joomlabridge_modules (`module_id`,`module_content`) VALUES
										('" . intval($module['id']) . "', '" . mysql_real_escape_string($response['data']) . "')";
									mysql_query($sql);
								}

								$delete[] = $module['id'];

								if($module['visible']) $output['data'][$module['id']] = $this->filterModulesData($module['id'], $response['data']);
							}
						}
						else
						{
							$output['message'] = $response['message'];
							return $output;
						}
					}

					$output['success'] = true;
				}
				else
				{
					$output['message'] = $modules['message'];
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
		$output = array('success' => false, 'data' => array(), 'message' => '');

		if($this->config['cache_enabled'] && !$force_cache)
		{
			$sql = "SELECT *
				FROM mod_joomlabridge_modules
				ORDER BY visible DESC, id ASC";
			$result = mysql_query($sql);

			while($module_details = mysql_fetch_assoc($result))
			{
				$output['data'][] = array_merge($module_details, array('smarty' => $this->getSmarty($module_details)));
			}
			mysql_free_result($result);

			$output['success'] = true;
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

					$output['data'][$count] = array(
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
								module_name = '" . mysql_real_escape_string($module_details['title']) . "',
								module_position = '" . mysql_real_escape_string($module_details['position']) . "',
								module_type = '" . mysql_real_escape_string($module_details['module']) . "'
							WHERE id = '{$module_cache_details['id']}'";
						mysql_query($sql);

						$output['data'][$count]['id'] = $module_cache_details['id'];
						$output['data'][$count]['smarty'] = $module_cache_details['smarty'];
						$output['data'][$count]['visible'] = $module_cache_details['visible'];
					}
					else
					{
						$sql = "INSERT INTO mod_joomlabridge_modules (`module_id`,`module_name`,`module_position`,`module_type`,`smarty`) VALUES
							('" . intval($module_details['id']) . "', '" . mysql_real_escape_string($module_details['title']) . "', '" . mysql_real_escape_string($module_details['position']) . "', '" . mysql_real_escape_string($module_details['module']) . "','')";
						mysql_query($sql);

						$output['data'][$count]['id'] = mysql_insert_id();
						$output['data'][$count]['smarty'] = '';
						$output['data'][$count]['visible'] = 0;
					}

					$output['data'][$count]['smarty'] = $this->getSmarty($output['data'][$count]);

					$count++;
				}

				if(sizeof($delete))
				{
					$sql = "DELETE
						FROM mod_joomlabridge_modules
						WHERE module_id NOT IN('" . implode("','", $delete) . "')";
					mysql_query($sql);
				}

				usort($output['data'], array($this, 'visibleSort'));

				$output['success'] = true;
			}
			else
			{
				$output['message'] = $response['message'];
			}
		}

		return $output;
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
					$getModulesData = $this->getModulesData($module_id, true);

					if(!$getModulesData['success'])
					{
						$output['message'] = $getModulesData['message'];
						return $output;
					}
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
		$output = array('success' => false, 'message' => '');

		$getModules = $this->getModules(true);

		if($getModules['success'])
		{
			$getModulesData = $this->getModulesData(null, true);

			if($getModulesData['success'])
			{
				$output['success'] = true;
			}
			else
			{
				$output['message'] = $getModulesData['message'];
			}
		}
		else
		{
			$output['message'] = $getModules['message'];
		}

		return $output;
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
		$html .= "<html><head></head><body>\n";

		$html .= "<iframe src='{$iframeUrl}' id='joomlalogin' style='display: none;'></iframe>\n";

		if($_POST && strpos($_SERVER['REQUEST_URI'], 'dologin') === false)
		{
			$html .= "<form method='post' id='loginForm' action='{$_SERVER['REQUEST_URI']}'>\n";

			foreach($_POST as $key => $value)
			{
				if(in_array($key, array('submit'))) continue;
				$html .= "<input type='hidden' name='{$key}' value='{$value}' />\n";
			}

			$html .= "<input type='submit' name='submit' value='true' style='display: none;' />\n";
			$html .= "</form>\n";

			$html .= "<script type='text/javascript'>
					var joomlalogin = document.getElementById('joomlalogin');
					var loginform = document.getElementById('loginForm');
					joomlalogin.onload = function() { loginform.submit.click(); };
				  </script>";
		}
		else
		{
			$html .= "<script type='text/javascript'>
					document.getElementById('joomlalogin').onload = function() 
					{ 
						window.location='" . (strpos($_SERVER['REQUEST_URI'], 'dologin') === false ? $_SERVER['REQUEST_URI'] : 'clientarea.php') . "'; 
					};
				</script>";
		}

		$html .= "</body></html>\n";

		return $html;
	}

	function logout($user_id, $token)
	{
		$iframeUrl = "{$this->config['joomla_url']}/index.php?option=com_jbridge&view=usersmanager&action=logout&user_id={$user_id}&token={$token}";
		$iframe = "<iframe src='{$iframeUrl}' style='display: none;'></iframe>";

		return $iframe;
	}

	function visibleSort($a, $b) 
	{
		if ($a['visible'] == $b['visible']) 
		{
			return 0;
		}

		return ($a['visible'] < $b['visible']) ? 1 : -1;
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
			curl_setopt($ch, CURLOPT_HEADER, 1);

			$response = curl_exec($ch);

			if($response === false)
			{
				$output['message'] = "CURL Error: " . curl_error($ch);
				return $output;
			}

			$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
			$header = substr($response, 0, $header_size);
			$headers = explode("\n", $header);
			$response = substr($response, $header_size);

			curl_close($ch);

			logModuleCall('jbridge', $params_ary['action'], $this->config['joomla_url'] . '/index.php?' . implode("&", $params), $header . ' ' . $response, $output_decode);

			if(strpos($header, '200 OK') !== false)
			{
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
				$output['data'] = $response;
				$output['message'] = 'We got HTTP Error: ' . $headers[0] . '<br />For more information please go to the <a href="systemmodulelog.php">Module Logs</a>';
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