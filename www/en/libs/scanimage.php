<?php
/*
 * scanimage library
 *
 * This library allows to run the scanimage program, scan images and save them to disk
 *
 * @license http://opensource.org/licenses/GPL-2.0 GNU Public License, Version 2
 * @copyright Sven Oostenbrink <support@capmega.com>
 * @category Function reference
 * @package scanimage
 */



/*
 * Initialize the library. Automatically executed by libs_load(). Will automatically load the ssh library configuration
 *
 * @auhthor Sven Olaf Oostenbrink <sven@capmega.com>
 * @copyright Copyright (c) 2018 Capmega
 * @license http://opensource.org/licenses/GPL-2.0 GNU Public License, Version 2
 * @category Function reference
 * @package scanimage
 *
 * @return void
 */
function scanimage_library_init(){
    try{
        load_config('scanimage');

    }catch(Exception $e){
        throw new bException('scanimage_library_init(): Failed', $e);
    }
}



/*
 * Scan image using the scanimage command line program
 *
 * @author Sven Olaf Oostenbrink <sven@capmega.com>
 * @copyright Copyright (c) 2018 Capmega
 * @license http://opensource.org/licenses/GPL-2.0 GNU Public License, Version 2
 * @category Function reference
 * @package scanimage
 * @example scanimage --progress  --buffer-size --contrast 50 --gamma 1.8 --jpeg-quality 80 --transfer-format JPEG --mode Color --resolution 300 --format jpeg > test.jpg
 *
 * @param params $params
 * @params string device
 * @params string jpeg_quality
 * @params string buffer_size
 * @params string options
 * @params string format
 * @params string file
 * @return string the file name for the scanned image
 */
function scanimage($params){
    try{
        $params  = scanimage_validate($params);
        $command = scanimage_command().' --format tiff '.$params['options'];

        /*
         * Finish scan command and execute it
         */
        try{
            if(empty($params['batch'])){
                switch($params['format']){
                    case 'tiff':
                        $command .= ' > '.$params['file'];
                        $result   = safe_exec($command);
                        break;

                    case 'jpeg':
                        $command .= ' | convert tiff:- '.$params['file'];
                        $result   = safe_exec($command);
                        break;
                }

                file_chown($params['file']);

            }else{
                switch($params['format']){
                    case 'tiff':
                        $result   = safe_exec($command);
                        break;

                    case 'jpeg':
                        $result   = safe_exec($command);
                        break;
                }
            }

        }catch(Exception $e){
            $data = $e->getData();

            if(is_array($data)){
                $line = array_shift($data);

            }else{
                $line = '';
            }

            switch(substr($line, 0, 33)){
                case 'scanimage: open of device images':
                    /*
                     *
                     */
                    throw new bException(tr('scanimage(): Scan failed'), 'failed');

                case 'scanimage: no SANE devices found':
                    /*
                     * No scanner found
                     */
                    throw new bException(tr('scanimage(): No scanner found'), 'not-found');

                default:
                    throw new bException(tr('scanimage(): Unknown scanner process error ":e"', array(':e' => $e->getData())), $e);
            }
        }

        return $params['file'];

    }catch(Exception $e){
        throw new bException('scanimage(): Failed', $e);
    }
}



/*
 * Validate the specified scanimage parameters
 *
 * @author Sven Olaf Oostenbrink <sven@capmega.com>
 * @copyright Copyright (c) 2018 Capmega
 * @license http://opensource.org/licenses/GPL-2.0 GNU Public License, Version 2
 * @category Function reference
 * @package scanimage
 *
 * @param params $params
 * @params string device
 * @params string jpeg_quality
 * @params string buffer_size
 * @params string options
 * @params string format
 * @params string file
 * @return params The specified scanimage parameters $params validated
 */
