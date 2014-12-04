<?php
/*
 * Basic BASE configuration file. DO NOT MODIFY THIS FILE! This file contains default values
 * that may be overwritten when you perform a system update!
 *
 * ALL CONFIGURATION ENTRIES ARE ORDERED ALPHABETICALLY, ONLY "debug" IS ON TOP FOR CONVENIENCE

 * @license http://opensource.org/licenses/GPL-2.0 GNU Public License, Version 2
 * @copyright Sven Oostenbrink <support@svenoostenbrink.com>, Johan Geuze
 */

//Debug or not?
$_CONFIG['debug']              = false;                                                                     // If set to true, the system will run in debug mode, the debug.php library will be loaded, and debug functions will be available.

// Avatar configuration, default avatar image, type will be added after this string, e.g.  _48x48.jpg
$_CONFIG['avatars']            = array('default'          => '/pub/img/img_avatar',

                                       'types'            => array('small'          => '100x100xthumb-circle',
                                                                   'medium'         => '200x200xthumb-circle',
                                                                   'large'          => '400x400xthumb'),

                                       'get_order'        => array('facebook',
                                                                   'google',
                                                                   'microsoft'));

// Blog configuration
$_CONFIG['blogs']               = array('enabled'         => false,
                                        'url'             => '/%category%/%date%/%seoname%.html');

// Use bootstrap?
$_CONFIG['bootstrap']           = array('enabled'         => false,
                                        'css'             => 'bootstrap',
                                        'js'              => 'bootstrap',
                                        'viewport'        => 'width=device-width, initial-scale=1.0');

//
$_CONFIG['cache']              = array('method'           => 'file',                                        // "file", "memcached" or false.
                                       'key_hash'         => 'md5',
                                       'key_interlace'    => 4);

// CDN configuration
$_CONFIG['cdn']                = array('min'              => true,                                          // If set to "true" all CSS and JS files loaded with html_load_js() and html_load_css() will be loaded as file.min.js instead of file.js. Use "true" in production environment, "false" in all other environments

                                       'css'              => array('post'           => false),              // The default last CSS file to be loaded (after all others have been loaded, so that this one can override any CSS rule if needed)

                                       'js'               => array('jquery_version' => 1,                   // Major version of jQuery to use, either 1 or 2 for jQuery 1.X or 2.X

                                                                   'default_libs'   => array('base/jquery', 'base/strings', 'base/base'),   // Default JS libraries to be loaded

                                                                   'load_delayed'   => false,               // If set to true, the JS files will NOT be loaded in the <head> tag but at the end of the HTML <body> code so that the site will load faster. This may require some special site design to avoid problems though!

                                                                   'use_linked'     => false,               // If set to true, all files in the "linked" configuration below will be placed together in one larger file, and only that larger file will be loaded. This makes loading the pages faster since fewer requests are needed

                                                                   'linked'         => array('base' => array('popup',       // Assoc array list of all files that are to be linked in one file for faster loading. See "use_linked" configuration setting
                                                                                                             'validate'))),

                                       'normal'           => array('js'             => 'pub/js',            // Location of js, CSS and image files for desktop pages
                                                                   'css'            => 'pub/css',
                                                                   'img'            => 'pub/img'),

                                       'mobile'           => array('js'             => 'pub/mobile/js',     // Location of js, CSS and image files for mobile pages
                                                                   'css'            => 'pub/mobile/css',
                                                                   'img'            => 'pub/mobile/img'));

// Characterset
$_CONFIG['charset']            = 'UTF-8';                                                                   // The default character set for this website (Will be used in meta charset tag)

// Detect client?
$_CONFIG['client_detect']      = true;                                                                      // Should system try a client_detect() on first page of session? If yes, system will try to obtain client data (stored in $_SESSION[client]), is it mobile, is it spider, etc.

// PHP composer configuration
$_CONFIG['composer']           = array('global'           => false);

// Content configuration
$_CONFIG['content']            = array('autocreate'       => false);                                        // When using load_content(), if content is missing should it be created automatically? Normally, use "true" on development and test machines, "false" on production

// Cookie configuration
$_CONFIG['cookie']             = array('lifetime'         => 0,
                                       'path'             => '/',
                                       'domain'           => '.base',
                                       'secure'           => false,
                                       'httponly'         => false);

// Access-Control-Allow-Origin configuration. comma delimeted list of sites to allow with CORS
$_CONFIG['cors']               = '';

// Curl library configuration
$_CONFIG['curl']               = array('proxy'            => 'http://proxy.localhost/file_get_contents_proxy.php?url=',
                                       'user_agent'       => 'Mozilla/5.0 (Windows NT 5.1; rv:10.0.2) Gecko/20100101 Firefox/10.0.2');

// Global data location configuration
$_CONFIG['data']               = array('global'           => true); // Set to TRUE to enable auto detect

