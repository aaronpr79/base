<?php
/*
 * Basic BASE configuration file. DO NOT MODIFY THIS FILE! This file contains default values
 * that may be overwritten when you perform a system update!
 *
 * ALL CONFIGURATION ENTRIES ARE ORDERED ALPHABETICALLY, ONLY "debug" IS ON TOP FOR CONVENIENCE

 * @license http://opensource.org/licenses/GPL-2.0 GNU Public License, Version 2
 * @copyright Sven Oostenbrink <support@capmega.com>, Johan Geuze
 */

// To debug or not to debug?
$_CONFIG['debug']              = array('enabled'                            => false,      // If set to true, the system will run in debug mode, the debug.php library will be loaded, and debug functions will be available.
                                       'bar'                                => 'limited'); // true|false|"limited" If set to true, base will display a debug bar that with CSS can be positioned @ the bottom of the screen. When set to limited, only authenticated users with the right "debug" will be able to see the debug bar

// AJAX configuration
$_CONFIG['ajax']               = array('autosuggest'                        => array('min_characters'     => 2,
                                                                                     'default_results'    => 5,
                                                                                     'max_results'        => 15));

// Google AMP configuration
$_CONFIG['amp']                = array('enabled'                            => false);

// Avatar configuration, default avatar image, type will be added after this string, e.g.  _48x48.jpg
$_CONFIG['avatars']            = array('default'                            => '/pub/img/img_avatar',

                                       'types'                              => array('small'              => '100x100xthumb-circle',
                                                                                     'medium'             => '200x200xthumb-circle',
                                                                                     'large'              => '400x400xthumb'),

                                       'get_order'                          => array('facebook',
                                                                                     'google',
                                                                                     'microsoft'));

// Blog configuration
$_CONFIG['blogs']               = array('enabled'                           => false,
                                        'url'                               => '/%seocategory1%/%date%/%seoname%.html');

// Use bootstrap?
$_CONFIG['bootstrap']           = array('enabled'                           => false,
                                        'css'                               => 'bootstrap',
                                        'js'                                => 'bootstrap');
//:DELETE: viewport is used from $CONFIG_['mobile]['viewport']
                                        //'viewport'        => 'width=device-width, initial-scale=1.0');

//
$_CONFIG['cache']              = array('method'                             => 'file',                                                  // "file", "memcached" or false.
                                       'max_age'                            => 86400,                                                   // Max local cache age is one day
                                       'key_hash'                           => 'sha256',
                                       'key_interlace'                      => 3,
                                       'http'                               => array('enabled'            => 'auto',                    // Enable HTTP cache or not. If set to "auto", use PHP caching
                                                                                     'php_cache_limiter'  => 'public',                  // public | private | private_no_expire | nocache If $_CONFIG[cache][http][enabled] is set to auto, it will set PHP cache_limiter to this value. See https://secure.php.net/manual/en/function.session-cache-limiter.php
                                                                                     'php_cache_expire'   => '604800',                  // Use null for system default, which is no-store for admin, public for no user, and private, no-store for user session pages. If not, either "no-store", or "no-cache" with "public" or "private", optionally with max-age=N" where N is a natural number
                                                                                     'policy'             => null,                      // Use null for system default, which is no-store for admin, public for no user, and private, no-store for user session pages. If not, either "no-store", or "no-cache" with "public" or "private", optionally with max-age=N" where N is a natural number
                                                                                     'max_age'            => '604800'));                // Either "no-store", or "no-cache" with "public" or "private", optionally with max-age=N" where N is a natural number

