<?php
/*
 * HTML library, containing all sorts of HTML functions
 *
 * @license http://opensource.org/licenses/GPL-2.0 GNU Public License, Version 2
 * @copyright Sven Oostenbrink <support@svenoostenbrink.com>
 */



/*
 * Only allow execution on shell scripts
 */
function html_only(){
    if(PLATFORM != 'http'){
        throw new bException('html_only(): This can only be done over HTML', 'htmlonly');
    }
}



/*
 * Generate and return the HTML footer
 */
function html_iefilter($html, $filter){
    try{
        if(!$filter){
            return $html;
        }

        if($mod = str_until(str_from($filter, '.'), '.')){
            return "\n<!--[if ".$mod.' IE '.str_rfrom($filter, '.')."]>\n\t".$html."\n<![endif]-->\n";

        }elseif($filter == 'ie'){
            return "\n<!--[if IE ]>\n\t".$html."\n<![endif]-->\n";
        }

        return "\n<!--[if IE ".str_from($filter, 'ie')."]>\n\t".$html."\n<![endif]-->\n";

    }catch(Exception $e){
        throw new bException('html_iefilter(): Failed', $e);
    }
}



/*
 * Store libs for later loading
 */
function html_load_css($files = '', $media = null){
    global $_CONFIG;

    try{
        if(!$files){
            $files = array();
        }

        if(!is_array($files)){
            if(!is_string($files)){
                throw new bException('html_load_css(): Invalid files specification');
            }

            $files = explode(',', $files);
        }

        $min = $_CONFIG['cdn']['min'];

        if(empty($GLOBALS['css'])){
            $GLOBALS['css'] = array();
        }

        foreach($files as $file){
            if($file == 'style') continue;

            $GLOBALS['css'][$file] = array('min'   => $min,
                                           'media' => $media);
        }

    }catch(Exception $e){
        throw new bException('html_load_css(): Failed', $e);
    }
}



/*
 * Display libs in header
 */
function html_generate_css(){
    global $_CONFIG;

    try{
        if(empty($GLOBALS['css'])){
            $GLOBALS['css'] = array();
        }

        if($GLOBALS['page_is_admin']){
            /*
             * Use normal admin CSS
             */
            $GLOBALS['css']['admin'] = array('media' => null);

        }elseif($GLOBALS['page_is_mobile'] or empty($_CONFIG['bootstrap']['enabled'])){
            /*
             * Use normal, default CSS
             */
            $GLOBALS['css']['style'] = array('media' => null);

        }else{
            /*
             * Use bootstrap CSS
             */
            $GLOBALS['css'][$_CONFIG['bootstrap']['css']] = array('media' => null);
            $GLOBALS['css']['style']                      = array('media' => null);
//            $GLOBALS['css'][''bootstrap-theme']           => array('media' => null),
        }

        if(!empty($_CONFIG['cdn']['css']['post']) and !$GLOBALS['page_is_admin']){
            $GLOBALS['css']['post'] = array('min' => $_CONFIG['cdn']['min'], 'media' => (is_string($_CONFIG['cdn']['css']['post']) ? $_CONFIG['cdn']['css']['post'] : ''));
        }

        $retval = '';
        $min    = $_CONFIG['cdn']['min'];

        foreach($GLOBALS['css'] as $file => $meta) {
            if(!$file) continue;

            $html = '<link rel="stylesheet" type="text/css" href="'.$_CONFIG['root'].'/pub/css/'.((SUBENVIRONMENT and (substr($file, 0, 5) != 'base/')) ? SUBENVIRONMENT.'/' : '').(!empty($GLOBALS['page_is_mobile']) ? 'mobile/' : '').$file.($min ? '.min.css' : '.css').'"'.($meta['media'] ? ' media="'.$meta['media'].'"' : '').'>';

            if(substr($file, 0, 2) == 'ie'){
                $retval .= html_iefilter($html, str_until(str_from($file, 'ie'), '.'));

            }else{
                /*
                 * Hurray, normal stylesheets!
                 */
                $retval .= $html."\n";
            }
        }

        return $retval;

    }catch(Exception $e){
        throw new bException('html_generate_css(): Failed', $e);
    }
}



/*
 * Store list of libs that should be loaded in the header
 *
 * $option may be either "async" or "defer", see http://www.w3schools.com/tags/att_script_async.asp
 */
