<?php
/*
 * This is the startup sequence for CLI programs
 *
 * @license http://opensource.org/licenses/GPL-2.0 GNU Public License, Version 2
 * @copyright Sven Oostenbrink <support@ingiga.com>
 */



/*
 * All scripts will execute cli_done() automatically once done
 */
load_libs('cli');
register_shutdown_function('cli_done');



/*
 * Define basic platform constants
 */
define('ADMIN'   , '');
define('SCRIPT'  , str_runtil(str_rfrom($_SERVER['PHP_SELF'], '/'), '.php'));
define('PWD'     , slash(isset_get($_SERVER['PWD'])));
define('FORCE'   , cli_argument('-F', false, cli_argument('--force')));
define('NOCOLOR' , cli_argument('-C', false, cli_argument('--no-color')));
define('TEST'    , cli_argument('-T', false, cli_argument('--test')));
define('VERBOSE' , ((empty($this->register['quiet']) or cli_argument('-V', false, cli_argument('--verbose'))) ? 'VERBOSE' : ''));
define('QUIET'   , cli_argument('-Q', false, cli_argument('--quiet')));
define('LIMIT'   , cli_argument('--limit', true));
define('STARTDIR', slash(getcwd()));



/*
 * Process basic shell arguments
 */
try{
    /*
     * Correct $_SERVER['PHP_SELF'], sometimes seems empty
     */
    if(empty($_SERVER['PHP_SELF'])){
        if(!isset($_SERVER['_'])){
            throw new Exception('No $_SERVER[PHP_SELF] or $_SERVER[_] found', 'notfound');
        }

         $_SERVER['PHP_SELF'] =  $_SERVER['_'];
    }

    foreach($GLOBALS['argv'] as $argid => $arg){
        /*
         * (Usually first) argument may contain the startup of this script, which we may ignore
         */
        if($arg == $_SERVER['PHP_SELF']){
            continue;
        }

        switch($arg){
            case '-D':
                // FALLTHROUGH
            case '--debug':
                /*
                 * Run script in debug mode
                 */
                log_console('WARNING: RUNNING IN DEBUG MODE!');
                debug(true);
                break;

            case '--version':
                /*
                 * Show version information
                 */
                log_console(tr('BASE framework code version ":fv", project code version ":pv"', array(':fv' => FRAMEWORKCODEVERSION, ':pv' => PROJECTCODEVERSION)));
                $die = 0;
                break;

            case '-U':
                // FALLTHROUGH
            case '--usage':
                // FALLTHROUGH
            case 'usage':
                cli_show_usage(isset_get($GLOBALS['usage']), 'white');
                $die = 0;
                break;

            case '-H':
                // FALLTHROUGH
            case '--help':
                // FALLTHROUGH
            case 'help':
                if(isset_get($GLOBALS['argv'][$argid + 1]) == 'system'){
                    load_libs('help');
                    help('system');

                }else{
                    if(empty($GLOBALS['help'])){
                        log_console(tr('Sorry, this script has no help text defined yet'), 'yellow');

                    }else{
                        $GLOBALS['help'] = array_force($GLOBALS['help'], "\n");

                        if(count($GLOBALS['help']) == 1){
                            log_console(array_shift($GLOBALS['help']), 'white');

                        }else{
                            foreach(array_force($GLOBALS['help'], "\n") as $line){
                                log_console($line, 'white');
                            }

                            log_console();
                        }
                    }
                }

                $die = 0;
                break;

            case '-V':
                // FALLTHROUGH
            case '--verbose':
                break;

            case '-L':
                // FALLTHROUGH
            case '--language':
                /*
                 * Set language to be used
                 */
                if(isset($language)){
                    throw new Exception('Language has been specified twice');
                }

                if(!isset($GLOBALS['argv'][$argid + 1])){
                    throw new Exception('startup-cli: The "language" argument requires a two letter language core right after it');

                }else{
                    $language = $GLOBALS['argv'][$argid + 1];
                }

                unset($GLOBALS['argv'][$argid]);
                unset($GLOBALS['argv'][$argid + 1]);
                break;

            case '-E':
                // FALLTHROUGH
            case '--env':
                /*
                 * Set environment and reset next
                 */
                if(isset($environment)){
                    throw new Exception('Environment has been specified twice');
                }

                if(!isset($GLOBALS['argv'][$argid + 1])){
                    throw new Exception('startup-cli: The "environment" argument requires an existing environment name right after it');

                }else{
                    $environment = $GLOBALS['argv'][$argid + 1];
                }

                unset($GLOBALS['argv'][$argid]);
                unset($GLOBALS['argv'][$argid + 1]);
                break;

            case '--timezone':
                /*
                 * Set timezone
                 */
                if(isset($timezone)){
                    throw new Exception('Timezone has been specified twice');
                }

                if(!isset($GLOBALS['argv'][$argid + 1])){
                    throw new Exception('startup-cli: The "timezone" argument requires a valid and existing timezone name right after it');

                }else{
                    $timezone = $GLOBALS['argv'][$argid + 1];
                }

                unset($GLOBALS['argv'][$argid]);
                unset($GLOBALS['argv'][$argid + 1]);
                break;

            default:
                /*
                 * This is not a system parameter
                 */
                break;
        }
    }

    unset($arg);
    unset($argid);

}catch(Exception $e){
    echo "startup-cli: Command line parser failed with \"".$e->getMessage()."\"\n";
    $core->register['exit_code'] = 1;
    die(1);
}