// CDN configuration
$_CONFIG['cdn']                = array('min'                                => true,                                                    // If set to "true" all CSS and JS files loaded with html_load_js() and html_load_css() will be loaded as file.min.js instead of file.js. Use "true" in production environment, "false" in all other environments

                                       'enabled'                            => false,                                                   // If set to "true", base will try to use configured CDN servers for the content files. If set to false, files will be used from the local server

                                       'is_server'                          => false,                                                   // If set to "true", this server can function as a CDN server

                                       'copies'                             => 2,                                                       // Required amount of copies of each files. NOTE: This amount should be lower or equal to the amount of available CDN servers! (would not make sense otherwise)

                                       'domain'                             => '',                                                       // Required amount of copies of each files. NOTE: This amount should be lower or equal to the amount of available CDN servers! (would not make sense otherwise)

                                       'bundler'                            => array('enabled'            => true,                      // If JS and CSS bundler should be enabled or not
                                                                                     'max_age'            => 86400),                    // Max age of bundle files before they are deleted and regenerated

                                       'css'                                => array('post'               => false),                    // The default last CSS file to be loaded (after all others have been loaded, so that this one can override any CSS rule if needed)

                                       'js'                                 => array('load_delayed'       => false,                     // If set to true, the JS files will NOT be loaded in the <head> tag but at the end of the HTML <body> code so that the site will load faster. This may require some special site design to avoid problems though!

                                                                                     'use_linked'         => false,                     // If set to true, all files in the "linked" configuration below will be placed together in one larger file, and only that larger file will be loaded. This makes loading the pages faster since fewer requests are needed

                                                                                     'linked'             => array('base' => array('popup',               // Assoc array list of all files that are to be linked in one file for faster loading. See "use_linked" configuration setting
                                                                                                                                   'validate'))),

                                       'network'                            => array('enabled'            => false),                    // Use CDN network or not

                                       'normal'                             => array('js'                 => 'pub/js',                  // Location of js, CSS and image files for desktop pages
                                                                                     'css'                => 'pub/css',
                                                                                     'img'                => 'pub/img'),

                                       'mobile'                             => array('js'                 => 'pub/mobile/js',           // Location of js, CSS and image files for mobile pages
                                                                                     'css'                => 'pub/mobile/css',
                                                                                     'img'                => 'pub/mobile/img'),

                                       'path'                               => '',                                                      // Path component for location of CDN files. Useful for debugging multiple CDN servers

                                       'prefix'                             => '/pub/',                                                 // Prefix for all CDN objects, may be CDN server domain, for example

                                       'shared_key'                         => '');                                                     // Shared encryption key between site servers and CDN servers to send and receive encrypted messages


// Characterset
$_CONFIG['charset']            = 'UTF-8';                                                                                               // The default character set for this website (Will be used in meta charset tag)

// Client configuration
$_CONFIG['client']             = array('detect'                             => false);                                                  // If client detection should be performed. false if not, one of "full", "normal" or "lite" if detection should happen, and what type of detection

// PHP composer configuration
$_CONFIG['composer']           = array('global'                             => false);

// Content configuration
$_CONFIG['content']            = array('autocreate'                         => false);                                                  // When using load_content(), if content is missing should it be created automatically? Normally, use "true" on development and test machines, "false" on production

// Cookie configuration
$_CONFIG['cookie']             = array('lifetime'                           => 0,
                                       'path'                               => '/',
                                       'domain'                             => 'auto',                                                  // Domain limitation for cookies. Can be emtpy (no limitation), auto for SERVER_NAME, .auto for .SERVER_NAME which will limit to SERVER_NAME and sub domains, or a specific domain. NOTE: IF A SPECIFIC DOMAIN IS SPECIFIED, THEN IT MUST MATCH THE PRODUCTION DOMAIN (or .DOMAIN for domain and subdomains) OR BASE WILL CRASH AT STARTUP TO AVOID NON WORKING COOKIES!
                                       'secure'                             => false,
                                       'httponly'                           => false);

//
$_CONFIG['copyright']          = array('name'                               => 'Ingiga',                                                // Name to be displayed for the copyright
                                       'url'                                => 'https://capmega.com/copyright.html');                    // URL used for the copyright

// Access-Control-Allow-Origin configuration. comma delimeted list of sites to allow with CORS
$_CONFIG['cors']               = array('origin'                             => '*.',
                                       'methods'                            => 'GET, POST',
                                       'headers'                            => '');

// Global data location configuration
$_CONFIG['data']               = array('global'                             => true); // Set to TRUE to enable auto detect

