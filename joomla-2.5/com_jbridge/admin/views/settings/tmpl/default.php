<?php
/**
 * @version	$Id: default.php 20196 2012-02-23 12:00:00Z idanbe $
 * @package	Joomla.Administrator
 * @subpackage	com_jbridge
 * @copyright	Copyright (C) Jetserver.
 */

// No direct access.
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');

?>

<form action="<?php echo JRoute::_('index.php?option=com_jbridge&view=settings');?>" method="post" name="adminForm" id="adminForm">

	<div class="width-100">
	<fieldset class="adminform">
	<legend><?php echo JText::_('COM_JBRIDGE_ADMINISTRATOR_GENERAL_SETTINGS'); ?></legend>
	<ul class="adminformlist">
		<li>
			<label for="usersyncenabled" id="usersyncenabled-lbl"><?php echo JText::_('COM_JBRIDGE_USER_SYNC_ENABLED'); ?></label>
			<select name="config[usersyncenabled]" id="usersyncenabled">
				<option value="1"<?php if($this->config['usersyncenabled']) { ?> selected="selected"<?php } ?>>Yes</option>
				<option value="0"<?php if(!$this->config['usersyncenabled']) { ?> selected="selected"<?php } ?>>No</option>
			</select>
		</li>
		<li>
			<label for="modulesyncenabled" id="modulesyncenabled-lbl"><?php echo JText::_('COM_JBRIDGE_MODULE_SYNC_ENABLED'); ?></label>
			<select name="config[modulesyncenabled]" id="modulesyncenabled">
				<option value="1"<?php if($this->config['modulesyncenabled']) { ?> selected="selected"<?php } ?>>Yes</option>
				<option value="0"<?php if(!$this->config['modulesyncenabled']) { ?> selected="selected"<?php } ?>>No</option>
			</select>
		</li>
		<li>
			<label for="userloginenabled" id="userloginenabled-lbl"><?php echo JText::_('COM_JBRIDGE_USER_LOGIN_ENABLED'); ?></label>
			<select name="config[userloginenabled]" id="userloginenabled">
				<option value="1"<?php if($this->config['userloginenabled']) { ?> selected="selected"<?php } ?>>Yes</option>
				<option value="0"<?php if(!$this->config['userloginenabled']) { ?> selected="selected"<?php } ?>>No</option>
			</select>
		</li>
	</ul>
	</fieldset>
	</div>

	<div>
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>