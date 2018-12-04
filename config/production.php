<?php
/*
 * ALL CONFIGURATION ENTRIES ARE ORDERED ALPHABETICALLY, ONLY "debug" IS ON TOP FOR CONVENIENCE
 */

//Debug or not?
$_CONFIG['debug']['enabled']            = false;

// Detect browser?
$_CONFIG['browser_detect']	            = false;

$_CONFIG['cdn']['js']['load_delayed']   = true;

// Cookie configuration
$_CONFIG['cookie']['domain']            = 'base';

//database
$_CONFIG['db']['core']['db']            = '';
$_CONFIG['db']['core']['user']          = 'base';
$_CONFIG['db']['core']['pass']          = 'base';
$_CONFIG['db']['core']['timezone']      = 'UTC';

//domain
$_CONFIG['domain']                      = 'base';

//
$_CONFIG['formats']                     = array('date'                 => 'Ymd',
                                                'time'                 => 'YmdHis',
                                                'human_date'           => 'F j, Y',
                                                'human_time'           => 'H:i:s A',
                                                'human_datetime'       => 'd/m/Y H:i:s A');
// google api
$_CONFIG['google-map-api-key']          = '';

// Language
$_CONFIG['language']['default']         = 'en';

// Mail configuration
$_CONFIG['mail']['developers']          = array(array('name'  => 'Sven Oostenbrink',
                                                      'email' => 'support@capmega.com'));

//
$_CONFIG['mobile']['viewport']          = 'width=device-width, initial-scale=1';

// Name of the website
$_CONFIG['name']                        = 'base';

// SSO configuration
$_CONFIG['sso']['facebook']             = array('appid'    => '',
                                                'secret'   => '',
                                                'scope'    => 'email,publish_stream,status_update,friends_online_presence,user_birthday,user_location,user_work_history',
                                                'redirect' => 'http://base.localhost/tests/sso.php?provider=facebook');

$_CONFIG['sso']['google']               = array('appid'    => '',
                                                'secret'   => '',
                                                'scope'    => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/plus.me https://www.google.com/m8/feeds',
                                                'redirect' => 'http://base.localhost/tests/sso.php?provider=google');

$_CONFIG['sso']['linkedin']             = array('appid'    => '',
                                                'secret'   => '',
                                                'scope'    => 'r_fullprofile r_emailaddress',
                                                'redirect' => 'http://base.localhost/tests/sso.php?provider=linkedin');

$_CONFIG['sso']['microsoft']            = array('appid'    => '',
                                                'secret'   => '',
                                                'scope'    => 'wl.basic wl.emails wl.birthday wl.skydrive wl.photos',
                                                'redirect' => 'http://base.localhost/tests/sso.php?provider=microsoft');

$_CONFIG['sso']['paypal']               = array('appid'    => '',
                                                'secret'   => '',
                                                'scope'    => 'email profile',
                                                'redirect' => 'http://base.localhost/tests/sso.php?provider=paypal');

$_CONFIG['sso']['reddit']               = array('appid'    => '',
                                                'secret'   => '',
                                                'scope'    => 'identity',
                                                'redirect' => 'http://base.localhost/tests/sso.php?provider=reddit');

$_CONFIG['sso']['twitter']              = array('appid'    => '',
                                                'secret'   => '',
                                                'scope'    => '',
                                                'redirect' => 'http://base.localhost/tests/sso.php?provider=twitter');

$_CONFIG['sso']['yandex']               = array('appid'    => '',
                                                'secret'   => '',
                                                'scope'    => '',
                                                'redirect' => 'http://base.localhost/tests/sso.php?provider=yandex');

// Title
$_CONFIG['title']                       = 'BASE test project';
?>
