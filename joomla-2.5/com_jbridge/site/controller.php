<?php
/**
 * @version	$Id: controller.php 20196 2012-02-23 12:00:00Z idanbe $
 * @package	Joomla.Administrator
 * @subpackage	com_jbridge
 * @copyright	Copyright (C) Jetserver.
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');
 
class JbridgeController extends JController
{
	public function display()
	{
		$vName = JRequest::getCmd('view', 'modules');
		JRequest::setVar('view', $vName);

		parent::display();
	}
}

?>