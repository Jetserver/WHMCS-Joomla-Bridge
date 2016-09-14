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

<form action="<?php echo JRoute::_('index.php?option=com_jbridge&view=whitelist');?>" method="post" name="adminForm" id="adminForm">

<fieldset class="form-horizontal">
	<legend><?php echo JText::_('COM_JBRIDGE_ADMINISTRATOR_WHITELIST'); ?></legend>

	<table class="table table-striped">
	<thead>
	<tr>
		<th width="1%" class="nowrap center"><input type="checkbox" onclick="Joomla.checkAll(this)" class="hasTooltip" value="" name="checkall-toggle" title="Check All" /></th>
		<th class="left"><?php echo JText::_('COM_JBRIDGE_IP_ADDRESS'); ?></th>
		<th class="left"><?php echo JText::_('COM_JBRIDGE_EXPIRATION_DATE'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach($this->whitelist_items as $i => $whitelist_item) { ?>
	<tr class="row<?php echo $i; ?>">
		<td class="center">
			<input type="checkbox" title="Checkbox for row <?php echo ($i+1); ?>" onclick="Joomla.isChecked(this.checked);" value="<?php echo $whitelist_item['id']; ?>" name="wid[]" id="cb<?php echo $i; ?>" />
		</td>
		<td><a href="<?php echo JRoute::_('index.php?option=com_jbridge&view=whitelist&layout=edit&id=' . $whitelist_item['id']);?>"><?php echo $whitelist_item['ip']; ?></a></td>
		<td><?php echo ($whitelist_item['expiry'] ? date("Y-m-d H:i:s", $whitelist_item['expiry']) : 'Never'); ?></td>
	</tr>
	<?php } ?>
	</tbody>
	</table>

	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</fieldset>

</form>