if(isset($die)){
    $core->register['exit_code'] = $die;
    die($die);
}

// :TODO: Check what this does, either delete it or comment it!
array_shift($GLOBALS['argv']);



/*
 * Check what environment we're in
 */
if(empty($environment)){
    $env = getenv(PROJECT.'_ENVIRONMENT');

    if(empty($env)){
        echo "\033[0;31mstartup: No required environment specified for project \"".PROJECT."\"\033[0m\n";
        $core->register['exit_code'] = 2;
        die(2);
    }

}else{
    $env = $environment;
}

if(strstr($env, '_')){
    echo "\033[0;31mstartup: Specified environment \"$env\" is invalid, environment names cannot contain the underscore character\033[0m\n";
    $core->register['exit_code'] = 4;
    die(4);
}

define('ENVIRONMENT', $env);

if(!file_exists(ROOT.'config/'.$env.'.php')){
    echo "\033[0;31mstartup: Configuration file \"ROOT/config/".$env.".php\" for specified environment\"".$env."\" not found\033[0m\n";
    $core->register['exit_code'] = 5;
    die(5);
}



/*
 * Load basic configuration for the current environment
 * Load cache libraries (done until here since these need configuration @ load time)
 */
load_config(' ');
load_libs('cache'.(empty($_CONFIG['memcached']) ? '' : ',memcached').(empty($_CONFIG['cdn']['enabled']) ? '' : ',cdn'));



/*
 * Determine the screen width
 */
$core->register['cli'] = array('columns' => cli_get_columns(),
                               'rows'    => cli_get_lines());

if($core->register['cli']['columns'] <= 80){
    $core->register['cli']['width'] = 'small';

}elseif($core->register['cli']['columns'] <= 160){
    $core->register['cli']['width'] = 'medium';

}else{
    $core->register['cli']['width'] = 'large';
}



/*
 * Set security umask
 */
umask($_CONFIG['security']['umask']);



/*
 * Setup locale and character encoding
 */
ini_set('default_charset', $_CONFIG['charset']);

foreach($_CONFIG['locale'] as $key => $value){
    if($value){
        setlocale($key, $value);
    }
}



/*
 * Prepare for unicode usage
 */
if($_CONFIG['charset'] = 'UTF-8'){
    mb_init(not_empty($_CONFIG['locale'][LC_CTYPE], $_CONFIG['locale'][LC_ALL]));

    if(function_exists('mb_internal_encoding')){
        mb_internal_encoding('UTF-8');
    }
}



/*
 * Set timezone information
 * See http://www.php.net/manual/en/timezones.php for more info
 */
try{
    date_default_timezone_set($_CONFIG['timezone']['system']);

}catch(Exception $e){
    /*
     * Users timezone failed, use the configured one
     */
    notify($e);
}

define('TIMEZONE', $_CONFIG['timezone']['display']);
$_SESSION['user']['timezone'] = $_CONFIG['timezone']['display'];



/*
 * Get required language.
 */
$language = not_empty(cli_argument('--language'), cli_argument('L'), $_CONFIG['language']['default']);

if($_CONFIG['language']['supported'] and !isset($_CONFIG['language']['supported'][$language])){
    throw new bException(tr('startup-cli: Unknown language ":language" specified', array(':language' => $language)), 'unknown');
}

define('LANGUAGE', $language);
define('LOCALE'  , $language.(empty($_SESSION['location']['country']['code']) ? '' : '_'.$_SESSION['location']['country']['code']));

$_SESSION['language'] = $language;



/*
 * Give some startup messages, if needed
 */
if(VERBOSE){
    if(QUIET){
        throw new bException(tr('Both QUIET and VERBOSE have been specified but these options are mutually exclusive. Please specify either one or the other'), 'warning/invalid');
    }

    log_console(tr('Running in VERBOSE mode, started @ ":datetime"', array(':datetime' => date_convert(STARTTIME, 'human_datetime'))), 'white');
}

if(FORCE){
    if(TEST){
        throw new bException(tr('Both FORCE and TEST modes where specified, these modes are mutually exclusive'), 'invalid');
    }

    log_console(tr('Running in FORCE mode'), 'yellow');
}

if(TEST){
    log_console(tr('Running in TEST mode'), 'yellow');
}



/*
 * Load custom library, if available
 */
load_libs('custom');
?>
