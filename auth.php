<?php
/*
JWT Authenticator
https://shawnwang.net
Description: Add JWT authentication with ease. This plugin can automate login and user creation, it is optimised for AAF Rapid Connect, but can be used for other providers too.
Version: 1.0
Author: Shawn Wang
Author URI: https://shawnwang.net
License: GPLv2 or later
*/

// register the callback
add_action( 'rest_api_init', function () {
	register_rest_route( 'jwt-auth/v1', 'callback', array(
		'methods' => 'POST',
		'callback' => 'ja_login'
	), true );
} );

require_once('JWT.php');

function ja_login() {
	//get all attributes
	$options = get_option( 'ja_settings' );
	$token_name = $options['token_name'];
	$secret_key = $options['secret_key'];
	$iss = $options['iss'];
	$aud = $options['aud'];
	$attributes = $options['attributes'];
	$mail = $options['mail'];
	$givenname = $options['givenname'];
	$surname = $options['surname'];
	$nickname = $options['nickname'];
	$displayname = $options['displayname'];
	$default_role = $options['default_role'];

	// decode the token
	$token = $_POST[$token_name];
	$key = $secret_key;
	$JWT = new JWT;
	$json = $JWT->decode($token, $key);
	$jwt = json_decode($json, true);

	// use unix time for comparision
	$exp = $jwt['exp'].is_int ? $jwt['exp'] : strtotime($jwt['exp']);
	$nbf = $jwt['nbf'].is_int ? $jwt['nbf'] : strtotime($jwt['nbf']);
	$now = strtotime("now");

	// if authentication successful
	if ($jwt['iss'] == $iss && $jwt['aud'] == $aud && $exp > $now && $now > $nbf) {
	    $attributes = $jwt[$attributes];
	    $_SESSION['attributes'] = $attributes;
	    $_SESSION['jwt'] = $jwt;

	    // find or create user
		$user = ja_find_or_create_user($attributes[$mail], $attributes[$mail], $attributes[$givenname], $attributes[$surname], $attributes[$nickname], $attributes[$displayname], $default_role);
		// login user
		if ($user)
		{
		    wp_clear_auth_cookie();
		    wp_set_current_user ( $user->ID, $user->user_login );
		    wp_set_auth_cookie  ( $user->ID );
		    do_action( 'wp_login', $user->user_login );
		    // redirect to dashboard
		    $redirect_to = user_admin_url();
		    wp_safe_redirect( $redirect_to );
		    exit();
		}

	}
	else return 'login failed!';
}

function ja_find_or_create_user($username, $email, $firstname, $lastname, $nickname, $displayname, $default_role) {
	// if user exists return user
	if ( username_exists( $username ) )
    	return get_user_by('login', $username );
	elseif ( email_exists($email) )
		return get_user_by('email', $email);
	// else create user
   	else {
    	$random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
    	// create user
		$user_id = wp_create_user( $username, $random_password, $email );
		// update user metadata and return user id
		return wp_update_user( array( 'ID' => $user_id, 'first_name' => $firstname, 'last_name' => $lastname, 'nickname' => $nickname, 'display_name' => $displayname, 'role' => $default_role ));
	}
}

// add login url to the login form
function the_login_message() {
	$options = get_option( 'ja_settings' );
	$login_url = $options['login_url'];
	$login_message = $options['login_message'];
    return "<a href='{$login_url}' >{$login_message}</a>";
}
add_filter( 'login_message', 'the_login_message' );

?>