function scanimage_validate($params){
    global $_CONFIG;

    try{
        load_libs('validate');
        $v       = new validate_form($params, 'device,jpeg_quality,format,file,buffer_size,options');
        $options = array();

        /*
         * Get the device with the device options list
         */
        if($params['device']){
            $device = scanimage_get($params['device']);

        }else{
            $device = scanimage_get_default();

            if(!$device){
                $v->setError(tr('No scanner specified and no default scanner found'));
            }
        }

        $options[] = '--device "'.$device['string'].'"';

        /*
         * Validate target file
         */
        if(!$params['file']){
            if(empty($params['batch'])){
                $v->setError(tr('No file specified'));

            }else{
                /*
                 * Batch orders have a target path with a file pattern
                 */
                if(!file_exists(dirname($params['options']['batch']))){
                    $v->setError(tr('Specified batch target path ":path" does not exists', array(':path' => $params['options']['batch'])));
                }
            }

        }elseif(file_exists($params['file']) and !FORCE){
            $v->setError(tr('Specified file ":file" already exists', array(':file' => $params['file'])));

        }else{
            file_ensure_path(dirname($params['file']));
        }

        /*
         * Validate scanner buffer size
         */
        if($params['buffer_size']){
            $v->isNatural($params['buffer_size'], tr('Please specify a valid natural numbered buffer size'));
            $v->isBetween($params['buffer_size'], 1, 1024, tr('Please specify a valid buffer size between 1 and 1024'));
        }

        /*
         * Ensure requested format is known and file ends with correct extension
         */
        switch($params['format']){
            case 'jpeg':
                $extension = 'jpg';
                break;

            case 'tiff':
                $extension = 'tiff';
                break;

            default:
                $v->setError(tr('Unknown format ":format" specified', array(':format' => $params['format'])));
        }

        if(!empty($extension)){
            if($params['batch']){
                if(str_rfrom($params['options']['batch'], '.') != 'tiff'){
                    $v->setError(tr('Specified batch file pattern ":file" has an incorrect file name extension for the requested format ":format", it should have the extension ":extension"', array(':file' => $params['options']['batch'], ':format' => $params['format'], ':extension' => $extension)));
                }

            }else{
                if(str_rfrom($params['file'], '.') != $extension){
                    $v->setError(tr('Specified file ":file" has an incorrect file name extension for the requested format ":format", it should have the extension ":extension"', array(':file' => $params['file'], ':format' => $params['format'], ':extension' => $extension)));
                }
            }
        }

        /*
         * Validate parameters against the device
         */
        if(!is_array($params['options'])){
            $v->setError(tr('Please ensure options are specified as an array'));

        }else{
            foreach($params['options'] as $key => $value){
                if(!isset($device['options'][$key])){
                    /*
                     * This may be a system driver option
                     */
                    switch($key){
                        case 'batch':
// :TODO: Implement validations!
                            break;
                        case 'batch-start':
// :TODO: Implement validations!
                            break;
                        case 'batch-count':
// :TODO: Implement validations!
                            break;
                        case 'batch-increment':
// :TODO: Implement validations!
                            break;
                        case 'batch--double':
// :TODO: Implement validations!
                            break;

                        default:
                            $v->setError(tr('Driver option ":key" is not supported by device ":device"', array(':key' => $key, ':device' => $params['device'])));
                            goto continue_validation;
                    }

                    $options[] = '--'.$key.'="'.$value.'"';
                    $params['options'] = implode(' ', $options);
                    goto continue_validation;
                }

                if(!$value){
                    unset($params['option']);
                    continue;
                }

                if(is_string($device['options'][$key])){
                    /*
                     * This is a value range
                     */
                    $device['options'][$key] = array('min' => str_until($key, '..'),
                                                     'max' => str_from($key, '..'));

                    $v->isNatural($value, tr('Please specify a numeric contrast value'));
                    $v->isBetween($value, $device['options'][$key]['min'], $device['options'][$key]['max'], tr('Please ensure that ":key" is in between ":min" and ":max"', array(':key' => $key, ':min' => $device['options'][$key]['min'], ':max' => $device['options'][$key]['max'])));

                }else{
                    $v->inArray($value, $device['options'][$key], tr('Please select a valid ":key" value', array(':key' => $key)));
                }

                if(strlen($key) == 1){
                    $options[] = '-'.$key.' "'.$value.'"';

                }else{
                    $options[] = '--'.$key.' "'.$value.'"';
                }

                continue_validation:
            }
        }

        $v->isValid();

        $params['options'] = implode(' ', $options);

        return $params;

    }catch(Exception $e){
        throw new bException('scanimage_validate(): Failed', $e);
    }
}



/*
 * List the available scanner devices from the driver database
 *
 * @author Sven Olaf Oostenbrink <sven@capmega.com>
 * @copyright Copyright (c) 2018 Capmega
 * @license http://opensource.org/licenses/GPL-2.0 GNU Public License, Version 2
 * @category Function reference
 * @package scanimage
 *
 * @return array All found scanner devices
 */