function html_load_js($files = '', $option = null, $ie = null){
    global $_CONFIG;

    try{
        if(!$files){
            $files = array();
        }

        if(!is_array($files)){
            if(!is_string($files)){
                throw new bException('html_load_js(): Invalid files specification');
            }

            $files = explode(',', $files);
        }

        //if($min === null){
        //    $min = $_CONFIG['cdn']['min'];
        //}

        if(!isset($GLOBALS['js'])){
            $GLOBALS['js'] = array();
        }

        foreach($files as $file){
            if(substr($file, 0, 4) != 'http') {
                /*
                 * Compatibility code: ALL LOCAL JS FILES SHOULD ALWAYS BE SPECIFIED WITHOUT .js OR .min.js!!
                 */
// :TODO: SEND EMAIL NOTIFICATIONS IF THESE ARE FOUND!
                if(substr($file, -3, 3) == '.js'){
                    $file = substr($file, 0, -3);

                }elseif((substr($file, -3, 3) == '.js') or (substr($file, -7, 7) == '.min.js')){
                    $file = substr($file, 0, -7);
                }

            }

            $data = array();

            if($option){
                $data['option'] = $option;
            }

            if($ie){
                $data['ie'] = $ie;
            }

            $GLOBALS['js'][$file] = $data;
        }

    }catch(Exception $e){
        throw new bException('html_load_js(): Failed', $e);
    }
}



/*
 * Display libs in header and or footer
 */
function html_generate_js(){
    global $_CONFIG;

    try{
        if(empty($GLOBALS['js'])){
            return '';
        }

        /*
         * Shortcut to JS configuration
         */
        $js     = $_CONFIG['cdn']['js'];
        $min    = ($_CONFIG['cdn']['min'] ? '.min' : '');

        $libs   = array();
        $retval = '';
        $footer = '';

        /*
         * Set to load default JS libraries
         */
        foreach($js['default_libs'] as $lib){
            if($lib == 'base/jquery'){
                $lib .= $js['jquery_version'];
            }

            $libs[$lib] = array();
        }

        /*
         * Load JS libraries
         */
        foreach($GLOBALS['js'] = array_merge($libs, $GLOBALS['js']) as $file => $data) {
            if(!$file) continue;

            $check = str_rfrom(str_starts($file, '/'), '/');
            $file  = str_replace(array('<', '>'), '', $file);

            if($check == 'jquery')    continue; // jQuery js is always loaded in the header
            if($check == 'bootstrap') continue; // bootstrap js is always loaded in the header

            if(substr($file, 0, 4) == 'http') {
                /*
                 * These are external scripts
                 */
                $html = '<script'.(!empty($data['option']) ? ' '.$data['option'] : '').' type="text/javascript" src="'.$file.'"></script>';

            } else {
                /*
                 * These are local scripts
                 *
                 * Check if linked libraries are enabled, and if so, if its part of any of these.
                 */
                if($js['use_linked']){
                    /*
                     * Since we may need to break out of multiple loops, keep track of break variable.
                     * Skip is used to check if a linked library has been sent or not and we can skip current library
                     */
                    $break = false;
                    $skip  = false;

                    foreach($js['linked'] as $linked => $libraries){
                        foreach($libraries as $library){
                            if($file == $library){
                                $break = true;

                                /*
                                 * This file is inside a linked library. Send the linked library instead
                                 */
                                if(!empty($js['linked'][$linked]['sent'])){
                                    /*
                                     * The linked library has already been sent
                                     */
                                    $skip  = true;

                                }else{
                                    $file = $linked;
                                }
                            }

                            if($break) break;
                        }

                        if($break) break;
                    }

                    if($skip) continue;
                }

                $html = '<script'.(!empty($data['option']) ? ' '.$data['option'] : '').' type="text/javascript" src="'.$_CONFIG['root'].'/pub/js/'.$file.$min.'.js"></script>';
            }

            /*
             * Add the scripts with IE only filters?
             */
            if(isset_get($data['ie'])){
                $html = html_iefilter($html, $data['ie']);

            }else{
                $html = $html."\n";
            }

            if($check[0] == '>' or (!empty($js['load_delayed']) and ($check[0] != '<'))){
                /*
                 * Add this script in the footer
                 */
                $footer .= $html;

            }else{
                /*
                 * Add this script in the header
                 */
                $retval .= $html;
            }
        }

        /*
         * Should all JS scripts be loaded at the end (right before the </body> tag)?
         * This may be useful for site startup speedups
         */
        if(!empty($footer)){
            $GLOBALS['footer'] = $footer.isset_get($GLOBALS['footer'], '').isset_get($GLOBALS['script_delayed'], '');
        }

        /*
         * Always load jQuery!
         * Always load jQuery in the HEAD so that in site <script> that use jQuery will work
         */
        $jquery = '<script'.(!empty($data['option']) ? ' '.$data['option'] : '').' type="text/javascript" src="'.$_CONFIG['root'].'/pub/js/base/jquery'.$min.".js\"></script>\n";

        if(!$GLOBALS['page_is_mobile'] and !empty($_CONFIG['bootstrap']['enabled'])){
            /*
             * Use bootstrap JS
             */
            $jquery .= '<script'.(!empty($data['option']) ? ' '.$data['option'] : '').' type="text/javascript" src="'.$_CONFIG['root'].'/pub/js/'.$_CONFIG['bootstrap']['js'].$min.".js\"></script>\n";
        }

        return $jquery.$retval;

    }catch(Exception $e){
        throw new bException('html_generate_js(): Failed', $e);
    }
}



