<?php
/*
Plugin Name: Joomla2WP Migrated Users Authentication Plugin
Description: Authenticate users migrated from Joomla and update their WP passwords
Version: 1.3.0
Author: lucky62, asmartin, luthien_in_edhil
*/
require_once (ABSPATH . 'wp-includes/class-phpass.php');
// plugin must run before any other authentication plugins -
add_filter('authenticate', 'joomla_mig_auth', 1, 3);


// function provides user authentication against joomla encrypted password
// encrypted joomla password should be stored to user meta key "joomlapass" during migration
// when user are trying to log in to WP site first time after migration
// password provides by user is checked against joomla hash
// and if is correct WP password of user is udated.

function joomla_mig_auth( $user, $username, $password ) {
   if ( is_a($user, 'WP_User') ) { return $user; }

   // check existence of required parameters
   if ( empty($username) || empty($password) ) return $user;

   // retrieve user data
   $userdata = get_user_by('login', $username);
   if ( !$userdata ) return $user;
   if ( !$userdata->joomlapass ) return $user;

   // try authenticating against stored joomla password
   $auth_success = false;
   if (strpos($userdata->joomlapass, '$P$') === 0) {
       // use CheckPassword
       $auth_success = use_wp_checkpassword($password, $userdata->joomlapass);
   } else if (strpos($userdata->joomlapass, '$2y') === 0){
      // Looks like a job for php's builtin password_verify - fits on one line here
      $auth_success = password_verify($password , $userdata->joomlapass);
   } else {
      // Use Joomla's old md5:salt method
      $auth_success = use_md5_salt($password, $userdata->joomlapass);
   }
   if ( $auth_success ) {
      // password is OK; update WP with user-provided password
      $user_id = $userdata->ID;
      wp_set_password($password, $user_id);
      // rename joomlapass to joomlapassbak to avoid rewrite WP password hash repeatedly
      update_user_meta($user_id, 'joomlapassbak', $userdata->joomlapass);
      delete_user_meta($user_id, 'joomlapass');
   }  
   return $user;
}


// change (or add to) these this functions if passwords are encrypted by non default Joomla 
// encryption method

// this function checks the password using Wordpress' CheckPassword funtion (in class-phpass.php)
function use_wp_checkpassword($password, $joomlapass) {
    // Use PHPass's portable hashes with a cost of 10.
    $phpass = new PasswordHash(10, true);
    $password = stripslashes($password);
    return $phpass->CheckPassword($password, $joomlapass);
}

// this function is when Joomla's md5 hash + salt separated by a colon ':' is used. It 
// simply replicates the same mechanism with the user provided pw and compares the outcome
function use_md5_salt($password, $joomlapass) {
    $parts  = explode( ':', $joomlapass );
    $joomlahash = $parts[0];
    $joomlasalt = $parts[1];
    $passwhash = ($joomlasalt) ? md5($password.$joomlasalt) : md5($password);

    return ($joomlahash == $passwhash);
}