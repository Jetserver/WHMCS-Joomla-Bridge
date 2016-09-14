<?php
/**
 * @version     1.0.0
 * @package     com_jbridge
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Idan Ben-Ezra <admin@jetserver.co.il> - http://www.jetserver.net
 */


// no direct access
defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_jbridge')) 
{
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));

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
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();