/*
 * Generate and return the HTML header
 */
function html_header($params = null, $meta = array()){
    global $_CONFIG;

    try{
        array_params($meta);
        array_params($params, 'title');

        array_default($params, 'http'          , 'html');
        array_default($params, 'doctype'       , 'html');
        array_default($params, 'html'          , 'html');
        array_default($params, 'body'          , '<body>');
        array_default($params, 'title'         , isset_get($meta['title']));
        array_default($params, 'meta'          , $meta);
        array_default($params, 'link'          , array());
        array_default($params, 'extra'         , '');
        array_default($params, 'favicon'       , true);
        array_default($params, 'prefetch_dns'  , $_CONFIG['prefetch']['dns']);
        array_default($params, 'prefetch_files', $_CONFIG['prefetch']['files']);

        if(!empty($params['js'])){
            html_load_js($params['js']);
        }

        if(!empty($params['css'])){
            html_load_css($params['css']);
        }

        if(empty($params['meta']['description'])){
            throw new bException('html_header(): No header meta description specified (SEO!)');
        }

        if(empty($params['meta']['keywords'])){
            throw new bException('html_header(): No header meta keywords specified (SEO!)');
        }

        if(!empty($params['meta']['noindex'])){
            $params['meta']['robots'] = 'noindex';
            unset($params['meta']['noindex']);
        }

        if(!empty($_CONFIG['meta'])){
            /*
             * Add default configured meta tags
             */
            $params['meta'] = array_merge($_CONFIG['meta'], $params['meta']);
        }

        if(!empty($_CONFIG['bootstrap']['enabled'])){
            array_ensure($params['meta'], 'viewport', $_CONFIG['bootstrap']['viewport']);
        }

        /*
         * Add meta tag no-index for non production environments and admin pages
         */
        if((ENVIRONMENT != 'production') || $GLOBALS['page_is_admin']){
           $params['meta']['robots'] = 'noindex';
        }

        $meta['title'] = html_title($meta['title']);
        unset($meta['title']);

        $retval = "<!DOCTYPE ".$params['doctype'].">\n".
                  "<".$params['html'].">\n".
                  "<head>\n".
                  "<meta http-equiv=\"Content-Type\" content=\"text/html;charset=".$_CONFIG['charset']."\">\n".
                  "<title>".$params['title']."</title>\n";

        foreach($params['prefetch_dns'] as $prefetch){
            $retval .= '<link rel="dns-prefetch" href="//'.$prefetch."\">\n";
        }

        foreach($params['prefetch_files'] as $prefetch){
            $retval .= '<link rel="prefetch" href="'.$prefetch."\">\n";
        }

        unset($prefetch);

        $retval .=    html_generate_css().
                      html_generate_js();

        /*
         * Add required fonts
         */
        if(!empty($_CONFIG['cdn']['fonts'])){
            if((ENVIRONMENT == 'production') or (empty($_CONFIG['cdn']['production_fonts']))){
                foreach($_CONFIG['cdn']['fonts'] as $font){
                    $retval .= "<link href=\"".$font."\" rel=\"stylesheet\" type=\"text/css\">\n";
                }
            }
        }

        /*
         * Add all other meta tags
         * Only add keywords with contents, all that have none are considerred
         * as false, and do-not-add
         */
        foreach($params['meta'] as $keyword => $contents){
            if($contents){
                $retval .= "<meta name=\"".$keyword."\" content=\"".$contents."\">\n";
            }
        }

        $retval .= html_favicon($params['favicon']).$params['extra'];

        /*
         * Add viewport meta tag for mobile devices
         */
        if(!empty($_SESSION['mobile'])){
            if(!empty($_CONFIG['mobile']['viewport'])){
                $retval .= $_CONFIG['mobile']['viewport']."\n";
            }
        }

        return $retval."</head>\n".
                       $params['body']."\n";

    }catch(Exception $e){
        throw new bException('html_header(): Failed', $e);
    }
}



/*
 * Generate and return the HTML footer
 */
function html_footer(){
    global $_CONFIG;

    try{
        if(empty($GLOBALS['footer'])){
            return "</body>\n</html>";
        }

        return $GLOBALS['footer']."</body>\n</html>";

    }catch(Exception $e){
        throw new bException('html_footer(): Failed', $e);
    }
}



