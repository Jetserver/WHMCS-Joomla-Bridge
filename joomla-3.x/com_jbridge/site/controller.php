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

jimport('joomla.application.component.controller');

class JbridgeController extends JControllerLegacy
{
	public function display($cachable = false, $urlparams = false)
	{
		$vName = JFactory::getApplication()->input->getCmd('view', 'modules');
		JFactory::getApplication()->input->set('view', $vName);

		parent::display();
	}
}