<?php

// No direct access
defined('_JEXEC') or die();
jimport('joomla.application.component.modeladmin');

class JbridgeModelSettings extends JModelAdmin
{
	protected $text_prefix = 'COM_JBRIDGE';
	
	public function getTable($type = 'settings', $prefix = 'JbridgeTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_jbridge.config', 'settings', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form)) {
			return false;
		}
		
		// Modify the form based on access controls.
		if (!$this->canEditState((object) $data)) {
			// Disable fields for display.
			$form->setFieldAttribute('published', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is a record you can edit.
			$form->setFieldAttribute('published', 'filter', 'unset');
		}

		return $form;
	}
}
?>