function scanimage_list(){
    try{
        /*
         * Get device data from cache
         */
        load_libs('drivers');
        $devices = drivers_get_devices('scanner');

        if($devices){
            $devices = sql_list($devices);
            return $devices;
        }

        return null;

    }catch(Exception $e){
        throw new bException('scanimage_list(): Failed', $e);
    }
}



/*
 * Search devices from the scanner drivers. This might take a while, easily up to 30 seconds or more
 *
 * @author Sven Olaf Oostenbrink <sven@capmega.com>
 * @copyright Copyright (c) 2018 Capmega
 * @license http://opensource.org/licenses/GPL-2.0 GNU Public License, Version 2
 * @category Function reference
 * @package scanimage
 * @example scanimage -L outputs would be
 * device `brother4:bus4;dev1' is a Brother MFC-L8900CDW USB scanner
 * device `imagescan:esci:usb:/sys/devices/pci0000:00/0000:00:1c.0/0000:03:00.0/usb4/4-2/4-2:1.0' is a EPSON DS-1630
 *
 * @return array All found scanner devices
 */
function scanimage_detect_devices(){
    try{
        $scanners = safe_exec(scanimage_command().' -L -q');
        $devices  = array();

        foreach($scanners as $scanner){
            if(substr($scanner, 0, 6) != 'device') continue;

            $device = null;
            $found  = preg_match_all('/device `(.+?):bus(\d+);dev(\d+)\' is a (.+)/i', $scanner, $matches);

            if($found){
                /*
                 * Found a scanner
                 */
                $device = array('raw'         => $matches[0][0],
                                'driver'      => $matches[1][0],
                                'bus'         => $matches[2][0],
                                'device'      => $matches[3][0],
                                'string'      => $matches[1][0].':bus'.$matches[2][0].';dev'.$matches[3][0],
                                'description' => $matches[4][0]);
            }else{
                $found = preg_match_all('/device `((.+?):.+?)\' is a (.+)/i', $scanner, $matches);

                if($found){
                    /*
                     * Found a scanner
                     */
                    $device = array('raw'         => $matches[0][0],
                                    'driver'      => $matches[2][0],
                                    'bus'         => null,
                                    'device'      => null,
                                    'string'      => $matches[1][0],
                                    'description' => $matches[3][0]);
                }
            }

            if($device){
                $device['manufacturer'] = trim(str_until($device['description'], ' '));
                $device['model']        = trim(str_until(str_from($device['description'], ' '), ' '));

                $devices[] = $device;
            }
        }


        return $devices;

    }catch(Exception $e){
        throw new bException('scanimage_detect_devices(): Failed', $e);
    }
}



/*
 *
 *
 * @author Sven Olaf Oostenbrink <sven@capmega.com>
 * @copyright Copyright (c) 2018 Capmega
 * @license http://opensource.org/licenses/GPL-2.0 GNU Public License, Version 2
 * @category Function reference
 * @package scanimage
 * @example scanimage -L outputs would be
 * device `brother4:bus4;dev1' is a Brother MFC-L8900CDW USB scanner
 * device `imagescan:esci:usb:/sys/devices/pci0000:00/0000:00:1c.0/0000:03:00.0/usb4/4-2/4-2:1.0' is a EPSON DS-1630
 *
 * @return array All found scanner devices
 */