// Database connectors configuration
$_CONFIG['db']                 = array('default'                            => 'core',

                                       'core'                               => array('driver'           => 'mysql',                         // PDO Driver used to communicate with the database server. For now, only MySQL has been tested, no others have been used yet, use at your own discretion
                                                                                     'host'             => 'localhost',                     // Hostname for SQL server
                                                                                     'port'             => '',                              // If set, don't use the default 3306 port
                                                                                     'user'             => 'base',                          // Username to login to SQL server
                                                                                     'pass'             => 'base',                          // Password to login to SQL server
                                                                                     'db'               => 'base',                          // Name of core database on SQL server
                                                                                     'init'             => true,                            // If set to true, upon first query of the pageload, the SQL library will check if the database requires initialization
                                                                                     'autoincrement'    => 1,                               // Default autoincrement for all database tables (MySQL only)
                                                                                     'buffered'         => false,                           // WARNING, READ ALL!! Use buffered queries or not. See PHP documentation for more information. WARNING: Currently buffered queries appear to completely wreck this sytem, do NOT use!
                                                                                     'charset'          => 'utf8mb4',                       // Default character set for all database tables
                                                                                     'collate'          => 'utf8mb4_general_ci',            // Default collate set for all database tables
                                                                                     'limit_max'        => 10000,                           // Standard SQL allowed LIMIT specified in table displays, for example, to avoid displaying a table with a milion entries, for example
                                                                                     'mode'             => 'PIPES_AS_CONCAT,IGNORE_SPACE,NO_KEY_OPTIONS,NO_TABLE_OPTIONS,NO_FIELD_OPTIONS',   // Special mode options for MySQL server
                                                                                     'ssh_tunnel'       => array('required'    => false,    // SSH tunnel configuration. This should NOT be used for the core database!
                                                                                                                 'local_port'  => 3307,     // Port on the local machine to enter in the SSH tunnel
                                                                                                                 'remote_port' => 3306,     // MySQL server port on the remote server
                                                                                                                 'server'      => ''),      // SEO name of server registered in the servers table

                                                                                     'pdo_attributes'   => array(),                         // Special PDO otions. By default, try to use MySQLND with PDO::ATTR_EMULATE_PREPARES to avoid internal data type changes from int > string!
                                                                                     //'pdo_attributes'   => array(PDO::ATTR_EMULATE_PREPARES  => false,  // Special PDO otions. By default, try to use MySQLND with PDO::ATTR_EMULATE_PREPARES to avoid internal data type changes from int > string!
                                                                                     //                            PDO::ATTR_STRINGIFY_FETCHES => false, ),
                                                                                     'timezone'         => 'UTC'));                         // Default timezone to use

//domain
$_CONFIG['domain']             = 'auto';                                                                                                    // The base domain of this website. for example, "mywebsite.com",  "thisismine.com.mx", etc. If set to "auto" it will use $_SERVER[SERVER_NAME]

// Editors configuration, tinymce jbimages plugin configuration
$_CONFIG['editors']            = array('imageupload'                        => 'session',                                                   // "all" or "session" or "admin",

                                       'images'                             => array('url'                => '/images',                     // Base URL that jbimiages will give to tinymce for all images inserted into the document
                                                                                     'allowed_types'      => 'gif|jpg|png',                 // What file extensions will be recognized by jbimages as being an image
                                                                                     'max_size'           => 0,                             //
                                                                                     'max_width'          => 0,                             //
                                                                                     'max_height'         => 0,                             //
                                                                                     'allow_resize'       => false,                         //
                                                                                     'overwrite'          => false,                         // If set to true, if images names already exist when a new images is being uploaded, it will be overwritten. If set to false, the new image will be assigned a number behind the basename (before the extension) to make it unique
                                                                                     'encrypt_name'       => false));                       // Should filenames retain their original name (false) or should jbimages give it a random character name (true)?

// Feedback configuration
$_CONFIG['feedback']           = array('emails'                             => array('Ingiga Support' => 'support@capmega.com'));

// Flash alert configuration
$_CONFIG['flash']              = array('type'                               => 'html',                                                      // The type of HTML flash message to use. Either "html" or "sweetalert"
                                       'html'                               => '<div class="flash:type">:message</div>');

//
$_CONFIG['formats']            = array('force1224'                          => '24',
                                       'date'                               => 'Ymd',
                                       'time'                               => 'YmdHis',
                                       'human_date'                         => 'd/m/Y',
                                       'human_time'                         => 'H:i:s',
                                       'human_datetime'                     => 'd/m/Y H:i:s',
                                       'human_nice_date'                    => 'l, j F Y');

// Filesystem configuration
$_CONFIG['fs']                 = array('dir_mode'                           => 0770,                                                        // When the system creates directory, this sets what file mode it will have (Google unix file modes for more information)
                                       'file_mode'                          => 0660,                                                        // When the system creates a file, this sets what file mode it will have (Google unix file modes for more information)
                                       'target_path_size'                   => 4);                                                          // When creating

// google api
$_CONFIG['google-map-api-key'] = '';                                                                                                        // The google maps API key

// Init configuration
$_CONFIG['init']               = array('cli'                                => true,                                                        // Sets if system init can be executed by shell
                                       'http'                               => false);                                                      // Sets if system init can be executed by http (IMPORTANT: This is not supported yet!)

