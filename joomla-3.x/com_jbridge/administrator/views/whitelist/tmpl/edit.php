<?php
/**
 * @version	$Id: edit.php 20196 2012-02-23 12:00:00Z idanbe $
 * @package	Joomla.Administrator
 * @subpackage	com_jbridge
 * @copyright	Copyright (C) Jetserver.
 */

// No direct access.
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
JHtml::_('behavior.calendar');  
?>

<form action="<?php echo JRoute::_('index.php?option=com_jbridge&view=whitelist');?>" method="post" name="adminForm" id="adminForm" class="form-horizontal">

<fieldset class="form-horizontal">
	<legend><?php echo JText::_($this->isNew ? 'COM_JBRIDGE_ADMINISTRATOR_WHITELIST_ADDNEW' : 'COM_JBRIDGE_ADMINISTRATOR_WHITELIST_EDIT'); ?></legend>

	<div class="control-group">
		<div class="control-label">
			<label class="hasTooltip" for="ipaddress" id="ipaddress-lbl" title="<strong><?php echo JText::_('COM_JBRIDGE_IP_ADDRESS'); ?></strong><br />Specify IP to communicate with Joomla Bridge.<br />IPv4 Only (e.g, 1.1.1.1)."><?php echo JText::_('COM_JBRIDGE_IP_ADDRESS'); ?></label>
		</div>
		<div class="controls">
			<input type="text" size="30" class="inputbox required" value="<?php echo $this->item_details['ip']; ?>" id="ipaddress" name="ipaddress" />
		</div>
	</div>

	<div class="control-group">
		<div class="control-label">
			<label class="hasTooltip" for="expiry" id="expiry-lbl" title="<strong><?php echo JText::_('COM_JBRIDGE_EXPIRATION_DATE'); ?></strong><br />Leave empty to never expire."><?php echo JText::_('COM_JBRIDGE_EXPIRATION_DATE'); ?></label>
		</div>
		<div class="controls">
			<div class="input-append">
				<input type="text" class="hasTooltip" title="" name="expiry" id="expiry_calendar" value="<?php echo ($this->item_details['expiry'] ? date("Y-m-d H:i:s", $this->item_details['expiry']) : ''); ?>" size="22" class="inputbox" />
				<button class="btn" id="expiry_calendar_img"><i class="icon-calendar"></i></button>
			</div>
		</div>
	</div>

	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="id" value="<?php echo $this->item_id; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</fieldset>

</form>