function scanimage_update_devices(){
    try{
        load_libs('drivers');
        drivers_clear_devices('scanner');

        $scanners = scanimage_detect_devices();
        $failed   = 0;

        foreach($scanners as $scanner){
            unset($options);

            try{
                $scanner = drivers_add_device($scanner, 'scanner');
                log_file(tr('Added device ":device" with device string ":string"', array(':device' => $scanner['description'], ':string' => $scanner['string'])), 'scanner');

            }catch(Exception $e){
                $failed++;

                /*
                 * One device failed to add, continue adding the rest
                 */
                log_file(tr('Failed to add device ":device" with device string ":string"', array(':device' => $scanner['description'], ':string' => $scanner['string'])), 'scanner');
                log_file(tr('Scanner data:'), 'scanner');
                log_file($scanner, 'scanner');
                log_file(tr('Scanner exception:'), 'exceptions');
                log_file($e, 'scanner');
                continue;
            }

            try{
                $options = scanimage_get_options($scanner['string']);
                $count   = drivers_add_options($scanner['id'], $options);
                log_file(tr('Added ":count" options for device string ":string"', array(':string' => $scanner['string'], ':count' => $count)), 'scanner');

            }catch(Exception $e){
                $failed++;
                drivers_device_status($scanner['string'], 'failed');

                /*
                 * Options for one device failed to add, continue adding the rest
                 */
                if(empty($options)){
                    /*
                     * HP device? Give information on how to solve this issue
                     */
                    log_file(tr('Failed to retrieve options for device ":device" with device string ":string", scanner device has been disabled', array(':device' => $scanner['description'], ':string' => $scanner['string'])), 'scanner');
                    log_file(tr('Scanner options exception:'), 'exceptions');
                    log_file($e, 'scanner');

                    if(strstr($scanner['string'], 'hpaio') or strstr($scanner['string'], 'Hewlett-Packard') or strstr($scanner['string'], ' HP')){
                        if(strstr($e->getData(), 'Error during device I/O')){
                            log_file(tr('*** INFO *** Device ":string" appears to be an Hewlett Packard scanner with an "Error during device I/O" error. This possibly is a known issue with a solution', array(':string' => $scanner['string'])), 'scanner');
                            log_file(tr('Uninstall the current "hplip" package from your installation, and install the official HP version'), 'scanner');
                            log_file(tr('For more information, see ":url"', array(':url' => 'https://unix.stackexchange.com/questions/272951/hplip-hpaio-error-during-device-i-o')), 'scanner');
                            log_file(tr('For more information, see ":url"', array(':url' => '//https://developers.hp.com/hp-linux-imaging-and-printing')), 'scanner');
                        }
                    }


                }else{
                    log_file(tr('Failed to store options for device ":device" with device string ":string", scanner device has been disabled', array(':device' => $scanner['description'], ':string' => $scanner['string'])), 'scanner');
                    log_file(tr('Options data:'), 'scanner');
                    log_file($options, 'scanner');
                    log_file(tr('Scanner exception:'), 'exceptions');
                    log_file($e, 'scanner');
                }

                continue;
            }
        }

        if(empty($failed)){
            return $scanners;
        }

        throw new bException(tr('scanimage_update_devices(): Failed to add ":count" scanners or driver options, see file log for more information', array(':count' => $failed)), 'warning/failed');

    }catch(Exception $e){
        throw new bException('scanimage_update_devices(): Failed', $e);
    }
}



/*
 * Get driver options for the specified scanner device from the drivers
 *
 * @author Sven Olaf Oostenbrink <sven@capmega.com>
 * @copyright Copyright (c) 2018 Capmega
 * @license http://opensource.org/licenses/GPL-2.0 GNU Public License, Version 2
 * @category Function reference
 * @package scanimage
 * @note Devices confirmed to be working:
 * device `brother4:bus4;dev1' is a Brother MFC-L8900CDW USB scanner
 * device `imagescan:esci:usb:/sys/devices/pci0000:00/0000:00:1c.0/0000:03:00.0/usb4/4-2/4-2:1.0' is a EPSON DS-1630
 * device `hpaio:/usb/HP_LaserJet_CM1415fnw?serial=00CNF8BC4K04' is a Hewlett-Packard HP_LaserJet_CM1415fnw all-in-one
 *
 * @param string $device
 * @return
 */