// JS configuration
$_CONFIG['js']                 = array('animate'                            => array('speed'              => 100));                         // Sets default speed for javascript animations

// jQuery UI configuration
$_CONFIG['jquery-ui']          = array('theme'                              => 'smoothness');                                               // Sets the default UI theme for jquery-ui

// Language
$_CONFIG['language']           = array('default'                            => 'en',                                                        // If www user has no language specified, this determines the default language. Either a 2 char language code (en, es, nl, ru, pr, etc) or "auto" to do GEOIP language detection
                                       'detect'                             => true,                                                        // Perform requested language auto detect
                                       'supported'                          => array('en'                 => 'English',                     // Associated array list of language_code => language_name of supported languages for this website
                                                                                     'es'                 => 'Español',                     // Associated array list of language_code => language_name of supported languages for this website
                                                                                     'nl'                 => 'Nederlands'));

// Locale configuration
$_CONFIG['locale']             = array(LC_ALL                               => 'en_US.UTF8',
                                       LC_COLLATE                           => null,
                                       LC_CTYPE                             => null,
                                       LC_MONETARY                          => null,
                                       LC_NUMERIC                           => null,
                                       LC_TIME                              => null,
                                       LC_MESSAGES                          => null);

// Location configuration
$_CONFIG['location']           = array('detect'                             => false);                                                      // Attempt auto location detect if current session doesn't have location information

//Log configuration
$_CONFIG['log']                = array('single'                             => true);                                                       // All file logs will go to one and the same file

// Mailer configuration
$_CONFIG['mailer']             = array('sender'                             => array('wait'               => 5,
                                                                                     'count'              => 100));

// Mail configuration
$_CONFIG['mail']               = array('developers'                         => array());

// Maintenance configuration
$_CONFIG['maintenance']        = false;                                                                                                     // If set to true, the only page that will be displayed is the www/LANGUAGE/maintenance.php

// Memcached configuration. If NOT set to false, the memcached library will automatically be loaded!
$_CONFIG['memcached']          = array('servers'                            => array(array('localhost', 11211, 20)),                        // Array of multiple memcached servers. If set to false, no memcached will be done.
                                       'expire_time'                        => 86400,                                                       // Default memcached object expire time (after this time memcached will drop them automatically)
                                       'prefix'                             => PROJECT.'-',                                                 // Default memcached object key prefix (in case multiple projects use the same memcached server)
                                       'namespaces'                         => true);                                                       // Use namespaces to store the data. This will require extra lookups on memcached to determine namespaces contents, but allows for more flexibility

//Meta configuration
$_CONFIG['meta']               = array('author'                             => '');                                                         // Set default meta tags for this site which may be overruled by parameters for the function html_header(). See libs/html.php

// Mobile configuration
$_CONFIG['mobile']             = array('enabled'                            => true,                                                        // If disabled, treat every device as a normal desktop device, no mobile detection will be done
                                       'force'                              => false,                                                       // Treat every device as if it is a mobile device
                                       'auto_redirect'                      => true,                                                        // If set to true, the first session page load will automatically redirect to the mobile version of the site
                                       'tablets'                            => false,                                                       // Treat tablets as mobile devices
                                       'viewport'                           => 'width=device-width, initial-scale=1, maximum-scale=1');     // The <meta> viewport tag used for this site

// Name of the website
$_CONFIG['name']               = 'base';

// Do not index this site. DANGEROUS! WILL DESTROY SEO! Use for intranet websites, for example.
$_CONFIG['noindex']            = false;                                                                                                     // If set to true, the entire website will not be indexed at all by google. Use for intranet websites


// Paging configuration
$_CONFIG['paging']             = array('limit'                              => 50,                                                          // The maximum amount of items shown per page
                                       'show_pages'                         => 5,                                                           // The maximum amount of pages show, should always be an odd number, or an exception will be thrown!
                                       'prev_next'                          => true,                                                        // Show previous - next links
                                       'first_last'                         => true,                                                        // Show first - last links
                                       'hide_first'                         => true,                                                        // Hide first number (number 1) in URL, useful for links like all.html, all2.html, etc
                                       'hide_single'                        => true,                                                        // Hide pager if there is only a single page
                                       'hide_ends'                          => true,                                                        // Hide the "first" and "last" options
                                       'list'                               => array(  10 => tr('Show 10 entries'),
                                                                                       20 => tr('Show 20 entries'),
                                                                                       50 => tr('Show 50 entries'),
                                                                                      100 => tr('Show 100 entries'),
                                                                                      500 => tr('Show 500 entries'),
                                                                                     null => tr('Show all entries')));

