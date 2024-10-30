=== JWT Authenticator ===

Contributors: shawn
Tags: jwt, token, authentication, login, sso, aaf, user
Requires at least: 3.2
Tested up to: 4.6
Stable tag: 1.1
License: GPL v2 or later

This plugin integrates JWT authentication and automates user creation.

== Description ==

This plugin integrates JWT authentication and automates user creation. The plugin is written for AAF Rapid Connect, but can be used for other providers too.

Here is how this plugin works:

1. Generate a secrete key with command: tr -dc '[[:alnum:][:punct:]]' < /dev/urandom | head -c32 ;echo
2. Register the key and call back URL http://yoursite.com/wp-json/jwt-auth/v1/callback with your authentication provider.
3. Specify authentication and user creation parameters. Those marked with * are required.

== Screenshots ==

1. ![Plugin settings page - Authentication](screenshot-1.png)
1. ![Plugin settings page - User Creation](screenshot-2.png)

== Installation ==

You can install this plugin directly from your WordPress dashboard:

 1. Go to the *Plugins* menu and click *Add New*.
 2. Search for *JWT Authenticator*.
 3. Click *Install Now*.
 4. Activate the plugin.

 Alternatively, you can download the plugin and install manually:
 1. Upload the entire `/jwt-authenticator/` folder to the `/wp-content/plugins/` directory.
 2. Activate the plugin.

== Frequently Asked Questions ==

= Does this plugin have dependencies on other plugins? =

No.

= Where can I configure the plugin =

You can find the settings page here: *Settings > Settings > JWT Authenticator*

= Where can I get help? =

You can try the WordPress Support Forum or email me directly.

== Changelog ==

= 1.1 =

* Added screenshots

= 1.0 =

* Initial release