function scanimage_get_options($device){
    try{
        $skip    = true;
        $results = safe_exec(scanimage_command().' -h -d "'.$device.'"');
        $retval  = array();

        foreach($results as $result){
            if(strstr($result, 'failed:')){
                if(strtolower(trim(str_from($result, 'failed:'))) == 'invalid argument'){
                    throw new bException(tr('scanimage_get_options(): Options scan for device ":device" failed with ":e". This could possibly be a permission issue; does the current process user has the required daccess to scanner devices? Please check this user\'s groups!', array(':device' => $device, ':e' => trim(str_from($result, 'failed:')))), 'failed', $result);
                }

                throw new bException(tr('scanimage_get_options(): Options scan for device ":device" failed with ":e"', array(':device' => $device, ':e' => trim(str_from($result, 'failed:')))), 'failed', $result);
            }

            if($skip){
                if(preg_match('/Options specific to device \`'.str_replace(array('.', '/'), array('\.', '\/'), $device).'\':/', $result)){
                    $skip = false;
                    continue;
                }

                continue;
            }

            $result = trim($result);
            $status = null;

            if(substr($result, 0, 1) != '-'){
                /*
                 * Doesn't contain driver info
                 */
                continue;
            }

            /*
             * These are driver keys
             */
            if(substr($result, 0, 2) == '--'){
                /*
                 * These are double dash options
                 */
                if(!preg_match_all('/--([a-zA-Z-]+)(.+)/', $result, $matches)){
                    throw new bException(tr('scanimage_get_options(): Unknown driver line format encountered for key "resolution"'), 'unknown');
                }
// :DEBUG: Do not remove the folowing commented line(s), its for debugging purposes
//show($matches);

                $key     = $matches[1][0];
                $data    = $matches[2][0];
                $default = str_rfrom($data, ' [');
                $default = trim(str_runtil($default, ']'));
                $data    = trim(str_runtil($data, ' ['));

                if($default == 'inactive'){
                    $status  =  $default;
                    $default = null;
                }

// :DEBUG: Do not remove the folowing commented line(s), its for debugging purposes
//show($key);
//show($data);
//show($default);
                if($data == '[=(yes|no)]'){
                    /*
                     * Options are yes or no
                     */
                    $data = array('yes', 'no');

                }else{
                    switch($key){
                        case 'mode':
                            // FALLTHROUGH
                        case 'scan-area':
                            // FALLTHROUGH
                        case 'source':
                            $data = explode('|', $data);
                            break;

                        case 'resolution':
                            $data = str_replace('dpi', '', $data);

                            if(strstr($data, '..')){
                                /*
                                 * Resolutions given as a range instead of discrete values
                                 */
                                $data = array(trim($data));

                            }else{
                                $data = explode('|', $data);
                            }

                            break;

                        case 'brightness':
                            $data = str_until($data, '(');
                            $data = str_replace('%', '', $data);
                            $data = array(trim($data));
                            break;

                        case 'contrast':
                            $data = str_until($data, '(');
                            $data = str_replace('%', '', $data);
                            $data = array(trim($data));
                            break;

                        default:
                            if(!strstr($data, '|')){
                                if(!strstr($data, '..')){
                                    throw new bException(tr('scanimage_get_options(): Unknown driver line ":result" found', array(':result' => $result)), 'unknown');
                                }

                                /*
                                 * Unknown entry, but treat it as a range
                                 */
                                $data = str_until($data, '(');
                                $data = str_replace('%', '', $data);
                                $data = array(trim($data));

                            }else{
                                /*
                                 * Unknown entry, but treat it as a distinct list
                                 */
                                $data = str_until($data, '(');
                                $data = str_replace('%', '', $data);
                                $data = explode('|', $data);
                            }
                    }
                }

            }else{
                /*
                 * These are single dash options
                 */
                if(!preg_match_all('/-([a-zA-Z-]+)(.+)/', $result, $matches)){
                    throw new bException(tr('scanimage_get_options(): Unknown driver line format encountered for key "resolution"'), 'unknown');
                }
// :DEBUG: Do not remove the folowing commented line(s), its for debugging purposes
//show($matches);

                $key     = $matches[1][0];
                $data    = $matches[2][0];
                $default = str_rfrom($data, ' [');
                $default = trim(str_runtil($default, ']'));
                $data    = str_runtil($data, ' [');
                $data    = trim(str_replace('mm', '', $data));

                if($default == 'inactive'){
                    $status  =  $default;
                    $default = null;
                }

// :DEBUG: Do not remove the folowing commented line(s), its for debugging purposes
//show($key);
//show($data);
//show($default);
                switch($key){
                    case 'l':
                        $data = str_until($data, '(');
                        $data = str_replace('%', '', $data);
                        $data = array(trim($data));
                        break;

                    case 't':
                        $data = str_until($data, '(');
                        $data = str_replace('%', '', $data);
                        $data = array(trim($data));
                        break;

                    case 'x':
                        $data = str_until($data, '(');
                        $data = str_replace('%', '', $data);
                        $data = array(trim($data));
                        break;

                    case 'y':
                        $data = str_until($data, '(');
                        $data = str_replace('%', '', $data);
                        $data = array(trim($data));
                        break;

                    default:
                        throw new bException(tr('scanimage_get_options(): Unknown driver key ":key" found', array(':key' => $key)), 'unknown');
                }
            }

            $retval[$key] = array('data'    => $data,
                                  'status'  => $status,
                                  'default' => $default);
        }

        return $retval;

    }catch(Exception $e){
        throw new bException(tr('scanimage_get_options(): Failed for device ":device"', array(':device' => $device)), $e);
    }
}



/*
 * Return the data on the default scanner
 *
 * @author Sven Olaf Oostenbrink <sven@capmega.com>
 * @copyright Copyright (c) 2018 Capmega
 * @license http://opensource.org/licenses/GPL-2.0 GNU Public License, Version 2
 * @category Function reference
 * @package scanimage
 *
 * @return
 */