// Database configuration
$_CONFIG['db']                 = array('driver'           => 'mysql',                                       // PDO Driver used to communicate with the database server. For now, only MySQL has been tested, no others have been used yet, use at your own discretion
                                       'host'             => 'localhost',                                   // Hostname for SQL server
                                       'user'             => 'base',                                        // Username to login to SQL server
                                       'pass'             => 'base',                                        // Password to login to SQL server
                                       'db'               => 'base',                                        // Name of core database on SQL server
                                       'mode'             => 'PIPES_AS_CONCAT,IGNORE_SPACE,NO_KEY_OPTIONS,NO_TABLE_OPTIONS,NO_FIELD_OPTIONS',                                 // Special options for SQL server
                                       'buffered'         => true,                                          // Use buffered queries or not. See PHP documentation for more information
                                       'charset'          => 'utf8',                                        // Default character set for all database tables
                                       'collate'          => 'utf8_general_ci',                             // Default collate set for all database tables
                                       'autoincrement'    => 1,                                             // Default autoincrement for all database tables (MySQL only)
                                       'timezone'         => 'America/Mexico_City');                        // Default timezone to use

//domain
$_CONFIG['domain']             = '';                                                                        // The base domain of this website. for example, "mywebsite.com",  "thisismine.com.mx", etc.

// Editors configuration, tinymce jbimages plugin configuration
$_CONFIG['editors']            = array('imageupload'      => 'session',                                     // "all" or "session" or "admin",

                                       'images'           => array('url'           => '/images',            // Base URL that jbimiages will give to tinymce for all images inserted into the document
                                                                   'allowed_types' => 'gif|jpg|png',        // What file extensions will be recognized by jbimages as being an image
                                                                   'max_size'      => 0,                    //
                                                                   'max_width'     => 0,                    //
                                                                   'max_height'    => 0,                    //
                                                                   'allow_resize'  => false,                //
                                                                   'overwrite'     => false,                // If set to true, if images names already exist when a new images is being uploaded, it will be overwritten. If set to false, the new image will be assigned a number behind the basename (before the extension) to make it unique
                                                                   'encrypt_name'  => false));              // Should filenames retain their original name (false) or should jbimages give it a random character name (true)?

// Feedback configuration
$_CONFIG['feedback']           = array('emails'           => array('Sven Oostenbrink Support' => 'support@svenoostenbrink.com'));

// Flash alert configuration
$_CONFIG['flash']              = array('css_name'         => 'flash',
                                       'button'           => '',
                                       'prefix'           => '');

//
$_CONFIG['formats']            = array('date'           => 'Ymd',
                                       'time'           => 'YmdHis',
                                       'human_date'     => 'd/m/Y',
                                       'human_time'     => 'H:i:s meridian',
                                       'human_datetime' => 'd/m/Y H:i:s meridian');

// Filesystem configuration
$_CONFIG['fs']                 = array('system_tempdir'   => true,                                          // ?
                                       'dir_mode'         => 0770,                                          // When the system creates directory, this sets what file mode it will have (Google unix file modes for more information)
                                       'file_mode'        => 0660,                                          // When the system creates a file, this sets what file mode it will have (Google unix file modes for more information)
                                       'target_path_size' => 4);                                            // When creating

// google api
$_CONFIG['google-map-api-key'] = '';                                                                        // The google maps API key

//imagemagic location
$_CONFIG['imagemagic_convert'] = '/usr/bin/convert';                                                        // The location of the imagemagic "convert" command

// Init configuration
$_CONFIG['init']               = array('shell'            => true,                                          // Sets if system init can be executed by shell
                                       'apache'           => false);                                        // Sets if system init can be executed by www (IMPORTANT: This is not supported yet!)

// jQuery UI configuration
$_CONFIG['jquery-ui']          = array('theme'            => 'smoothness');                                 // Sets the default UI theme for jquery-ui

// Language
$_CONFIG['language']           = array('default'          => 'auto',                                        // If www user has no language specified, this determines the default language. Either a 2 char language code (en, es, nl, ru, pr, etc) or "auto" to do GEOIP language detection
                                       'fallback'         => 'en',                                          // If language default was set to "auto" and GEOIP detection failed, what will be the fallback language? 2 char language code like "en", "es", "nl", etc.
                                       'supported'        => array('en' => 'English',                       // Associated array list of language_code => language_name of supported languages for this website
                                                                   'es' => 'Español',                       // Associated array list of language_code => language_name of supported languages for this website
                                                                   'nl' => 'Nederlands'));

// Locale configuration
$_CONFIG['locale']             = 'es-MX';

//Log configuration
$_CONFIG['log']                = array('default'          => 'db',                                          // Where entries will be logged. Either "db", "file", or "both"
                                       'path'             => 'log/');                                       // In case log is "file" or "both", sets the path for the log file

// Mailer configuration
$_CONFIG['mailer']             = array('sender'           => array('wait'  => 5,
                                                                   'count' => 100));

// Mail configuration
$_CONFIG['mail']               = array('developers'       => '');

// Maintenance configuration
$_CONFIG['maintenance']        = false;                                                                     // If set to true, the only page that will be displayed is the www/LANGUAGE/maintenance.php

// Memcached configuration. If NOT set to false, the memcached library will automatically be loaded!
$_CONFIG['memcached']          = array('servers'          => array(array('localhost', 11211, 20)),          // Array of multiple memcached servers. If set to false, no memcached will be done.
                                       'expire_time'      => 86400,                                         // Default memcached object expire time (after this time memcached will drop them automatically)
                                       'prefix'           => PROJECT.'-');                                  // Default memcached object key prefix (in case multiple projects use the same memcached server)

