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
class JbridgeViewUsersmanager extends JViewLegacy 
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

		if(!$config['usersyncenabled'])
		{
			echo json_encode(array(
				'status'	=> false,
				'message'	=> 'Users Sync is disabled in the system',
			));
			exit;
		}

		$action 	= JRequest::getVar('action', '');
		$userid 	= JRequest::getInt('user_id', 0);
		$name 		= JRequest::getVar('name', '');
		$email 		= JRequest::getVar('email', '');
		$username 	= JRequest::getVar('username', '');
		$password 	= JRequest::getVar('password', '');
		$enabled 	= JRequest::getInt('enabled', 0);

		switch($action)
		{
			case 'token':

				if(!$userid)
				{
					echo json_encode(array(
						'status'	=> false,
						'message'	=> 'You didn\'t provided user id',
					));
					exit;
				}

				$sql = "DELETE
					FROM #__jbridge_tokens
					WHERE user_id = '{$userid}'";
				$db->setQuery($sql);
				$db->query();

				$token = sha1(rand() . time() . $userid);

				$sql = "INSERT INTO #__jbridge_tokens (`token`,`user_id`,`time`) VALUES
					('{$token}', '{$userid}', '" . time() . "')";
				$db->setQuery($sql);

				if($db->query())
				{
					echo json_encode(array(
						'status'	=> true,
						'token'		=> $token,
						'message'	=> 'Token created successfull.',
					));
					exit;
				}
				else
				{
					echo json_encode(array(
						'status'	=> false,
						'message'	=> 'Can\'t add token. SQL Error: ' . $db->getErrorMsg(),
					));
					exit;
				}

			break;

			case 'logout':

				$mainframe = JFactory::getApplication();
				$session = JFactory::getSession();
				$session->set('nopopup', true);

				$logout = $mainframe->logout();

				if($logout)
				{
					echo json_encode(array(
						'status'	=> true,
						'message'	=> 'Logged out successfull.',
					));
					exit;
				}
				else
				{
					$messages = $mainframe->getMessageQueue();

					echo json_encode(array(
						'status'	=> false,
						'message'	=> $messages[0]['message'],
					));
					exit;
				}

			break;

			case 'login':

				if($username && $password)
				{
					$mainframe = JFactory::getApplication();
					$session = JFactory::getSession();
					$session->set('nopopup', true);

					$login = $mainframe->login(array(
						'username' 	=> $username, 
						'password' 	=> $password, 
					));

					if($login)
					{
						echo json_encode(array(
							'status'	=> true,
							'message'	=> 'Logged in successfull.',
						));
						exit;
					}
					else
					{
						$messages = $mainframe->getMessageQueue();

						echo json_encode(array(
							'status'	=> false,
							'message'	=> $messages[0]['message'],
						));
						exit;
					}
				}
				else
				{
					if(!$username) $error = 'You didn\'t provided username';
					if(!$password) $error = 'You didn\'t provided password';

					echo json_encode(array(
						'status'	=> false,
						'message'	=> $error,
					));
					exit;
				}



			break;

			case 'disable':

				if(!$userid)
				{
					echo json_encode(array(
						'status'	=> false,
						'message'	=> 'You didn\'t provided user id',
					));
					exit;
				}

				$user = JFactory::getUser($userid);

				if($user->id == $userid)
				{
					$user->set('block', '1');
					$user->save();

					echo json_encode(array(
						'status'	=> true,
						'message'	=> 'User disabled successfull.',
					));
					exit;
				}
				else
				{
					echo json_encode(array(
						'status'	=> false,
						'message'	=> 'The provided user id is not exists',
					));
					exit;
				}

			break;

			case 'delete':

				if(!$userid)
				{
					echo json_encode(array(
						'status'	=> false,
						'message'	=> 'You didn\'t provided user id',
					));
					exit;
				}

				$instance = JUser::getInstance($userid);

				if($instance->delete())
				{
					echo json_encode(array(
						'status'	=> true,
						'message'	=> 'User deleted successfull.',
					));
					exit;
				}
				else
				{
					echo json_encode(array(
						'status'	=> false,
						'message'	=> 'Can\'t delete this user',
					));
					exit;
				}
			break;

			case 'changepwd':

				if(!$userid)
				{
					echo json_encode(array(
						'status'	=> false,
						'message'	=> 'You didn\'t provided user id',
					));
					exit;
				}

				$user = JFactory::getUser($userid);

				if($user->id == $userid)
				{
					if($password)
					{
						$bind = array(
							'password'	=> $password,
							'password2'	=> $password,
						);

						if(!$user->bind($bind, 'usertype'))
						{
							echo json_encode(array(
								'status'	=> false,
								'message'	=> $user->getError(),
							));
							exit;
						}

						$user->save();

						echo json_encode(array(
							'status'	=> true,
							'message'	=> 'Password changed successfull.',
						));
						exit;
					}
					else
					{
						echo json_encode(array(
							'status'	=> false,
							'message'	=> 'You didn\'t provided password',
						));
						exit;
					}
				}
				else
				{
					echo json_encode(array(
						'status'	=> false,
						'message'	=> 'The provided user id is not exists',
					));
					exit;
				}

			break;

			case 'edit':

				if(!$userid)
				{
					echo json_encode(array(
						'status'	=> false,
						'message'	=> 'You didn\'t provided user id',
					));
					exit;
				}

				$user = JFactory::getUser($userid);

				if($user->id == $userid)
				{
					if($name && $username && $email)
					{
						$bind = array(
							'name'		=> $name,
							'email'		=> $email,
							'username'	=> $username,
						);

						if($password)
						{
							$bind = array_merge($bind, array(
								'password'	=> $password,
								'password2'	=> $password,
							));
						}

						if(!$user->bind($bind, 'usertype'))
						{
							echo json_encode(array(
								'status'	=> false,
								'message'	=> $user->getError(),
							));
							exit;
						}

						$user->set('block', $enabled ? '0' : '1');
						$user->save();

						echo json_encode(array(
							'status'	=> true,
							'message'	=> 'User details edited successfull.',
						));
						exit;
					}
					else
					{
						if(!$name) $error = 'You didn\'t provided name';
						if(!$email) $error = 'You didn\'t provided email';
						if(!$username) $error = 'You didn\'t provided username';

						echo json_encode(array(
							'status'	=> false,
							'message'	=> $error,
						));
						exit;
					}
				}
				else
				{
					echo json_encode(array(
						'status'	=> false,
						'message'	=> 'The provided user id is not exists',
					));
					exit;
				}

			break;

			case 'register':

				if($name && $username && $email && $password)
				{
					$user		= clone(JFactory::getUser());
					$usersConfig 	= JComponentHelper::getParams('com_users');

					$bind = array(
						'name'		=> $name,
						'email'		=> $email,
						'username'	=> $username,
						'password'	=> $password,
						'password2'	=> $password,
					);

					if(!$user->bind($bind, 'usertype'))
					{
						echo json_encode(array(
							'status'	=> false,
							'message'	=> $user->getError(),
						));
						exit;
					}

					$user->set('id', 0);
					$user->set('usertype', '');
					if($config['defaultgroup']) $user->set('groups', array($config['defaultgroup']));

					$user->save();

					$user_id = $user->get('id');
				
					if($user_id)
					{
						$user_group = $usersConfig->get('new_usertype');

						if(JUserHelper::addUserToGroup($user_id, $user_group))
						{
							echo json_encode(array(
								'status'	=> true,
								'userid'	=> $user_id,
								'message'	=> 'User created successfull.',
							));
							exit;
						}
						else
						{
							echo json_encode(array(
								'status'	=> false,
								'message'	=> 'Can\'t add the user to his group',
							));
							exit;
						}
					}
					else
					{
						echo json_encode(array(
							'status'	=> false,
							'message'	=> $user->getError(),
						));
						exit;
					}
				}
				else
				{
					if(!$name) $error = 'You didn\'t provided name';
					if(!$email) $error = 'You didn\'t provided email';
					if(!$username) $error = 'You didn\'t provided username';
					if(!$password) $error = 'You didn\'t provided password';

					echo json_encode(array(
						'status'	=> false,
						'message'	=> $error,
					));
					exit;
				}
			break;

			default:
				echo json_encode(array(
					'status'	=> false,
					'message'	=> 'Invalid action provided',
				));
				exit;
			break;
		}

		parent::display($tpl);
	}
}

?>