//Paypal configuration
$_CONFIG['paypal']             = array('version'                            => 'sandbox',                                                   //

                                       'live'                               => array('email'              => '',
                                                                                     'api-username'       => '',
                                                                                     'api-password'       => '',
                                                                                     'api-signature'      => ''),

                                       'sandbox'                            => array('email'              => '',
                                                                                     'api-username'       => '',
                                                                                     'api-password'       => '',
                                                                                     'api-signature'      => ''));

$_CONFIG['plans']              = array('silver'                             => null,
                                       'gold'                               => null);

// Prefetch
$_CONFIG['prefetch']           = array('dns'                                => array('facebook.com',
                                                                                     'twitter.com'),

                                       'files'                              => array());

// Is this a production environment?
$_CONFIG['production']         = true;

//domain
$_CONFIG['protocol']           = 'http://';                                                                                                 // The base protocol of this website. Basically either "http://",  or "https://".

// Redirects configuration (This ususally would not require changes unless you want to have other file names for certain actions like signin, etc)
$_CONFIG['redirects']          = array('auto'                               => 'get',                                                       // Auto redirects (usually because of user or right required) done by "session" or "get"
                                                                               'index'            => 'index.php',                           // What is the default index page for this site
                                                                               'accessdenied'     => '403',                                 // Usually won't redirect, but just show
                                                                               'signin'           => 'signin.php',                          // What is the default signin page for this site
                                                                               'lock'             => 'lock.php',                            // What is the default lock page for this site
                                                                               'aftersignin'      => 'index.php',                           // Where will the site redirect to by default after a signin?
                                                                               'aftersignout'     => 'index.php');


// Root URL of the website
$_CONFIG['root']               = '';                                                                                                        //

// Share buttons
$_CONFIG['share']              = array('provider'                           => false);                                                      // Share button provider

// Security configuration
$_CONFIG['security']           = array('signin'                             => array('save_password'    => true,                            // Allow the browser client to save the passwords. If set to false, different form names will be used to stop browsers from saving passwords
                                                                                     'ip_lock'          => false,                           // Either "false", "true" or number n (which makes it lock to users with the right ip_lock), or "ip address" or array("ip address", "ip address", ...). If specified as true, only 1 IP will be allowed. If specified as number N, up to N IP addresses will be allowed. If specified as "ip address", only that IP address will be allowed. If specified as array("ip address", ...) all IP addresses in that array will be allowed
                                                                                     'destroy_session'  => false,                           // Either "false", "true" or number n (which makes it lock to users with the right ip_lock), or "ip address" or array("ip address", "ip address", ...). If specified as true, only 1 IP will be allowed. If specified as number N, up to N IP addresses will be allowed. If specified as "ip address", only that IP address will be allowed. If specified as array("ip address", ...) all IP addresses in that array will be allowed
                                                                                     'two_factor'       => false),                          // Either "false" or a valid twilio "from" phone number

                                       'authentication'                     => array('captcha_failures' => 4,                               // Authentication failures until re-captcha is required. If 0, recaptcha will be required with every authentication. If set to FALSE, captcha will be disabled completely
                                                                                     'auto_lock_fails'  => 6,                               // Upon N authentication failures, auto lock user accounts. Set to 0 to disable
                                                                                     'auto_lock_time'   => 60),                             // Upon user account auto locking, keep the account locked for N seconds. Set to 0 to disable

                                       'passwords'                          => array('test'             => false,                           // Test new user password strength?
                                                                                     'hash'             => 'sha256',                        // What hash algorithm will we use to store the passwords?
                                                                                     'usemeta'          => true,                            // Add used hash as meta data to the password when storing them so we know what hash was used.
                                                                                     'useseed'          => true,                            // Use the SEED constant to calculate passwords
                                                                                     'unique_updates'   => 3,                               // Passwords cannot be updated to the same password for minimum N times
                                                                                     'unique_days'      => 30),                             // Passwords cannot be updated to the same password for minimum N days

                                       'user'                               => 'apache',                                                    //
                                       'group'                              => 'apache',                                                    //
                                       'umask'                              =>  0007,                                                       //
                                       'expose_php'                         => false,                                                       // If false, will hide the X-Powered-By PHP header. If true, will leave the header as is. If set to any other value, will send that value as X-Powered-By value
                                       'seed'                               => '%T"$#HET&UJHRT87',                                          // SEED for generating codes

                                       'csrf'                               => array('enabled'          => 'force',                         // CSRF detection configuration. true | false | "force". Force will forcibly check every POST on CSRF
                                                                                     'buffer_size'      => 10,                              // The amount of server side CSRF keys that are being kept. With more keys, more pages can be run in parrallel
                                                                                     'timeout'          => 0));                             // Timeout after page generation, where @ POST time the CSRF check will fail. Use 0 to disable