function scanimage_get_default(){
    try{
        $scanners = scanimage_list();

        foreach($scanners as $devices_id => $scanner){
            if($scanner['default']){
                load_libs('drivers');

                $scanner['options'] = drivers_get_options($devices_id);
                return $scanner;
            }
        }

        return null;

    }catch(Exception $e){
        throw new bException('scanner_get_default(): Failed', $e);
    }
}



/*
 * Return the data on the default scanner
 *
 * @author Sven Olaf Oostenbrink <sven@capmega.com>
 * @copyright Copyright (c) 2018 Capmega
 * @license http://opensource.org/licenses/GPL-2.0 GNU Public License, Version 2
 * @category Function reference
 * @package scanimage
 *
 * @param string $device_string The device to get and return data from
 * @return array All found data for the specified device
 */
function scanimage_get($device_string){
    try{
        load_libs('drivers');
        $scanner = drivers_get_device($device_string);

        if(!$scanner){
            throw new bException(tr('scanner_get(): Specified scanner with device string ":string" does not exist', array(':string' => $device_string)), 'not-exist');
        }

        $scanner['options'] = drivers_get_options($scanner['id']);
        return $scanner;

    }catch(Exception $e){
        throw new bException('scanner_get(): Failed', $e);
    }
}



/*
 * Create and return HTML for a select component showing available scanners
 *
 * @author Sven Olaf Oostenbrink <sven@capmega.com>
 * @copyright Copyright (c) 2018 Capmega
 * @license http://opensource.org/licenses/GPL-2.0 GNU Public License, Version 2
 * @category Function reference
 * @package scanimage
 *
 * @param params $params
 * @return string The HTML for the select box
 */
function scanimage_select($params){
    try{
        array_ensure($params);
        array_default($params, 'name'      , 'scanner');
        array_default($params, 'autosubmit', true);
        array_default($params, 'none'      , false);
        array_default($params, 'empty'     , tr('No scanners available'));

        $scanners = scanimage_list();

        foreach($scanners as $scanner){
            $params['resource'][$scanner['string']] = $scanner['description'];
        }

        $html = html_select($params);

        return $html;

    }catch(Exception $e){
        throw new bException('scanimage_select(): Failed', $e);
    }
}



/*
 * Create and return HTML for a select component showing available resolutions
 * for the specified scanner device
 *
 * @author Sven Olaf Oostenbrink <sven@capmega.com>
 * @copyright Copyright (c) 2018 Capmega
 * @license http://opensource.org/licenses/GPL-2.0 GNU Public License, Version 2
 * @category Function reference
 * @package scanimage
 *
 * @param params $params
 * @return string The HTML for the resolution select box
 */
function scanimage_select_resolution($params){
    try{
        array_ensure($params, 'string');
        array_default($params, 'name'      , 'scanner');
        array_default($params, 'autosubmit', true);
        array_default($params, 'none'      , false);
        array_default($params, 'empty'     , tr('No scanners available'));

        $params['resource'] = sql_query('SELECT    `drivers_options`.`value` AS `id`,
                                                   `drivers_options`.`value`

                                         FROM      `drivers_devices`

                                         LEFT JOIN `drivers_options`
                                         ON        `drivers_options`.`devices_id` = `drivers_devices`.`id`
                                         AND       `drivers_options`.`key`        = "resolution"

                                         WHERE     `drivers_devices`.`string`     = :string',

                                         array(':string' => $params['string']));

        $html = html_select($params);

        return $html;

    }catch(Exception $e){
        throw new bException('scanimage_select_resolution(): Failed', $e);
    }
}



/*
 * Returns the scanimage command. This, by default is "sudo scanimage" but by configuration may be something else
 *
 * @author Sven Olaf Oostenbrink <sven@capmega.com>
 * @copyright Copyright (c) 2018 Capmega
 * @license http://opensource.org/licenses/GPL-2.0 GNU Public License, Version 2
 * @category Function reference
 * @package scanimage
 *
 * @return string The scanimage command
 */
function scanimage_command(){
    global $_CONFIG;

    try{
        if(empty($_CONFIG['scanimage']['sudo'])){
            return 'scanimage';
        }

        return 'sudo scanimage';

    }catch(Exception $e){
        throw new bException('scanimage_command(): Failed', $e);
    }
}
?>
