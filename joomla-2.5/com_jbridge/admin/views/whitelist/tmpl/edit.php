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

<form action="<?php echo JRoute::_('index.php?option=com_jbridge&view=whitelist');?>" method="post" name="adminForm" id="adminForm">

	<div class="width-100">
	<fieldset class="adminform">
	<legend><?php echo JText::_($this->isNew ? 'COM_JBRIDGE_ADMINISTRATOR_WHITELIST_ADDNEW_LABEL' : 'COM_JBRIDGE_ADMINISTRATOR_WHITELIST_EDIT_LABEL'); ?></legend>
	<ul class="adminformlist">
		<li>
			<label class="required" for="ipaddress" id="ipaddress-lbl"><?php echo JText::_('COM_JBRIDGE_IP_ADDRESS'); ?><span class="star">&nbsp;*</span></label>
			<input type="text" size="30" class="inputbox required" value="<?php echo $this->item_details['ip']; ?>" id="ipaddress" name="ipaddress" />
		</li>
		<li>
			<label for="expiry" id="expiry-lbl"><?php echo JText::_('COM_JBRIDGE_EXPIRATION_DATE'); ?></label>
			<input type="text" class="inputbox" size="22" value="<?php echo ($this->item_details['expiry'] ? date("Y-m-d H:i:s", $this->item_details['expiry']) : ''); ?>" id="expiry_calendar" name="expiry" title="" />
			<img id="expiry_calendar_img" class="calendar" alt="Calendar" src="/administrator/templates/bluestork/images/system/calendar.png" />
		</li>
	</ul>
	</fieldset>
	</div>

	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="id" value="<?php echo $this->item_id; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>