/*
 * Generate and return the HTML footer
 */
function html_title($params){
    global $_CONFIG;

    try{
        $title = $_CONFIG['title'];

        /*
         * If no params are specified then just return the given title
         */
        if(empty($params)){
            return $title;
        }

        /*
         * If the given params is a plain string then override the configured title with this
         */
        if(!is_array($params)){
            if(is_string($params)){
                return $params;
            }

            throw new bException('html_title(): Invalid title specified');
        }

        /*
         * Do a search / replace on all specified items to create correct title
         */
        foreach($params as $key => $value){
            $title = str_replace($key, $value, $title);
        }

        return $title;

    }catch(Exception $e){
        throw new bException('html_title(): Failed', $e);
    }
}



/*
 * Show a flash message with the specified message
 */
function html_flash($class = null){
    global $_CONFIG;

    try{
        if(PLATFORM != 'http'){
            throw new bException('html_flash(): This function can only be executed on a webserver!');
        }

        if($class == null){
            /*
             *
             */
            $class = $_CONFIG['flash']['default_class'];
        }

        if(!isset($_SESSION['flash'])){
            /*
             * Auto create
             */
            $_SESSION['flash'] = array();
        }

        if(!is_array($_SESSION['flash'])){
            /*
             * $_SESSION['flash'] should always be an array. Don't crash on minor detail, just correct and continue
             */
// :TODO: Should this be an exception?
            log_error(tr('html_flash(): Invalid flash structure in $_SESSION array, it should always be an array but it is a "%type%". Be sure to always use html_flash_set() to add new flash messages', array('%type%' => gettype($_SESSION['flash']))), 'invalid');
            $_SESSION['flash'] = array();
        }

        $retval = '';

        foreach($_SESSION['flash'] as $id => $message){
            //if(is_object($message) and $message instanceof Exception){
            //    $message = array('type'    => 'error',
            //                     'message' => $message->getMessage(),
            //                     'class'   => $class);
            //}

            if(($class != $message['class']) and ($class != 'all')){
                continue;
            }

            /*
             * The message contains what type and basic (usually this comes from $_SESSION[flash]
             */
            $type     = $message['type'];
            $class    = $message['class'];
            $message  = $message['message'];

            if(($type == 'error') and (ENVIRONMENT == 'production')){
                $message = tr('Something went wrong, please try again later');
            }

            switch(strtolower($type)){
                case 'info':
                    $type = 'information';
                    // FALLTHROUGH

                case 'information':
                    break;

                case 'success':
                    break;

                case 'error':
                    break;

                case 'warning':
                    $type = 'attention';
                    // FALLTHROUGH

                case 'attention':
                    break;

                default:
                    throw new bException(tr('html_flash(): Unknown flash type "%type%" specified. Please specify one of "info" or "success" or "attention" or "error"', array('%type%' => str_log($type))), 'flash/unknown');
            }

            if(!debug()){
                /*
                 * Don't show "function_name(): " part of message
                 */
                $message = trim(str_from($message, '():'));
            }

            /*
             * Set the indicator that we have added flash messages
             */
            $GLOBALS['flash'] = true;

    //        $retval .= '<div class="sys_bg sys_'.$type.'"></div><div class="'.$_CONFIG['flash']['css_name'].' sys_'.$type.'">'.$message.'</div>';
            $retval .= '<div class="'.$_CONFIG['flash']['css_name'].' '.$_CONFIG['flash']['prefix'].$type.($class ? ' '.$class : '').'">'.$_CONFIG['flash']['button'].$message.'</div>';

            unset($_SESSION['flash'][$id]);
        }

        /*
         * Add an extra hidden flash message box that can respond for jsFlashMessages
         */
        return $retval.'<div id="jsFlashMessage" class="'.$_CONFIG['flash']['css_name'].' '.$_CONFIG['flash']['prefix'].($class ? ' '.$class : '').'" style="display:none;"></div>';

    }catch(Exception $e){
        throw new bException('html_flash(): Failed', $e);
    }
}



/*
 * Show a flash message with the specified message
 */
function html_flash_set($messages, $type = 'info', $class = null){
    global $_CONFIG;

    try{
        if(!$messages){
            /*
             * Wut? no message?
             */
            return false;
        }

        if($class == null){
            $class = $_CONFIG['flash']['default_class'];
        }

        /*
         * Ensure session flash data consistency
         */
        if(empty($_SESSION['flash'])){
            $_SESSION['flash'] = array();

        }elseif(!is_array($_SESSION['flash'])){
            $_SESSION['flash'] = array($_SESSION['flash']);
        }

        if(!is_array($messages)){
            if(is_object($messages) and $messages instanceof Exception){
                $type     = (($type == 'warning') ? 'warning' : 'error');
                $messages = $messages->getMessage();
                $messages = (strstr($messages, '():') ? trim(str_from($messages, '():')) : $messages);
            }

            if(is_string($messages) and (strpos($messages, "\n") !== false)){
                $messages = explode("\n", $messages);

            }else{
                $messages = array($messages);
            }
        }

        foreach($messages as $message){
            $_SESSION['flash'][] = array('type'    => $type,
                                         'class'   => $class,
                                         'message' => $message);
        }

    }catch(Exception $e){
        throw new bException('html_flash_set(): Failed', $e);
    }
}



