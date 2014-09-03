<?php
/*
 * JSON library
 *
 * This library contains JSON functions
 *
 * All function names contain the json_ prefix
 *
 * @license http://opensource.org/licenses/GPL-2.0 GNU Public License, Version 2
 * @copyright Sven Oostenbrink <support@svenoostenbrink.com>, Johan Geuze
 */


/*
 * Custom JSON encoding function
 */
function json_encode_custom($source = false){
    if(is_null($source)){
        return 'null';
    }

    if($source === false){
        return 'false';
    }

    if($source === true){
        return 'true';
    }

    if(is_scalar($source)){
        if(is_numeric($source)){
            if(is_float($source)){
                // Always use "." for floats.
                $source = floatval(str_replace(',', '.', strval($source)));
            }

            // Always use "" for numerics.
            return '"'.strval($source).'"';
        }

        if(is_string($source)){
            static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
            return '"'.str_replace($jsonReplaces[0], $jsonReplaces[1], $source).'"';
        }

        return $source;
    }

    $isList = true;

    for($i = 0, reset($source); $i < count($source); $i++, next($source)){
        if(key($source) !== $i){
            $isList = false;
            break;
        }
    }

    $result = array();

    if($isList){
        foreach ($source as $v){
            $result[] = json_encode_custom($v);
        }

        return '['.join(',', $result).']';

    }else{
        foreach ($source as $k => $v){
            $result[] = json_encode_custom($k).':'.json_encode_custom($v);
        }

        return '{'.join(',', $result).'}';
    }
}



/*
 * Send correct JSON reply
 */
function json_reply($reply = null, $result = 'OK', $httpcode = null){
    if(!$reply){
        $reply = array('result' => $result);
    }

    /*
     * Auto assume result = "OK" entry if not specified
     */
    if(strtoupper($result) == 'REDIRECT'){
        $reply = array('redirect' => $reply,
                       'result'   => 'REDIRECT');

    }elseif(!is_array($reply)){
        $reply = array('result'  => strtoupper($result),
                       'message' => $reply);

    }else{
        if(empty($reply['result'])){
            $reply['result'] = $result;
        }

        $reply['result'] = strtoupper($reply['result']);
    }

    if($httpcode){
        load_libs('http');
        http_header($httpcode);
    }

    header('Content-Type: application/json');
    header('Content-Type: text/html; charset=utf-8');

    echo json_encode_custom($reply);
    die();
}



/*
 * Send correct JSON reply
 */
function json_error($message, $default_message = null){
    if(is_object($message)){
        /*
         * Assume this is an bException object
         */
        if(!($message instanceof bException)){
            throw new bException('json_error(): Specified message must either be a string or an bException ojbect, but is neither');
        }

        if($default_message === null){
            $default_message = tr('Something went wrong, please try again');
        }

        $code = $message->code;

//        if(debug('messages') and (substr($code, 0, 5) == 'user/') or ($code == 'user')){
        if(debug('messages')){
            /*
             * This is a user visible message
             */
            $message = $message->getMessages("\n");

        }elseif($default_message){
            $message = $default_message;
        }
    }

    json_reply($message, 'ERROR', 500);
}



/*
 * Validate the given JSON string
 */
function json_decode_custom($json, $as_array = true){
    /*
     * Decode the JSON data
     */
    $retval = json_decode($json, $as_array);

    /*
     * Switch and check possible JSON errors
     */
    switch(json_last_error()){
        case JSON_ERROR_NONE:
            break;

        case JSON_ERROR_DEPTH:
            throw new bException('json_decode_custom(): Maximum stack depth exceeded');

        case JSON_ERROR_STATE_MISMATCH:
            throw new bException('json_decode_custom(): Underflow or the modes mismatch');

        case JSON_ERROR_CTRL_CHAR:
            throw new bException('json_decode_custom(): Unexpected control character found');

        case JSON_ERROR_SYNTAX:
            throw new bException('json_decode_custom(): Syntax error, malformed JSON');

        case JSON_ERROR_UTF8:
            /*
             * Only PHP 5.3+
             */
            throw new bException('json_decode_custom(): Malformed UTF-8 characters, possibly incorrectly encoded');

        default:
            throw new bException('json_decode_custom(): Unknown JSON error occured');
    }

    return $retval;
}
?>