//Meta configuration
$_CONFIG['meta']               = array('author'           => '');                                           // Set default meta tags for this site which may be overruled by parameters for the function html_header(). See libs/html.php

// Mobile configuration
$_CONFIG['mobile']             = array('enabled'          => true,                                          // If disabled, treat every device as a normal desktop device, no mobile detection will be done
                                       'force'            => false,                                         // Treat every device as if it is a mobile device
                                       'auto_redirect'    => true,                                          // If set to true, the first session page load will automatically redirect to the mobile version of the site
                                       'tablets'          => false,                                         // Treat tablets as mobile devices
                                       'viewport'         => '<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">');  // The <meta> viewport tag used for this site

// Name of the website
$_CONFIG['name']               = 'base';

// Notification configuration
$_CONFIG['notifications']      = array('force'            => false,

                                       'methods'          => array('email'         => array('enabled' => true),
                                                                   'prowl'         => array('enabled' => false)));

// Paging configuration
$_CONFIG['paging']             = array('pages'            => 5,                                             //
                                       'count'            => 20);                                           // When using html_paging, howmany items should be shown per page?

//Password configuration
$_CONFIG['password']           = array('hash'             => 'sha1',                                        //
                                       'usemeta'          => true,                                          //
                                       'useseed'          => true);                                         //

//Paypal configuration
$_CONFIG['paypal']             = array('version'          => 'sandbox',                                     //

                                       'live'             => array('email'         => '',
                                                                   'api-username'  => '',
                                                                   'api-password'  => '',
                                                                   'api-signature' => ''),

                                       'sandbox'          => array('email'         => '',
                                                                   'api-username'  => '',
                                                                   'api-password'  => '',
                                                                   'api-signature' => ''));

$_CONFIG['plans']              = array('silver' => null,
                                       'gold'   => null);

//domain
$_CONFIG['protocol']           = 'http://';                                                                 // The base protocol of this website. Basically either "http://",  or "https://".

// The URL root of the website
$_CONFIG['root']               = '';

// Redirects configuration (This ususally would not require changes unless you want to have other file names for certain actions like signin, etc)
$_CONFIG['redirects']          = array('index'            => 'index.php',                                   // What is the default index page for this site
                                       'signin'           => 'signin.php',                                  // What is the default signin page for this site
                                       'aftersignin'      => 'index.php',                                   // Where will the site redirect to by default after a signin?
                                       'aftersignout'     => 'index.php');                                  //Where will the site redirect to by default after a signout?

// Security configuration
$_CONFIG['security']           = array('signin'           => array('save_password' => true,                 // Allow the browser client to save the passwords. If set to false, different form names will be used to stop browsers from saving passwords
                                                                   'ip_lock'       => false,                // Either "false", "true" (which makes it lock to users with the right ip_lock), or "ip address"
                                                                   'two_factor'    => false),               // Either "false" or a valid twilio "from" phone number

                                       'user'             => 'apache',                                      //
                                       'group'            => 'apache');                                     //

// Sessions
$_CONFIG['sessions']           = array('shared_memory'    => false,                                         // Store session data in shared memory, very useful for security on shared servers!

                                       'extended'         => array('age'           => 2592000,              //
                                                                   'clear'         => true),                //

                                       'signin'           => array('force'         => false,                //
                                                                   'allow_next'    => true,                 //
                                                                   'redirect'      => 'index.php'));        //

// Social website integration configuration
$_CONFIG['social']             = array('links'            => array('facebook' => '',                        //
                                                                   'twitter'  => '',                        //
                                                                   'youtube'  => '',                        //
                                                                   'target'   => '_blank'));                //

// SSO configuration
$_CONFIG['sso']                = array('facebook'         => false,                                         //

                                       'google'           => false,                                         //

                                       'linkedin'         => false,                                         //

                                       'microsoft'        => false,                                         //

                                       'paypal'           => false,                                         //

                                       'reddit'           => false,                                         //

                                       'twitter'          => false,                                         //

                                       'yandex'           => false);                                        //

// Sync configuration.
$_CONFIG['sync']               = array();                                                                   //

// Timezone configuration. See http://www.php.net/manual/en/timezones.php for more info
$_CONFIG['timezone']           = 'America/Mexico_City';                                                     //

// Default title configuration
$_CONFIG['title']              = 'Base';                                                                    //

// System configuration
$_CONFIG['system']             = array('translator'       => 'translator.localhost',                        //
                                       'updater'          => 'updater.svenoostenbrink.com');                //

//Xapian search
$_CONFIG['xapian']             = array('dir'              => ROOT.'data/xapian/');                          // Base path for Xapian databases

// Temporary path location, either "local" (ROOT/tmp/) or "global" (/tmp/)
$_CONFIG['tmp']                = 'local';                                                                   // Either "local" or "global". "local" will save all temporary files in ROOT/tmp, "global" will save all temporary files in /tmp/PROJECT/
?>