/*
 * Returns true if there is an HTML message with the specified class
 */
function html_flash_class($class = null){
    try{
        if($class == null){
            $class = $_CONFIG['flash']['default_class'];
        }

        if(isset($_SESSION['flash'])){
            foreach($_SESSION['flash'] as $message){
                if($message['class'] == $class){
                    return true;
                }
            }
        }

        return false;

    }catch(Exception $e){
        throw new bException('html_flash_class(): Failed', $e);
    }
}



/*
 * Return an HTML <select> list
 */
function html_select($params, $selected = null, $name = '', $none = '', $class = '', $option_class = '', $disabled = false) {
    static $count = 0;

    try{
        array_params ($params, 'resource');
        array_default($params, 'class'       , $class);
        array_default($params, 'disabled'    , $disabled);
        array_default($params, 'name'        , $name);
        array_default($params, 'id'          , $params['name']);
        array_default($params, 'none'        , tr('None selected'));
        array_default($params, 'empty'       , tr('None available'));
        array_default($params, 'option_class', $option_class);
        array_default($params, 'selected'    , $selected);
        array_default($params, 'bodyonly'    , false);
        array_default($params, 'autosubmit'  , false);
        array_default($params, 'onchange'    , '');
        array_default($params, 'hide_empty'  , false);
        array_default($params, 'autofocus'   , false);

        if(!$params['name']){
            throw new bException('html_select(): No name specified');
        }

        if($params['autosubmit']){
            if($params['class']){
                $params['class'] .= ' autosubmit';

            }else{
                $params['class']  = 'autosubmit';
            }
        }

        if(!$params['resource']){
            if($params['hide_empty']){
                return '';
            }

            if(is_numeric($params['disabled'])){
                $params['disabled'] = true;

            }else{
                if(is_array($params['resource'])){
                    $params['disabled'] = ((count($params['resource']) + ($params['name'] ? 1 : 0)) <= $params['disabled']);

                }elseif(is_object($params['resource'])){
                    $params['disabled'] = (($params['resource']->rowCount() + ($params['name'] ? 1 : 0)) <= $params['disabled']);

                }elseif($params['resource'] === null){
                    $params['disabled'] = true;

                }else{
                    throw new bException(tr('html_select(): Invalid resource of type "%type%" specified, should be either null, an array, or a PDOStatement object', array('%type%' => gettype($params['resource']))), 'invalid');
                }
            }
        }

        if($params['bodyonly']){
            return html_select_body($params);
        }

        /*
         * <select> class should not be applied to <option>
         */
        $class = $params['class'];
        unset($params['class']);

        $body = html_select_body($params);

        if(substr($params['id'], -2, 2) == '[]'){
            $params['id'] = substr($params['id'], 0, -2).$count++;
        }

        if($params['disabled']){
            /*
             * Add a hidden element with the name to ensure that multiple selects with [] will not show holes
             */
            return '<select'.($params['id'] ? ' id="'.$params['id'].'_disabled"' : '').' name="'.$params['name'].'" '.($class ? ' class="'.$class.'"' : '').' readonly disabled>'.
                    $body.'</select><input type="hidden" name="'.$params['name'].'" >';
        }else{
            $retval = '<select'.($params['id'] ? ' id="'.$params['id'].'"' : '').' name="'.$params['name'].'" '.($class ? ' class="'.$class.'"' : '').($params['disabled'] ? ' disabled' : '').($params['autofocus'] ? ' autofocus' : '').'>'.
                      $body.'</select>';
        }

        if($params['onchange']){
            /*
             * Execute the JS code for an onchange
             */
            $retval .= html_script('$("#'.$params['id'].'").change(function(){ '.$params['onchange'].' });');

        }

        if(!$params['autosubmit']){
            /*
             * There is no onchange and no autosubmit
             */
            return $retval;

        }elseif($params['autosubmit'] === true){
            /*
             * By default autosubmit on the id
             */
            $params['autosubmit'] = '#'.$params['id'];
        }

        /*
         * Autosubmit on the specified selector
         */
        return $retval.html_script('$("'.$params['autosubmit'].'").change(function(){ $(this).closest("form").find("input,textarea,select").addClass("ignore"); $(this).closest("form").submit(); });');

    }catch(Exception $e){
        throw new bException('html_select(): Failed', $e);
    }
}