// Sessions
$_CONFIG['sessions']           = array('lifetime'                           => 86400,                                                       // Total time that a session may exist until the user has to login again
                                       'timeout'                            => 86400,                                                       // Time between pageloads that, when passed, will cause the session to be closed
                                       'http_only'                          => true,                                                        // Sets if cookies can be sent over other protocols than HTTP. See https://secure.php.net/manual/en/session.configuration.php#ini.session.cookie_httponly
                                       'secure_only'                        => true,                                                        // Sets if cookies can only be sent over secure connections. See https://secure.php.net/manual/en/session.configuration.php#ini.session.cookie_secure
                                       'same_site'                          => 'Lax',                                                       // false | Lax | Strict : Sets if cookiets can be sent cross domain by browser. See https://secure.php.net/manual/en/session.configuration.php#ini.session.cookie_samesite
                                       'strict'                             => true,                                                        // Forces session.use_strict_mode to the specified value. Recommended TRUE for session security! See https://secure.php.net/manual/en/session.configuration.php#ini.session.use_strict_mode
                                       'regenerate_id'                      => 600,                                                         // Time required to regenerate the session id, used to mitigate session fixation attacks. MUST BE LOWER THAN $_CONFIG[session][lifetime]!
                                       'check_referrer'                     => true,                                                        // If set to true, the referrer must contain the domain name
                                       'handler'                            => false,                                                       // false | mm | mc | sql Use the default PHP session manager, shared memory manager (mm), memcached (mc) or sql_sessions library to manage sessions
                                       'euro_cookies'                       => false,                                                       // If set to true, all european countries will see a "This site uses cookies" warning before cookies are being sent

                                       'extended'                           => array('age'           => 2592000,                            //
                                                                                     'clear'         => true),                              //

                                       'signin'                             => array('force'         => false,                              //
                                                                                     'allow_next'    => true,                               //
                                                                                     'redirect'      => 'index.php'));                      //

// Social website integration configuration
$_CONFIG['social']             = array('links'                              => array('facebook'       => '',                                //
                                                                                     'twitter'        => '',                                //
                                                                                     'youtube'        => '',                                //
                                                                                     'target'         => '_blank'));                        //

// Sync configuration.
$_CONFIG['sync']               = array();                                                                                                   //

// Sync configuration.
$_CONFIG['statistics']         = array('enabled'                            => true);                                                       //

// Timezone configuration. See http://www.php.net/manual/en/timezones.php for more info
$_CONFIG['timezone']           = array('display'                            => 'America/Mexico_City',                                       // Default timezone to be used to display date/time
                                       'system'                             => 'UTC');                                                      // Default system timezone to be used when dates are stored

// Default title configuration
$_CONFIG['title']              = 'Base';                                                                                                    //

// Temporary path location, either "local" (ROOT/tmp/) or "global" (/tmp/)
$_CONFIG['tmp']                = 'local';                                                                                                   // Either "local" or "global". "local" will save all temporary files in ROOT/tmp, "global" will save all temporary files in /tmp/PROJECT/

// What webservice this is.
$_CONFIG['type']               = 'core';                                                                                                    // core, api, cdn

// User configuration
$_CONFIG['users']              = array('type_filter'                        => null,
                                       'unique_nicknames'                   => true,
                                       'password_minumum_strength'          => 4,
                                       'duplicate_reference_codes'          => false);

//Xapian search
$_CONFIG['xapian']             = array('dir'                                => ROOT.'data/xapian/');                                        // Base path for Xapian databases

$_CONFIG['whitelabels']        = array('enabled'                            => false);                                                      // Either false (No whitelabel domains, only the normal site FQDN allowed), "list" (only default and registered FQDNs allowed), "sub" (only default FQDN and its sub domains allowed), "all" (All domains allowed), or the specific FQDN that is allowed
?>
