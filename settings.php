<?php
/*
Plugin Name: JWT Authenticator
Plugin URI: https://shawnwang.net
Description: This plugin integrates JWT authentication and automates user creation. The plugin is written for AAF Rapid Connect, but can be used for other providers too.
Version: 1.1
Author: Shawn Wang
Author URI: https://shawnwang.net
License: GPLv2 or later
*/

// only require when options are ready
if (ja_options_ready()) require_once "auth.php";

add_action( 'admin_menu', 'ja_add_admin_menu' );
add_action( 'admin_init', 'ja_settings_init' );

function ja_add_admin_menu(  ) { 
	add_options_page( 'JWT Authenticator', 'JWT Authenticator', 'manage_options', 'jwt_authenticator', 'ja_options_page' );
}

function ja_settings_init(  ) { 
	register_setting( 'pluginPage', 'ja_settings' );

	// instructions section
	add_settings_section(
		'ja_settings_instructions', 
		__( '', 'wordpress' ),
		function() { 
			$site_url = site_url('/wp-json/jwt-auth/v1/callback');
			$message = ja_options_ready() ? "Plugin enabled." : "Plugin disabled. Parameters not ready.";
			echo "
				<div style='background-color:white;padding:15px;margin-right:20px;border:1px dashed;border-color:grey'>
			        <h2>How to use this plugin</h2>
			        <ol>
			        	<li>Generate a secrete key with command: <b>tr -dc '[[:alnum:][:punct:]]' < /dev/urandom | head -c32 ;echo</b>
			            <li>Register the key and call back URL <b>{$site_url}</b> with your authentication provider.</li>
			            <li>Specify authentication and user creation parameters. Those marked with * are required.</li>
			        </ol>
			        <p><b>Status:</b> {$message}</p>
		        </div>
				"; 
		}, 
		'pluginPage'
	);

	// authentication section
	add_settings_section(
		'ja_settings_authentication', 
		__( 'Authentication', 'wordpress' ), 
		function() { echo '
		<div>
	        <ol>
	            <li>Token name: The token (POSTed from the authentication provider to the call back url) will be read from $_POST[token].</li>
	            <li>Login URL/Message: The login link will be displyed on the WP login form.</li>
	            <li>Secret Key: The secret key you generated earlier.</li>
	            <li>Issuer/Audience: Authentication provider/consumer.</li>
	        </ol>
		</div>
		'; }, 
		'pluginPage'
	);

	add_settings_field( 
		'token_name', 
		__( 'Token Name *', 'wordpress' ), 
		'token_name_render', 
		'pluginPage', 
		'ja_settings_authentication' 
	);

	add_settings_field( 
		'login_url', 
		__( 'Login URL *', 'wordpress' ), 
		'login_url_render', 
		'pluginPage', 
		'ja_settings_authentication' 
	);

	add_settings_field( 
		'login_message', 
		__( 'Login Message *', 'wordpress' ), 
		'login_message_render', 
		'pluginPage', 
		'ja_settings_authentication' 
	);

	add_settings_field( 
		'secret_key', 
		__( 'Secret Key *', 'wordpress' ), 
		'secret_key_render', 
		'pluginPage', 
		'ja_settings_authentication' 
	);

	add_settings_field( 
		'iss', 
		__( 'iss (Issuer) *', 'wordpress' ), 
		'iss_render', 
		'pluginPage', 
		'ja_settings_authentication' 
	);

	add_settings_field( 
		'aud', 
		__( 'aud (Audience) *', 'wordpress' ), 
		'aud_render', 
		'pluginPage', 
		'ja_settings_authentication' 
	);

	// user creation section
	add_settings_section(
		'ja_settings_user', 
		__( 'User Creation', 'wordpress' ), 
		function() { echo '
		<div>
	        <b>Configure parameters for WordPress user creation upon successful login.</b>
	        <ol>
	            <li>Attributes: User attributes will be read from decoded json response $jwt[attributes].</li>
	            <li>Attribute (Mail/Name): Attribute will be read from $jwt[attributes][attribute].</li>
	            <li>Default Role: Default role for new user.</li>
	        </ol>
		</div>
		'; }, 
		'pluginPage'
	);

	add_settings_field( 
		'attributes', 
		__( 'Attributes *', 'wordpress' ), 
		'attributes_render', 
		'pluginPage', 
		'ja_settings_user' 
	);

	add_settings_field( 
		'mail', 
		__( 'Mail *', 'wordpress' ), 
		'mail_render', 
		'pluginPage', 
		'ja_settings_user' 
	);

	add_settings_field( 
		'first_name', 
		__( 'First Name', 'wordpress' ),
		'first_name_render', 
		'pluginPage', 
		'ja_settings_user' 
	);

	add_settings_field( 
		'last_name', 
		__( 'Last Name', 'wordpress' ),
		'last_name_render', 
		'pluginPage', 
		'ja_settings_user' 
	);

	add_settings_field( 
		'nickname', 
		__( 'Nickname', 'wordpress' ), 
		'nickname_render', 
		'pluginPage', 
		'ja_settings_user' 
	);

	add_settings_field( 
		'displayname', 
		__( 'Display Name', 'wordpress' ), 
		'displayname_render', 
		'pluginPage', 
		'ja_settings_user' 
	);

	add_settings_field( 
		'default_role', 
		__( 'Default Role', 'wordpress' ),
		'default_role_render', 
		'pluginPage', 
		'ja_settings_user' 
	);
}

function token_name_render(  ) { 
	$options = get_option( 'ja_settings' );
	?>
	<input type='text' name='ja_settings[token_name]' value='<?php echo esc_html($options['token_name']); ?>' placeholder="token">
	<?php
}

function login_url_render(  ) { 
	$options = get_option( 'ja_settings' );
	?>
	<input type='text' size="60" name='ja_settings[login_url]' value='<?php echo esc_html($options['login_url']); ?>' placeholder="https://example.com/jwt/auth/">
	<?php
}

function login_message_render(  ) { 
	$options = get_option( 'ja_settings' );
	?>
	<input type='text' size="60" name='ja_settings[login_message]' value='<?php echo esc_html($options['login_message']); ?>' placeholder="Text or <img /> tag">
	<?php
}

function secret_key_render(  ) { 
	$options = get_option( 'ja_settings' );
	?>
	<input type='text' size="60" name='ja_settings[secret_key]' value='<?php echo esc_html($options['secret_key']); ?>' placeholder="_`]0dcX^O|7E#_ePH`9!jjcN;CL_sFG/">
	<?php
}

function iss_render(  ) { 
	$options = get_option( 'ja_settings' );
	?>
	<input type='text' size="60" name='ja_settings[iss]' value='<?php echo esc_html($options['iss']); ?>' placeholder="https://example.com/">
	<?php
}

function aud_render(  ) { 
	$options = get_option( 'ja_settings' );
	?>
	<input type='text' size="60" name='ja_settings[aud]' value='<?php echo esc_html($options['aud']); ?>' placeholder="https://yoursite.com/">
	<?php
}

function attributes_render(  ) { 
	$options = get_option( 'ja_settings' );
	?>
	<input type='text' name='ja_settings[attributes]' value='<?php echo esc_html($options['attributes']); ?>' placeholder="attributes">
	<?php
}

function mail_render(  ) { 
	$options = get_option( 'ja_settings' );
	?>
	<input type='text' name='ja_settings[mail]' value='<?php echo esc_html($options['mail']); ?>' placeholder="mail">
	<?php
}

function first_name_render(  ) { 
	$options = get_option( 'ja_settings' );
	?>
	<input type='text' name='ja_settings[first_name]' value='<?php echo esc_html($options['first_name']); ?>' placeholder="givenname">
	<?php
}

function last_name_render(  ) { 
	$options = get_option( 'ja_settings' );
	?>
	<input type='text' name='ja_settings[last_name]' value='<?php echo esc_html($options['last_name']); ?>' placeholder="surname">
	<?php
}

function nickname_render(  ) { 
	$options = get_option( 'ja_settings' );
	?>
	<input type='text' name='ja_settings[nickname]' value='<?php echo esc_html($options['nickname']); ?>' placeholder="cn">
	<?php
}

function displayname_render(  ) { 
	$options = get_option( 'ja_settings' );
	?>
	<input type='text' name='ja_settings[displayname]' value='<?php echo esc_html($options['displayname']); ?>' placeholder="displayname">
	<?php
}

function default_role_render(  ) { 
	$options = get_option( 'ja_settings' );
	?>
	<select name='ja_settings[default_role]'>
	    <?php foreach (ja_get_all_roles() as $key => $value) : ?>
       		<option value='<?php echo $key; ?>' <?php selected( $options['default_role'], $key ); ?>><?php echo $value; ?></option>
        <?php endforeach; ?>
	</select>
<?php
}

function ja_options_page(  ) { 
	?>
	<form action='options.php' method='post'>
		<h2>JWT Authenticator</h2>
		<?php
		settings_fields( 'pluginPage' );
		do_settings_sections( 'pluginPage' );
		submit_button();
		?>
	</form>
	<?php
}

// get all roles and reverse in order
function ja_get_all_roles() {
    global $wp_roles;
    $roles = $wp_roles->get_names();
    return array_reverse($roles);    
}

// check the presence of parameters
function ja_options_ready() {
	$options = get_option( 'ja_settings' );
	$options_ready = !empty($options['token_name']) && !empty($options['login_url']) && !empty($options['login_message']) && !empty($options['secret_key']) && !empty($options['iss']) && !empty($options['aud']) && !empty($options['attributes']) && !empty($options['mail']);
	return $options_ready;
}

?>