/*
 * Return the body of an HTML <select> list
 */
function html_select_body($params, $selected = null, $none = '', $class = '', $auto_select = true) {
    try{
        array_params ($params, 'resource');
        array_default($params, 'class'      , $class);
        array_default($params, 'none'       , tr('None selected'));
        array_default($params, 'empty'      , tr('None available'));
        array_default($params, 'selected'   , $selected);
        array_default($params, 'auto_select', $auto_select);

        if($params['none']){
            $retval = '<option'.($params['class'] ? ' class="'.$params['class'].'"' : '').''.(($params['selected'] === null) ? ' selected' : '').' value="">'.$params['none'].'</option>';

        }else{
            $retval = '';
        }

        if($params['resource']){
            if(is_array($params['resource'])){
                if($params['auto_select'] and ((count($params['resource']) == 1) and !$params['none'])){
                    /*
                     * Auto select the only available element
                     */
                    $params['selected'] = array_keys($params['resource']);
                    $params['selected'] = array_shift($params['selected']);
                }

                /*
                 * Process array resource
                 */
                foreach($params['resource'] as $key => $value){
                    $notempty = true;
                    $retval  .= '<option'.($params['class'] ? ' class="'.$params['class'].'"' : '').''.((($params['selected'] !== null) and ($key === $params['selected'])) ? ' selected' : '').' value="'.$key.'">'.$value.'</option>';
                }

            }elseif(is_object($params['resource'])){
                if(!($params['resource'] instanceof PDOStatement)){
                    throw new bException(tr('html_select_body(): Specified resource object is not an instance of PDOStatement'), 'invalidresource');
                }

                if($params['auto_select'] and ($params['resource']->rowCount() == 1)){
                    /*
                     * Auto select the only available element
                     */
// :TODO: Implement
                }

                /*
                 * Process SQL resource
                 */
                try{
                    while($row = sql_fetch($params['resource'])){
                        $notempty = true;

                        /*
                         * To avoid select problems with "none" entries, empty id column values are not allowed
                         */
                        if(!$row['id']){
                            $row['id'] = str_random(8);
                        }

                        $retval  .= '<option'.($params['class'] ? ' class="'.$params['class'].'"' : '').''.(($row['id'] === $params['selected']) ? ' selected' : '').' value="'.$row['id'].'">'.$row['name'].'</option>';
                    }

                }catch(Exception $e){

                    throw $e;
                }

            }else{
                throw new bException(tr('html_select_body(): Specified resource "'.str_log($params['resource']).'" is neither an array or resource'), 'invalidresource');
            }
        }


        if(empty($notempty)){
            /*
             * No conent (other than maybe the "none available" entry) was added
             */
            if($params['empty']){
                $retval = '<option'.($params['class'] ? ' class="'.$params['class'].'"' : '').' selected value="">'.$params['empty'].'</option>';
            }

            /*
             * Return empty body (though possibly with "none" element) so that the html_select() function can ensure the select box will be disabled
             */
            return $retval;
        }

        return $retval;

    }catch(Exception $e){
        throw new bException('html_select_body(): Failed', $e);
    }
}



/*
 * Generate and return the HTML footer
 *
 * $option maybe either "async" or "defer", see http://www.w3schools.com/tags/att_script_async.asp
 */
function html_script($script, $jquery_ready = true, $option = null, $type = null, $ie = false){
    global $_CONFIG;

    try{
        if(is_bool($type)){
            $jquery_ready = $type;
            $type         = null;
        }

        if(is_null($type)){
            $type = 'text/javascript';
        }

        /*
         * Event wrapper
         *
         * On what event should this script be executed? Eithere boolean true for standard "document ready" or your own jQuery
         *
         * If false, no event wrapper will be added
         */
        if($jquery_ready){
            if($jquery_ready === true){
                $jquery_ready = '$(document).ready(function(e){ %script% });';
            }

            $script = str_replace('%script%', $script, $jquery_ready);
        }

        if(substr($script, 0, 1) == '>'){
            $retval = '<script type="'.$type.'" src="/pub/js/'.substr($script, 1).'"'.($option ? ' '.$option : '').'></script>';

        }else{
            $retval = '<script type="'.$type.'"'.($option ? ' '.$option : '').">\n".
                            $script.
                      '</script>';
        }

        if($ie){
            $retval = html_iefilter($retval, $ie);
        }

        if(!empty($_CONFIG['cdn']['js']['load_delayed'])){
            /*
             * Add all <script> at the end of the page
             */
            $GLOBALS['footer'] = isset_get($GLOBALS['footer'], '')."\n".$retval;
            $retval = '';
        }

        if(empty($_CONFIG['cdn']['js']['load_delayed'])){
            return $retval;
        }

        /*
         * SCRIPT tags are added all at the end of the page for faster loading
         * (and to avoid problems with jQuery not yet being available)
         */

        $GLOBALS['script_delayed'] = $retval;
        return '';

    }catch(Exception $e){
        throw new bException('html_script(): Failed', $e);
    }
}



