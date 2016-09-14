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
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');


?>

<form action="<?php echo JRoute::_('index.php?option=com_jbridge&view=settings');?>" method="post" name="adminForm" id="adminForm" class="form-horizontal">

<fieldset class="form-horizontal">
	<legend><?php echo JText::_('COM_JBRIDGE_ADMINISTRATOR_SETTINGS'); ?></legend>

	<div class="control-group">
		<div class="control-label">
			<label class="hasTooltip" for="accessgroup" id="accessgroup-lbl" title="<strong><?php echo JText::_('COM_JBRIDGE_ACCESS_GROUP'); ?></strong><br /><?php echo JText::_('COM_JBRIDGE_ACCESS_GROUP_DESC'); ?>"><?php echo JText::_('COM_JBRIDGE_ACCESS_GROUP'); ?></label>
		</div>
		<div class="controls">
			<select id="accessgroup" name="config[accessgroup]">
				<option value="0">Select User Group</option>
				<?php foreach($this->groups as $group_details) { ?>
				<option value="<?php echo $group_details['id']; ?>" <?php if(isset($this->config['accessgroup']) && $this->config['accessgroup'] == $group_details['id']) { ?> selected="selected"<?php } ?>><?php echo $group_details['title']; ?></option>
				<?php } ?>
			</select>
		</div>
	</div>

	<div class="control-group">
		<div class="control-label">
			<label class="hasTooltip" for="defaultgroup" id="defaultgroup-lbl" title="<strong><?php echo JText::_('COM_JBRIDGE_DEFAULT_GROUP'); ?></strong><br /><?php echo JText::_('COM_JBRIDGE_DEFAULT_GROUP_DESC'); ?>"><?php echo JText::_('COM_JBRIDGE_DEFAULT_GROUP'); ?></label>
		</div>
		<div class="controls">
			<select id="defaultgroup" name="config[defaultgroup]">
				<option value="0">Select Group</option>
				<?php foreach($this->groups as $group_details) { ?>
				<option value="<?php echo $group_details['id']; ?>" <?php if(isset($this->config['defaultgroup']) && $this->config['defaultgroup'] == $group_details['id']) { ?> selected="selected"<?php } ?>><?php echo $group_details['title']; ?></option>
				<?php } ?>
			</select>
		</div>
	</div>

	<div class="control-group">
		<div class="control-label">
			<label class="hasTooltip" for="usersyncenabled" id="usersyncenabled-lbl" title="<strong><?php echo JText::_('COM_JBRIDGE_USER_SYNC_ENABLED'); ?></strong><br />Allow user integration."><?php echo JText::_('COM_JBRIDGE_USER_SYNC_ENABLED'); ?></label>
		</div>
		<div class="controls">
			<fieldset class="radio btn-group" id="usersyncenabled">
				<input type="radio"<?php if(!$this->config['usersyncenabled']) { ?> checked="checked"<?php } ?> value="0" name="config[usersyncenabled]" id="usersyncenabled0" />
				<label for="usersyncenabled0">No</label>
				<input type="radio" <?php if($this->config['usersyncenabled']) { ?> checked="checked"<?php } ?> value="1" name="config[usersyncenabled]" id="usersyncenabled1" />
				<label for="usersyncenabled1">Yes</label>
			</fieldset>
		</div>
	</div>

	<div class="control-group">
		<div class="control-label">
			<label class="hasTooltip" for="modulesyncenabled" id="modulesyncenabled-lbl" title="<strong><?php echo JText::_('COM_JBRIDGE_MODULE_SYNC_ENABLED'); ?></strong><br />Allow Joomla modules share."><?php echo JText::_('COM_JBRIDGE_MODULE_SYNC_ENABLED'); ?></label>
		</div>
		<div class="controls">
			<fieldset class="radio btn-group" id="modulesyncenabled">
				<input type="radio"<?php if(!$this->config['modulesyncenabled']) { ?>  checked="checked"<?php } ?> value="0" name="config[modulesyncenabled]" id="modulesyncenabled0" />
				<label for="modulesyncenabled0">No</label>
				<input type="radio"<?php if($this->config['modulesyncenabled']) { ?>  checked="checked"<?php } ?> value="1" name="config[modulesyncenabled]" id="modulesyncenabled1" />
				<label for="modulesyncenabled1">Yes</label>
			</fieldset>
		</div>
	</div>

	<div class="control-group">
		<div class="control-label">
			<label class="hasTooltip" for="userloginenabled" id="userloginenabled-lbl" title="<strong><?php echo JText::_('COM_JBRIDGE_USER_LOGIN_ENABLED'); ?></strong><br />Allow login share."><?php echo JText::_('COM_JBRIDGE_USER_LOGIN_ENABLED'); ?></label>
		</div>
		<div class="controls">
			<fieldset class="radio btn-group" id="userloginenabled">
				<input type="radio"<?php if(!$this->config['userloginenabled']) { ?>  checked="checked"<?php } ?> value="0" name="config[userloginenabled]" id="userloginenabled0" />
				<label for="userloginenabled0">No</label>
				<input type="radio"<?php if($this->config['userloginenabled']) { ?>  checked="checked"<?php } ?> value="1" name="config[userloginenabled]" id="userloginenabled1" />
				<label for="userloginenabled1">Yes</label>
			</fieldset>
		</div>
	</div>

	<div>
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>

</fieldset>

</form>