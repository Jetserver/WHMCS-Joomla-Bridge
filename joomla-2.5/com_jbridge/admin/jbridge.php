<?php
/**
 * @version	$Id: jbridge.php 20196 2012-02-23 12:00:00Z idanbe $
 * @package	Joomla.Administrator
 * @subpackage	com_jbridge
 * @copyright	Copyright (C) Jetserver.
 */

defined('_JEXEC') or die;

// Include dependancies
jimport('joomla.application.component.controller');

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_jbridge')) 
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

// Require helper file
JLoader::register('JbridgeHelper', dirname(__FILE__) . '/helpers/jbridge.php');

if($controller = JRequest::getVar('view', 'settings')) 
{                                          
	$path = JPATH_COMPONENT . '/controllers/' . $controller . '.php';

	if (file_exists($path)) 
	{
		require_once($path);
	} 
	else 
	{
		$controller = '';
		require_once(dirname(__FILE__) . '/controller.php');
	}
}

// Create the controller
$classname    = 'JbridgeController' . ucfirst($controller);
$controller   = new $classname();
$controller->execute(JRequest::getVar('task',''));
$controller->redirect();

?>