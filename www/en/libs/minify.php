<?php
/*
 * Minify library
 *
 * This library is a front end for the Minify project
 * @see https://github.com/mrclay/minify
 *
 * Since Base does its own HTML minification online (And JS and CSS
 * minification @deploy time), it will only use the minification
 *
 * @license http://opensource.org/licenses/GPL-2.0 GNU Public License, Version 2
 * @copyright Sven Oostenbrink <support@capmega.com>
 */



/*
 * Initialize the library
 * Automatically executed by libs_load()
 */
function minify_library_init(){
    try{
        ensure_installed(array('name'      => 'minify',
                               'project'   => 'minify',
                               'callback'  => 'minify_install',
                               'checks'    => array(ROOT.'libs/external/vendor/mrclay/minify')));

    }catch(Exception $e){
        throw new bException('minify_library_init(): Failed', $e);
    }
}



/*
 * Install the minify library
 */
function minify_install($params){
    try{
        $params['methods'] = array('composer' => array('commands'  => array(ROOT.'libs/external/composer.phar require mrclay/minify',
                                                                            'mv '.TMP.'/minify/vendor/ '.ROOT.'libs/external/',
                                                                            'rm '.TMP.'/minify/ -rf')));
        return install($params);

    }catch(Exception $e){
        throw new bException('minify_install(): Failed', $e);
    }
}



/*
 * Return the specified HTML minified
 */
function minify_html($html){
    try{
        include_once(ROOT.'libs/external/vendor/mrclay/minify/lib/Minify/HTML.php');
        include_once(ROOT.'libs/external/vendor/mrclay/minify/lib/Minify/CSS.php');
        include_once(ROOT.'libs/external/vendor/mrclay/jsmin-php/src/JSMin/JSMin.php');
        include_once(ROOT.'libs/external/vendor/mrclay/minify/lib/Minify/CSS/Compressor.php');
        include_once(ROOT.'libs/external/vendor/mrclay/minify/lib/Minify/CommentPreserver.php');

        $html = Minify_HTML::minify($html, array('cssMinifier'     => array('Minify_CSS'  , 'minify'),
                                                 'jsMinifier'      => array('\JSMin\JSMin', 'minify')));

// :FIX: This is a temp fix because the minifier appears to use \n as a space?
        $html = str_replace("\n", ' ', $html);

        return $html;

    }catch(Exception $e){
        throw new bException('minify_html(): Failed', $e);
    }
}
?>
