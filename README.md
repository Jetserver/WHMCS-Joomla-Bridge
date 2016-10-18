# WHMCS-Joomla-Bridge

# Installation

The plugin’s role is to eliminate Joomla’s user management URL. They will be redirected to WHMCS.

Login to your Joomla site’s backend as super admin user, navigate to – “Extensions” -> “Extension Manager“, click “Upload Package File” and install the plugin (file name starts with plg_*).

Navigate to “Extensions” -> “Plugin Manager“, find our plugin (“JBridge URL Plugin“), make sure it’s enabaled, set to active and edit the plugin properties

Once enabled, the plugin will eliminate Joomla’s user management URLs and will redirect them to WHMCS. This is completely editable, the recommended values are as follows –

* Login URL – http://www.yourdomain.com/clientarea.php
* Logout URL – http://www.yourdomain.com/logout.php
* Register URL – http://www.yourdomain.com/register.php
* Password Reset URL – http://www.yourdomain.com/pwreset.php
* Username Reminder URL – http://www.yourdomain.com/pwreset.php
* Edit Profile URL – http://www.yourdomain.com/clientarea.php?action=details
* User Profile URL – http://www.yourdomain.com/clientarea.php?action=details

** To disable a specific URL redirect, just leave it empty

---

The component’s role is to listen for WHMCS API requests. It will use to sync users & login shares.

Login to your Joomla site’s backend as super admin user, navigate to – “Extensions” -> “Extension Manager“, click “Upload a package file” and install the component (file name starts with com_*).

Once installed, navigate to “Components” -> “Joomla bridge” -> “Whitelist“, add your WHMCS server external WAN ip address

** ONLY IPS LISTED IN THE WHITELIST WILL BE ABLE TO COMUNICATE WITH THE BRDIGE COMPONENT

Next, we will set the access level allowed to login to Joomla:

navigate to “Components” -> “Joomla bridge” -> “Settings“, change access level to “Super Administrator“.

---

From the package you downloaded, unzip the whmcs addon module (file name whmcs_*) and upload the folder “joomlabridge” (as is) to your WHMCS installtion.

The folder should be uploaded to the following path: “modules/addons“.

After uploaded, the full path should be: “modules/addons/joomlabridge“.

Login to your WHMCS as admin user, and navigate to “Setup” -> “Addon modules“, Find the “Joomla bridge” module, and activate it.

You will be able to access it throught the “Addons” top menu.

# More Information

https://docs.jetapps.com/category/whmcs-addons/jbridge
