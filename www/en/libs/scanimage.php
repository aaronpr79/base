<?php
/*
 * scanimage library
 *
 * This library allows to run the scanimage program, scan images and save them to disk
 *
 * @license http://opensource.org/licenses/GPL-2.0 GNU Public License, Version 2
 * @copyright Sven Oostenbrink <support@ingiga.com>
 */



load_config('scanimage');



/*
 * Scan for an image
 *
 * Example command: scanimage --progress  --buffer-size --contrast 50 --gamma 1.8 --jpeg-quality 80 --transfer-format JPEG --mode Color --resolution 300 --format jpeg > test.jpg
 */
function scanimage($params){
    global $_CONFIG;

    try{
        array_params($params);
        array_default($params, 'file'           , file_temp());
        array_default($params, 'contrast'       , 50);
        array_default($params, 'brightness'     , 50);
        array_default($params, 'gamma'          , 1.0);
        array_default($params, 'jpeg_quality'   , 70);
        array_default($params, 'transfer_format', 'JPEG');
        array_default($params, 'device'         , null);

        $command = 'scanimage';

        if($params['contrast']){
            if(!is_natural($params['contrast']) or ($params['contrast']) > 100){
                throw new bException(tr('scanimage(): Specified contrast ":value" is invalid. Please ensure the contrast is in between 0 and 100', array(':value' => $params['contrast'])), 'invalid');
            }

            $command .= ' --contrast '.$params['contrast'];
        }

        if($params['brightness']){
            if(!is_natural($params['brightness']) or ($params['brightness']) > 100){
                throw new bException(tr('scanimage(): Specified brightness ":value" is invalid. Please ensure the brightness is in between 0 and 100', array(':value' => $params['brightness'])), 'invalid');
            }

            $command .= ' --brightness '.$params['brightness'];
        }

        if($params['gamma']){
            if(!is_natural($params['gamma']) or ($params['gamma']) > 100){
                throw new bException(tr('scanimage(): Specified gamma ":value" is invalid. Please ensure the gamma is in between 0 and 100', array(':value' => $params['gamma'])), 'invalid');
            }

            $command .= ' --gamma '.$params['gamma'];
        }

        if($params['resolution']){
            switch($params['resolution']){
                case '75':
                case '150':
                case '300':
                case '600':
                case '1200':
                    break;

                default:
                    throw new bException(tr('scanimage(): Specified resolution ":value" is invalid. Please ensure the resolution is one of 75, 150, 150, 300, 600 or 1200', array(':value' => $params['resolution'])), 'invalid');
            }

            $command .= ' --resolution '.$params['resolution'];
        }

        if($params['transfer_format']){
            switch($params['transfer_format']){
                case '75':
                case '150':
                case '300':
                case '600':
                case '1200':
                    break;

                default:
                    throw new bException(tr('scanimage(): Specified transfer_format ":value" is invalid. Please ensure transfer_format is one of JPEG or TIFF', array(':value' => $params['transfer_format'])), 'invalid');
            }

            $command .= ' --transfer-format '.$params['transfer_format'];
        }

        if($params['mode']){
            switch($params['mode']){
                case 'lineart':
                case 'gray':
                case 'color':
                    break;

                default:
                    throw new bException(tr('scanimage(): Specified mode ":value" is invalid. Please ensure mode is one of "color" or "grey" or "lineart"', array(':value' => $params['mode'])), 'invalid');
            }

            $command .= ' --mode '.str_capitalize($params['mode']);
        }

        try{
            $result = safe_exec($command);

        }catch(Exception $e){
            $data = $e->getData();
            $line = array_unshift($data);

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
            }
        }

        return $params['file'];

    }catch(Exception $e){
        throw new bException('scanimage(): Failed', $e);
    }
}



/*
 * List the available scanner devices
 */
function scanimage_list(){
    try{
        $results = safe_exec('scanimage -L -q');
        $retval  = array('usb'       => array(),
                         'scsi'      => array(),
                         'parrallel' => array(),
                         'unknown'   => array());

        foreach($results as $result){
            if(preg_match_all('/device `imagescan:esci:([usb|scsi|parrallel]):([a-z0-9/:.-]\' is a (a-z0-9_- )/i', $result, $matches)){
                /*
                 * Found a scanner
                 */
                $retval[$matches[0]][] = array('product' => $matches[2],
                                               'url'     => $matches[1]);

            }else{
                $retval['unknown'][] = $result;
            }
        }

        return $retval;

    }catch(Exception $e){
        throw new bException('scanimage(): Failed', $e);
    }
}
?>