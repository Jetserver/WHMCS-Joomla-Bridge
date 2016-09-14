<?php

if (!defined("WHMCS"))
	die("This file cannot be accessed directly");

function joomlabridge_config() 
{
	return array(
		"name" 		=> "Jetserver Joomla Bridge",
		"description" 	=> "",
		"version" 	=> "1.0.0",
		"author" 	=> "Idan Ben-Ezra",
		"language" 	=> "english",
	);
}

function joomlabridge_activate() 
{
	$sql = "CREATE TABLE IF NOT EXISTS `mod_joomlabridge_clients` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`client_id` int(11) unsigned NOT NULL DEFAULT '0',
			`joomla_id` int(11) unsigned NOT NULL DEFAULT '0',
		PRIMARY KEY (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
	$result = mysql_query($sql);

	if(!$result) $error[] = "Can't create the table `mod_joomlabridge_clients`. SQL Error: " . mysql_error();

	$sql = "CREATE TABLE IF NOT EXISTS `mod_joomlabridge_config` (
			`name` varchar(255) NOT NULL,
			`value` text NOT NULL,
		PRIMARY KEY (`name`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
	$result = mysql_query($sql);

	if($result)
	{
		$sql = "INSERT INTO `mod_joomlabridge_config` (`name`, `value`) VALUES
			('cache_enabled', '1'),
			('joomla_admin_password', ''),
			('joomla_admin_username', ''),
			('joomla_url', ''),
			('sync_enabled', '0'),
			('username_format', '3')";
		$result = mysql_query($sql);

		if(!$result) $error[] = "Can't insert data into the table `mod_joomlabridge_config`. SQL Error: " . mysql_error();
	}
	else
	{
		if(!$result) $error[] = "Can't create the table `mod_joomlabridge_config`. SQL Error: " . mysql_error();
	}

	$sql = "CREATE TABLE IF NOT EXISTS `mod_joomlabridge_modules` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`module_id` int(11) unsigned NOT NULL DEFAULT '0',
			`module_name` varchar(255) NOT NULL,
			`module_position` varchar(255) NOT NULL,
			`module_type` varchar(255) NOT NULL,
			`smarty` varchar(255) NOT NULL DEFAULT '',
			`visible` tinyint(1) unsigned NOT NULL DEFAULT '0',
		PRIMARY KEY (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
	$result = mysql_query($sql);

	if(!$result) $error[] = "Can't create the table `mod_joomlabridge_modules`. SQL Error: " . mysql_error();

	$sql = "CREATE TABLE IF NOT EXISTS `mod_joomlabridge_modules_attrs` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`module_id` int(11) unsigned NOT NULL DEFAULT '0',
			`attr_find` varchar(255) NOT NULL,
			`attr_replace` varchar(255) NOT NULL,
			`attr_order` int(11) unsigned NOT NULL DEFAULT '0',
		PRIMARY KEY (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
	$result = mysql_query($sql);

	if(!$result) $error[] = "Can't create the table `mod_joomlabridge_modules_attrs`. SQL Error: " . mysql_error();

	$sql = "CREATE TABLE IF NOT EXISTS `mod_joomlabridge_modules_data` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`module_id` int(11) unsigned NOT NULL DEFAULT '0',
			`module_content` text NOT NULL,
		PRIMARY KEY (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
	$result = mysql_query($sql);

	if(!$result) $error[] = "Can't create the table `mod_joomlabridge_modules_data`. SQL Error: " . mysql_error();

	if(sizeof($error))
	{
		joomlabridge_deactivate();
	}

	return array(
		'status'	=> sizeof($error) ? 'error' : 'success',
		'description'	=> sizeof($error) ? implode(" -> ", $error) : '',
	);
}

function joomlabridge_deactivate() 
{
	$sql = "DROP TABLE IF EXISTS `mod_joomlabridge_clients`";
	$result = mysql_query($sql);

	if(!$result) $error[] = "Can't drop the table `mod_joomlabridge_clients`. SQL Error: " . mysql_error();

	$sql = "DROP TABLE IF EXISTS `mod_joomlabridge_config`";
	$result = mysql_query($sql);

	if(!$result) $error[] = "Can't drop the table `mod_joomlabridge_config`. SQL Error: " . mysql_error();

	$sql = "DROP TABLE IF EXISTS `mod_joomlabridge_modules`";
	$result = mysql_query($sql);

	if(!$result) $error[] = "Can't drop the table `mod_joomlabridge_modules`. SQL Error: " . mysql_error();

	$sql = "DROP TABLE IF EXISTS `mod_joomlabridge_modules_attrs`";
	$result = mysql_query($sql);

	if(!$result) $error[] = "Can't drop the table `mod_joomlabridge_modules_attrs`. SQL Error: " . mysql_error();

	$sql = "DROP TABLE IF EXISTS `mod_joomlabridge_modules_data`";
	$result = mysql_query($sql);

	if(!$result) $error[] = "Can't drop the table `mod_joomlabridge_modules_data`. SQL Error: " . mysql_error();

	return array(
		'status'	=> sizeof($error) ? 'error' : 'success',
		'description'	=> sizeof($error) ? implode(" -> ", $error) : '',
	);
}

function joomlabridge_upgrade($vars) 
{
	$version = $vars['version'];
}

function joomlabridge_output($vars) 
{
	$modulelink = $vars['modulelink'];
	$version = $vars['version'];

	if(!class_exists('joomlaBridge'))
	{
		include(dirname(__FILE__) . '/joomla.php');
	}

	$joomlaBridge = new joomlaBridge;

	$page = $_REQUEST['page'];
	$pages = array('modules' => true,'modulesmanage' => false,'settings' => true);
	$page = in_array($page, array_keys($pages)) ? $page : $pages[0];
	$action = $_REQUEST['action'];
	$module_id = intval($_REQUEST['id']);

	$errors = $success = array(); 

?>

<script type="text/javascript">
$(document).ready(function() {

	$("#checkall").click(function () {
		$(".checkall").attr("checked",this.checked);
	});
});
</script>

<div id="tabs" style="margin-bottom: 10px;">
	<ul>
		<?php foreach($pages as $page_name => $visible) { ?>
		<?php if(!$visible) continue; ?>
		<?php $selected = ($page_name == $page) ? true : false; ?>
		<li class="tab<?php if($selected) { ?> tabselected<?php } ?>"><a href="<?php echo $modulelink; ?>&page=<?php echo $page_name; ?>"><?php echo $vars['_lang']['page_' . strtolower($page_name)]; ?></a></li>
		<?php } ?>
	</ul>
</div>

<?php

	switch($page)
	{
		case 'modulesmanage':

			if(!$module_id)
			{
				header("Location: {$modulelink}&page=modules&error=modulenotprovided");
				exit;
			}

			$sql = "SELECT *
				FROM mod_joomlabridge_modules
				WHERE id = '{$module_id}'";
			$result = mysql_query($sql);
			$module_details = mysql_fetch_assoc($result);

			if(!$module_details)
			{
				header("Location: {$modulelink}&page=modules&error=modulenotexists");
				exit;
			}

			$action = $_POST['save'] ? 'save' : $action;
			$action = $_POST['deletemulti'] ? 'deletemulti' : $action;

			switch($action)
			{
				case 'deletemulti':

					$rules = $_REQUEST['selectattributes'];

					if(is_array($rules) && sizeof($rules))
					{
						foreach($rules as $rule_id)
						{
							$sql = "DELETE
								FROM mod_joomlabridge_modules_attrs
								WHERE id = '{$rule_id}'
								AND module_id = '{$module_id}'";

							if(mysql_query($sql))
							{
								$success[] = "Rule #{$rule_id} deleted successfully";
							}
							else
							{
								$errors[] = "Can't delete rule #{$rule_id}. SQL Error: " . mysql_error();
							}
						}
					}
					else
					{
						$errors[] = "You didn't selected any rule";
					}
				break;

				case 'delete':

					$rule_id = intval($_REQUEST['rule_id']);

					if(!$rule_id)
					{
						$errors[] = "You didn't provided rule id";
						break;
					}

					$sql = "DELETE
						FROM mod_joomlabridge_modules_attrs
						WHERE id = '{$rule_id}'
						AND module_id = '{$module_id}'";

					if(mysql_query($sql))
					{
						$success[] = "Rule #{$rule_id} deleted successfully";
					}
					else
					{
						$errors[] = "Can't delete rule #{$rule_id}. SQL Error: " . mysql_error();
					}

				break;

				case 'save':

					$smarty_var = $_POST['smarty'];

					if($smarty_var && preg_match("/^[\w\d]+$/", $smarty_var))
					{
						$sql = "UPDATE mod_joomlabridge_modules
							SET smarty = '{$smarty_var}'
							WHERE id = '{$module_id}'";

						if(mysql_query($sql))
						{
							$module_details['smarty'] = $smarty_var;

							$success[] = "Settings saved successfully";
						}
						else
						{
							$errors[] = "Can't save settings. SQL Error: " . mysql_error(); 
						}
					}
					elseif($smarty_var)
					{
						$errors[] = "Invalid Chars on Smarty Variable field"; 
					}

					// update old rules
					$rule_find = $_POST['rule_find'];

					if(sizeof($rule_find) && is_array($rule_find))
					{
						foreach($rule_find as $rule_id => $rule_find_data)
						{
							$rule_find_data = mysql_escape_string($rule_find_data);
							$rule_replace_data = mysql_escape_string($_POST['rule_replace'][$rule_id]);

							if($rule_find_data)
							{
								$sql = "UPDATE mod_joomlabridge_modules_attrs
									SET attr_find = '{$rule_find_data}', attr_replace = '{$rule_replace_data}'
									WHERE id = '{$rule_id}'";

								if(mysql_query($sql))
								{
									$success[] = "Rule #{$rule_id} :: Saved successfully";
								}
								else
								{
									$errors[] = "Rule #{$rule_id} :: Can't save this rule. SQL Error: " . mysql_error();
								}
							}
							else
							{
								$error[] = "Rule #{$rule_id} :: No Find Regular Expression was found";
							}
						}
					}
			
					// add new rule
					$new_rule_find = mysql_escape_string($_POST['new_rule_find']);
					$new_rule_replace = mysql_escape_string($_POST['new_rule_replace']);

					if($new_rule_find)
					{
						$sql = "INSERT INTO mod_joomlabridge_modules_attrs (`module_id`,`attr_find`,`attr_replace`) VALUES 
							('{$module_id}','{$new_rule_find}','{$new_rule_replace}')";

						if(mysql_query($sql))
						{
							$success[] = "Rule created successfully";
						}
						else
						{
							$errors[] = "Can't create rule. SQL Error: " . mysql_error();
						}
					}
				break;
			}

			$attrs = array();

			$sql = "SELECT *
				FROM mod_joomlabridge_modules_attrs
				WHERE module_id = '{$module_id}'";
			$result = mysql_query($sql);

			while($attr_details = mysql_fetch_assoc($result))
			{
				$attrs[] = $attr_details;
			}
			mysql_free_result($result);

?>
<?php if(sizeof($errors)) { ?>
<div class="errorbox">
	<strong><span class="title">Error</span></strong><br />
	<?php echo implode("<br />", $errors); ?>
</div>
<?php } ?>

<?php if(sizeof($success)) { ?>
<div class="successbox">
	<strong><span class="title">Success</span></strong><br />
	<?php echo implode("<br />", $success); ?>
</div>
<?php } ?>

<script type="text/javascript">
$(document).ready(function() {

	$('#testhtml').click(function() {

		var docWidth = $(document).outerWidth();
		var docHeight = $(document).outerHeight();
		var winWidth = $(window).outerWidth();
		var winHeight = $(window).outerHeight();

		var shadow = $('<div />').css({
			display: 'none',
			position: 'absolute',
			top: 0,
			left: 0,
			zIndex: '9998',
			background: '#000',
			width: docWidth + 'px',
			height: docHeight + 'px',
			opacity: '0.5',
			filter: 'alpha(opacity=50)'
		});

		var contentBox = $('<div />').css({
			display: 'none',
			position: 'absolute',
			top: ((winHeight / 2) - 200),
			left: ((winWidth / 2) - 300),
			zIndex: '9999',
			background: '#fff',
			border: '1px solid #000',
			width: '580px',
			height: '380px',
			padding: '10px',
			overflow: 'auto'
		});

		$('body').append(shadow).append(contentBox);

		shadow.fadeIn('slow');
		contentBox.html('<pre><code><?php echo mysql_escape_string(str_replace("\n", "<br />", htmlentities($joomlaBridge->getModulesData($module_id, true)))); ?></code></pre>').fadeIn('slow');

		shadow.click(function() {

			shadow.fadeOut('slow');
			contentBox.fadeOut('slow', function() {

				shadow.remove();
				contentBox.remove();
			});
		});
	});
});
</script>

<form action="<?php echo $modulelink; ?>&page=modulesmanage&id=<?php echo $module_id; ?>" method="post">

<h2 style="margin: 20px 0 5px; font-weight: bold;">Editing Module "<?php echo $module_details['module_name']; ?>"</h2>
<table width="100%" cellspacing="2" cellpadding="3" border="0" class="form">
<tbody>
<tr>
	<td class="fieldlabel" style="width: 30%;">Smarty Variable</td>
	<td class="fieldarea">{$jbridge.<input type="text" name="smarty" maxlength="20" onblur="if(this.value=='')this.value='<?php echo "{$module_details['module_type']}_{$module_details['module_id']}"; ?>';" onclick="if(this.value=='<?php echo "{$module_details['module_type']}_{$module_details['module_id']}"; ?>')this.value='';" value="<?php echo $module_details['smarty'] ? $module_details['smarty'] : "{$module_details['module_type']}_{$module_details['module_id']}"; ?>" />}</td>
</tr>
</tbody>
</table>

<h2 style="margin: 20px 0 5px; font-weight: bold;">
	Manage HTML Attributes Rules
	<input type="button" class="button" name="submit" id="testhtml" onclick="return false;" value="Test HTML" />
</h2>

<div class="tablebg">
	<table width="100%" cellspacing="1" cellpadding="3" border="0" class="datatable">
	<tbody>
	<tr>
		<th width="20"><input type="checkbox" id="checkall" /></th>
		<th>Rule #</th>
		<th style="width: 45%">Find (Regular Expression)</th>
		<th style="width: 45%">Replace</th>
		<th width="20"></th>
	</tr>
	<?php if(sizeof($attrs)) { ?>
	<?php foreach($attrs as $attribute_details) { ?>
	<tr>
		<td><input type="checkbox" class="checkall" value="<?php echo $attribute_details['id']; ?>" name="selectattributes[]" /></td>
		<td style="text-align: center;"><?php echo $attribute_details['id']; ?></td>
		<td><input type="text" name="rule_find[<?php echo $attribute_details['id']; ?>]" value="<?php echo $attribute_details['attr_find']; ?>" style="width: 100%;" /></td>
		<td><input type="text" name="rule_replace[<?php echo $attribute_details['id']; ?>]" value="<?php echo $attribute_details['attr_replace']; ?>" style="width: 100%;" /></td>
		<td style="text-align: center;">
			<a href="<?php echo $modulelink; ?>&page=modulesmanage&action=delete&id=<?php echo $module_id; ?>&rule_id=<?php echo $attribute_details['id']; ?>">
				<img src="images/delete.gif" width="16" height="16" border="0" alt="" />
			</a>
		</td>
	</tr>
	<?php } ?>
	<?php } else { ?>
	<tr>
		<td colspan="100"><?php echo $vars['_lang']['no_records']; ?></td>
	</tr>
	<?php } ?>
	<tr>
		<td></td>
		<td></td>
		<td><input type="text" name="new_rule_find" value="" style="width: 100%;" /></td>
		<td><input type="text" name="new_rule_replace" value="" style="width: 100%;" /></td>
		<td></td>
	</tr>
	</tbody>
	</table>
	With Selected:
	<input type="submit" class="btn-danger" name="deletemulti" value="Delete" /> 
</div>

<p align="center">
	<input type="submit" class="button" name="save" value="<?php echo $vars['_lang']['save_changes']; ?>" />
	<input type="button" class="button" name="submit" onclick="window.location='<?php echo $modulelink; ?>&page=modules';" value="Back to Modules List" />
</p>

</form>
<?php
		break;

		default:
		case 'modules':

			$errors = array();

			if($_REQUEST['error'])
			{
				$errors[] = $vars['_lang'][$_REQUEST['error']];
			}

			switch($action)
			{
				case 'visiblity':

					$response = $joomlaBridge->setVisiblity($module_id);
				break;
			}

			$modulelist = $joomlaBridge->getModules();

?>
<?php if(sizeof($errors)) { ?>
<div class="errorbox">
	<strong><span class="title"><?php echo $vars['_lang']['error']; ?></span></strong><br />
	<?php echo implode("<br />", $errors); ?>
</div>
<?php } ?>

<?php if(sizeof($success)) { ?>
<div class="successbox">
	<strong><span class="title"><?php echo $vars['_lang']['success']; ?></span></strong><br />
	<?php echo implode("<br />", $success); ?>
</div>
<?php } ?>
<form action="<?php echo $modulelink; ?>&page=modules" method="post">
	<div class="tablebg">
		<table width="100%" cellspacing="1" cellpadding="3" border="0" class="datatable">
		<tbody>
		<tr>
			<th width="20"><input type="checkbox" id="checkall" /></th>
			<th>Module #</th>
			<th>Module Name</th>
			<th>Module Position</th>
			<th>Module Type</th>
			<th>Smarty Var</th>
			<th>Status</th>
			<th width="20"></th>
		</tr>
		<?php if(sizeof($modulelist)) { ?>
		<?php foreach($modulelist as $module_data) { ?>
		<tr>
			<td><input type="checkbox" class="checkall" value="<?php echo $module_data['module_id']; ?>" name="selectedmodules[]" /></td>
			<td style="text-align: center;"><?php echo $module_data['module_id']; ?></td>
			<td><?php echo $module_data['module_name']; ?></td>
			<td style="text-align: center;"><?php echo $module_data['module_position']; ?></td>
			<td style="text-align: center;"><?php echo $module_data['module_type']; ?></td>
			<td style="text-align: center;">{$jbridge.<?php echo $module_data['smarty']; ?>}</td>
			<td style="text-align: center;">
				<a href="<?php echo $modulelink; ?>&page=modules&action=visiblity&id=<?php echo $module_data['id']; ?>">
					<img src="<?php if($module_data['visible']) { ?>images/icons/tick.png<?php } else { ?>images/delete.gif<?php } ?>" width="16" height="16" border="0" alt="" />
				</a>
			</td>
			<td style="text-align: center;">
				<a href="<?php echo $modulelink; ?>&page=modulesmanage&id=<?php echo $module_data['id']; ?>">
					<img src="images/edit.gif" width="16" height="16" border="0" alt="Edit" />
				</a>
			</td>
		</tr>
		<?php } ?>
		<?php } else { ?>
		<tr>
			<td colspan="100"><?php echo $vars['_lang']['no_records']; ?></td>
		</tr>
		<?php } ?>
		</tbody>
		</table>
	</div>

	<input type="hidden" name="pagenum" value="<?php echo $pagenum; ?>" />
</form>
<?php
		break;

		case 'settings':

			$errors = $success = array();

			switch($action)
			{
				case 'save':

					$config = $_POST['config'];

					$validation = array('joomla_url' => array('empty','url'), 'joomla_admin_username' => array('empty'), 'joomla_admin_password' => array('empty'));

					if(sizeof($config) && is_array($config))
					{
						foreach($config as $key => $value)
						{
							if(isset($validation[$key]))
							{
								$valid = true;

								foreach($validation[$key] as $validation_type)
								{
									switch($validation_type)
									{
										case 'empty': if(trim($value) == '') { $errors[] = "The field '{$vars['_lang'][$key]}' can't be empty"; $valid = false; } break;
										case 'url': if(!preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?$|i', $value)) { $errors[] = "The field '{$vars['_lang'][$key]}' is not a valid URL"; $valid = false; } break;
									}

									if(!$valid) break;
								}
							}
						}

						if(!sizeof($errors))
						{
							foreach($config as $key => $value)
							{
								$joomlaBridge->setConfig($key, $value);
							}

							$success[] = "Settings saved successfully";
						}
					}
				break;

				case 'clearcache':

					$joomlaBridge->clearCache();

				break;
			}

?>
<?php if(sizeof($errors)) { ?>
<div class="errorbox">
	<strong><span class="title"><?php echo $vars['_lang']['error']; ?></span></strong><br />
	<?php echo implode("<br />", $errors); ?>
</div>
<?php } ?>

<?php if(sizeof($success)) { ?>
<div class="successbox">
	<strong><span class="title"><?php echo $vars['_lang']['success']; ?></span></strong><br />
	<?php echo implode("<br />", $success); ?>
</div>
<?php } ?>

<form action="<?php echo $modulelink; ?>&page=settings" method="post">

<h2 style="margin: 20px 0 5px; font-weight: bold;">Joomla Login Details</h2>
<table width="100%" cellspacing="2" cellpadding="3" border="0" class="form">
<tbody>
<tr>
	<td class="fieldlabel" style="width: 30%;"><?php echo $vars['_lang']['joomla_url']; ?></td>
	<td class="fieldarea"><input type="text" name="config[joomla_url]" value="<?php echo $joomlaBridge->config['joomla_url']; ?>" style="width: 300px;" /> <?php echo $vars['_lang']['joomla_url_explain']; ?></td>
</tr>
<tr>
	<td class="fieldlabel"><?php echo $vars['_lang']['joomla_admin_username']; ?></td>
	<td class="fieldarea"><input type="text" name="config[joomla_admin_username]" value="<?php echo $joomlaBridge->config['joomla_admin_username']; ?>" /></td>
</tr>
<tr>
	<td class="fieldlabel"><?php echo $vars['_lang']['joomla_admin_password']; ?></td>
	<td class="fieldarea"><input type="password" name="config[joomla_admin_password]" value="<?php echo $joomlaBridge->config['joomla_admin_password']; ?>" /></td>
</tr>
</tbody>
</table>

<h2 style="margin: 20px 0 5px; font-weight: bold;">Users Synchronization</h2>
<table width="100%" cellspacing="2" cellpadding="3" border="0" class="form">
<tbody>
<tr>
	<td class="fieldlabel" style="width: 30%;"><?php echo $vars['_lang']['enabled']; ?></td>
	<td class="fieldarea">
		<input type="radio"<?php if($joomlaBridge->config['sync_enabled']) { ?> checked="checked"<?php } ?> name="config[sync_enabled]" value="1" /> <?php echo $vars['_lang']['yes']; ?>
		<input type="radio"<?php if(!$joomlaBridge->config['sync_enabled']) { ?> checked="checked"<?php } ?>  name="config[sync_enabled]" value="0" /> <?php echo $vars['_lang']['no']; ?>
	</td>
</tr>
<tr>
	<td class="fieldlabel">Username Format</td>
	<td class="fieldarea">
		<select name="config[username_format]">
			<option value="0"<?php if($joomlaBridge->config['username_format'] == 0) { ?> selected="selected"<?php } ?>>FirstName_UserID</option>
			<option value="1"<?php if($joomlaBridge->config['username_format'] == 1) { ?> selected="selected"<?php } ?>>FirstNameLastName_UserID</option>
			<option value="2"<?php if($joomlaBridge->config['username_format'] == 2) { ?> selected="selected"<?php } ?>>FirstNameUserID</option>
			<option value="3"<?php if($joomlaBridge->config['username_format'] == 3) { ?> selected="selected"<?php } ?>>FirstNameLastNameUserID</option>
			<option value="4"<?php if($joomlaBridge->config['username_format'] == 4) { ?> selected="selected"<?php } ?>>Email</option>
		</select>
	</td>
</tr>
</tbody>
</table>

<h2 style="margin: 20px 0 5px; font-weight: bold;">Modules Managment</h2>
<table width="100%" cellspacing="2" cellpadding="3" border="0" class="form">
<tbody>
<tr>
	<td class="fieldlabel" style="width: 30%;">Cache Enabled</td>
	<td class="fieldarea">
		<input type="radio"<?php if($joomlaBridge->config['cache_enabled']) { ?> checked="checked"<?php } ?> name="config[cache_enabled]" value="1" /> <?php echo $vars['_lang']['yes']; ?>
		<input type="radio"<?php if(!$joomlaBridge->config['cache_enabled']) { ?> checked="checked"<?php } ?>  name="config[cache_enabled]" value="0" /> <?php echo $vars['_lang']['no']; ?>
	</td>
</tr>
<tr>
	<td class="fieldlabel" style="width: 30%;">Clear Cache</td>
	<td class="fieldarea">
		<script type="text/javascript">
		$(document).ready(function() {
			$('.clear-cache').click(function() { 
				var parent = $(this).parent('td');
				parent.children('a').remove(); 
				parent.children('img').css('display', 'inline'); 
			});
		});
		</script>
		<a class="btn btn-success clear-cache" style="text-decoration: none; color: #fff;" href="<?php echo $modulelink; ?>&page=settings&action=clearcache">Clear Now</a>
		<img style="display: none;" src="../modules/addons/joomlabridge/images/loader.gif" alt="Clearing Cache" />
	</td>
</tr>
</tbody>
</table>

<p align="center">
	<input type="hidden" name="action" value="save" />
	<input type="submit" name="submit" value="<?php echo $vars['_lang']['save_changes']; ?>" />
</p>

</form>
<?php
		break;
	}
}

?>