/*
 * Return favicon HTML
 */
function html_favicon($icon = null, $mobile_icon = null, $sizes = null, $precomposed = false){
    global $_CONFIG;

    try{
        array_params($params, 'icon');
        array_default($params, 'mobile_icon', $mobile_icon);
        array_default($params, 'sizes'      , $sizes);
        array_default($params, 'precomposed', $precomposed);

        if(!$params['sizes']){
            $params['sizes'] = array('');

        }else{
            $params['sizes'] = array_force($params['sizes']);
        }

        foreach($params['sizes'] as $sizes){
            if($GLOBALS['page_is_mobile']){
                if(!$params['mobile_icon']){
                    $params['mobile_icon'] = $_CONFIG['root'].'/pub/img'.(SUBENVIRONMENTNAME ? '/'.SUBENVIRONMENTNAME : '').'/mobile/favicon.png';
                }

                return '<link rel="apple-touch-icon'.($params['precomposed'] ? '-precompsed' : '').'"'.($sizes ? ' sizes="'.$sizes.'"' : '').' href="'.$params['mobile_icon'].'" />';

            }else{
                if(empty($params['icon'])){
                    $params['icon'] = $_CONFIG['root'].'/pub/img'.(SUBENVIRONMENTNAME ? '/'.SUBENVIRONMENTNAME : '').'/favicon.png';
                }

                return '<link rel="icon" type="image/x-icon"'.($sizes ? ' sizes="'.$sizes.'"' : '').'  href="'.$params['icon'].'" />';
            }
        }

    }catch(Exception $e){
        throw new bException('html_favicon(): Failed', $e);
    }
}



/*
 * Create HTML for an HTML step process bar
 */
function html_list($params, $selected = ''){
    try{
        if(!is_array($params)){
            throw new bException('html_list(): Specified params is not an array', 'invalid');
        }

        if(empty($params['steps']) or !is_array($params['steps'])){
            throw new bException('html_list(): params[steps] is not specified or not an array', 'invalid');
        }

        array_default($params, 'selected'    , $selected);
        array_default($params, 'class'       , '');
        array_default($params, 'disabled'    , false);
        array_default($params, 'show_counter', false);
        array_default($params, 'use_list'    , true);

        if(!$params['disabled']){
            if($params['class']){
                $params['class'] = str_ends($params['class'], ' ');
            }

            $params['class'].'hover';
        }

        if($params['use_list']){
            $retval = '<ul'.($params['class'] ? ' class="'.$params['class'].'"' : '').'>';

        }else{
            $retval = '<div'.($params['class'] ? ' class="'.$params['class'].'"' : '').'>';
        }

        /*
         * Get first and last keys.
         */
        end($params['steps']);
        $last  = key($params['steps']);

        reset($params['steps']);
        $first = key($params['steps']);

        $count = 0;

        foreach($params['steps'] as $name => $data){
            $count++;

            $class = $params['class'].(($params['selected'] == $name) ? ' selected active' : '');

            if($name == $first){
                $class .= ' first';

            }elseif($name == $last){
                $class .= ' last';

            }else{
                $class .= ' middle';
            }

            if($params['show_counter']){
                $counter = '<strong>'.$count.'.</strong> ';

            }else{
                $counter = '';
            }

            if($params['use_list']){
                if($params['disabled']){
                    $retval .= '<li'.($class ? ' class="'.$class.'"' : '').'><a href="" class="nolink">'.$counter.$data['name'].'</a></li>';

                }else{
                    $retval .= '<li'.($class ? ' class="'.$class.'"' : '').'><a href="'.$data['url'].'">'.$counter.$data['name'].'</a></li>';
                }

            }else{
                if($params['disabled']){
                    $retval .= '<a'.($class ? ' class="nolink'.($class ? ' '.$class : '').'"' : '').'>'.$counter.$data['name'].'</a>';

                }else{
                    $retval .= '<a'.($class ? ' class="'.$class.'"' : '').' href="'.$data['url'].'">'.$counter.$data['name'].'</a>';
                }

            }
        }

        if($params['use_list']){
            return $retval.'</ul>';
        }

        return $retval.'</div>';

    }catch(Exception $e){
        throw new bException('html_list(): Failed', $e);
    }
}



