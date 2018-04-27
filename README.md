## Joomla to WP Migrated Users Authentication Plugin

-    Contributors: [lucky62](https://profiles.wordpress.org/lucky62/), [asmartin](https://github.com/asmartin), [luthien](https://github.com/Luthien-in-edhil)
-    Tested up to: WP 4.9.5
-    Requires at least: WP 3.X
-    Stable tag: 1.2.0

A plugin to authenticate users migrated from Joomla/Mambo to Wordpress.

## Installation
1.  extract plugin zip file and load up to your wp-content/plugin directory
2.  Activate Plugin in the Admin => Plugins Menu

## Usage
Joomla encrypted passwords should be stored in `wp_usermeta` key `joomlapass` for this to work. You can import users and populate this field automatically with [this tool](https://github.com/asmartin/Joomla-To-Wordpress).

When the user logs in the first time after migration, his/her password is checked against the value in the `joomlapass` meta key. How this is done depends on the format of the Joomla hash:  

- when the hash begins with '$P$', the user-supplied password is hashed with a [PHPass](http://www.openwall.com/phpass) portable hash (with a cost of 10) and authenticated agains the `joomlapass` meta key;
- when the hash begins with '$2y' (looking like having been encoded with PHP's builtin [password_hash()](http://php.net/manual/en/function.password-hash.php)), the user-supplied password is checked using the complementary [password_verify()](http://php.net/manual/en/function.password-verify.php) function;
- else, we assume that the user-supplied password is hashed using the md5:salt algorithm, and is checked by using that algorith on the user-supplied password and checking if the resulting hash is equal to the `joomlapass` meta key

If the password turns out to be correct, it is encrypted using the default Wordpress algorithm and stored in the user's `user_pass` database field. At this time, the `joomlapass` meta key is renamed to `joomlapassbak` to avoid repeatedly performing this conversion, so that for all subsequent logins, the user will now be authenticated via the standard Wordpress authentication plugin. 

## License
plugin is free for any purpose...

## Changelog

-    19.3.2011 - 1.0.0 - first release
-    27.3.2011 - 1.0.1 - links correction
-    01.3.2017 - 1.1.0 - added support for PHPass, tested with Joomla 2.5.x and Wordpress 4.7.x
-    27.4.2018 - 1.2.0 - added support for PHP builtin password_verify() tested with Wordpress 4.9.5 (Joomla version unknown)