/*
 *
 */
function html_status_select($params){
    try{
        array_params ($params, 'name');
        array_default($params, 'name'    , 'status');
        array_default($params, 'none'    , '');
        array_default($params, 'resource', false);
        array_default($params, 'selected', '');

        return html_select($params);

    }catch(Exception $e){
        throw new bException('html_status_select(): Failed', $e);
    }
}



/*
 *
 */
function html_form(){
    return '<input type="hidden" name="dosubmit" value="1">';
}



/*
 *
 */
function html_hidden($source, $key = 'id'){
    try{
        return '<input type="hidden" name="'.$key.'" value="'.isset_get($source[$key]).'">';

    }catch(Exception $e){
        throw new bException('html_hidden(): Failed', $e);
    }
}



// :OBSOLETE: This is now done in http_headers
///*
// * Create the page using the custom library c_page function and add content-length header and send HTML to client
// */
//function html_send($params, $meta, $html){
//    $html = c_page($params, $meta, $html);
//
//    header('Content-Length: '.mb_strlen($html));
//    echo $html;
//    die();
//}



/*
 * Create and return an img tag that contains at the least src, alt, height and width
 */
function html_img($src, $alt, $more = '', $height = 0, $width = 0){
    global $_CONFIG;
    static $images;

    try{
        if(!$alt){
            throw new bException(tr('html_img(): No image alt text specified'), 'notspecified');
        }

        if(!$src){
            throw new bException(tr('html_img(): No image src specified'), 'notspecified');
        }

        /*
         * Images can be either local or remote
         * Local images either have http://thisdomain.com/image, https://thisdomain.com/image, or /image
         * Remote images must have width and height specified
         */
        if(substr($src, 0, 7) == 'http://'){
            $protocol = 'http';

        }elseif($protocol = substr($src, 0, 8) == 'https://'){
            $protocol = 'https';

        }else{
            $protocol = '';
        }

        if(!$protocol){
            /*
             * This is a local image
             */
            $file = ROOT.'www/en'.str_starts($src, '/');

        }else{
            if(preg_match('/^'.$protocol.':\/\/(?:www\.)?'.str_replace('.', '\.', $_CONFIG['domain']).'\/.+$/ius', $src)){
                /*
                 * This is a local image with domain specification
                 */
                $file = ROOT.'www/en'.str_starts(str_from($src, $_CONFIG['domain']), '/');

            }else{
                /*
                 * This is a remote image
                 * Remote images MUST have height and width specified!
                 */
                if(!$height){
                    throw new bException(tr('html_img(): No height specified for remote image'), 'notspecified');
                }

                if(!$width){
                    throw new bException(tr('html_img(): No width specified for remote image'), 'notspecified');
                }
            }
        }

        if(!$height or !$width){
            if(empty($images[$file])){
                if(!file_exists($file)){
                    log_error(tr('html_img(): Specified image src "%src%" does not exist', array('%src%' => $src)), 'notspecified');

                }elseif(!$img_size = getimagesize($file)){
                    log_error('image_is_valid(): File "'.str_log($filename).'" is not an image');
                }

                if(empty($img_size)){
                    $img_size = array(0, 0);
                }

// :DELETE: Its not needed to unset the image data
                //unset($img_size[2]);
                //unset($img_size[3]);
                //unset($img_size['bits']);
                //unset($img_size['channel']);
                //unset($img_size['mime']);

                $images[$file] = $img_size;

            }else{
                /*
                 * Use cached data
                 */
                $img_size = $images[$file];
            }

            if(!$width){
                $width  = $img_size[0];
            }

            if(!$height){
                $height = $img_size[1];
            }
        }

        return '<img src="'.$src.'" alt="'.$alt.'" height="'.$height.'" width="'.$width.'"'.($more ? ' '.$more : '').'>';

    }catch(Exception $e){
        throw new bException('html_img(): Failed', $e);
    }
}



/*
 * Show the specified page
 */
function page_show($pagename, $die = true, $force = false, $data = null) {
    global $_CONFIG;

    try{
        if($GLOBALS['page_is_ajax']){
            // Execute ajax page
            return include(ROOT.'www/'.LANGUAGE.'/ajax/'.$pagename.'.php');

        }elseif($GLOBALS['page_is_ajax']){
                $prefix = 'ajax/';

        }elseif($GLOBALS['page_is_mobile']){
                $prefix = 'mobile/';

        }else{
            $prefix = '';
        }

        return include(ROOT.'www/'.LANGUAGE.'/'.$prefix.$pagename.'.php');

        if($die){
            die();
        }

    }catch(Exception $e){
        throw new bException(tr('page_show(): Failed to show page "%page%"', array('%page%' => str_log($pagename))), $e);
    }
}
?>
