<?php
/*
 * PDO library
 *
 * This file contains various functions to access databases over PDO
 *
 * @license http://opensource.org/licenses/GPL-2.0 GNU Public License, Version 2
 * @copyright Sven Oostenbrink <support@ingiga.com>
 */


/*
 * Helper for building sql_in key value pairs
 */
function sql_in_columns($in){
    try{
        return implode(',', array_keys($in));

    }catch(Exception $e){
        throw new bException('sql_in_columns(): Failed', $e);
    }
}


/*
 * Execute specified query
 */
function sql_query($query, $execute = false, $handle_exceptions = true, $connector = 'core'){
    global $core;

    try{
        $connector   = sql_init($connector);
        $query_start = microtime(true);

        if(!is_string($query)){
            throw new bException(tr('sql_query(): Specified query ":query" is not a string', array(':query' => $query)), 'invalid');
        }

        if(!empty($core->register['sql_debug_queries'])){
            $core->register['sql_debug_queries']--;
            $query = ' '.$query;
        }

        if(substr($query, 0, 1) == ' '){
            debug_sql($query, $execute);
        }

        if(!$execute){
            /*
             * Just execute plain SQL query string.
             */
            $pdo_statement = $core->sql[$connector]->query($query);

        }else{
            /*
             * Execute the query with the specified $execute variables
             */
            $pdo_statement = $core->sql[$connector]->prepare($query);

            try{
                $pdo_statement->execute($execute);

            }catch(Exception $e){
                /*
                 * Failure is probably that one of the the $execute array values is not scalar
                 */
// :TODO: Move all of this to sql_error()
                if(!is_array($execute)){
                    throw new bException('sql_query(): Specified $execute is not an array!', 'invalid');
                }

                /*
                 * Check execute array for possible problems
                 */
                foreach($execute as $key => &$value){
                    if(!is_scalar($value) and !is_null($value)){
                        throw new bException(tr('sql_query(): Specified key ":value" in the execute array for query ":query" is NOT scalar! Value is ":value"', array(':key' => str_replace(':', '.', $key), ':query' => str_replace(':', '.', $query), ':value' => str_replace(':', '.', $value))), 'invalid');
                    }
                }

                throw $e;
            }
        }

        if(debug()){
            $current = 1;

            if(substr(current_function($current), 0, 4) == 'sql_'){
                $current = 2;
            }

            $function = current_function($current);
            $file     = current_function($current);
            $line     = current_function($current);

            $core->executedQuery(array('time'     => microtime(true) - $query_start,
                                       'query'    => debug_sql($query, $execute, true),
                                       'function' => current_function($current),
                                       'file'     => current_file($current),
                                       'line'     => current_line($current)));
        }

        return $pdo_statement;


    }catch(Exception $e){
        if(!$handle_exceptions){
            throw new bException(tr('sql_query(:connector): Query ":query" failed', array(':connector' => $connector, ':query' => debug_sql($query, $execute, true))), $e);
        }

        try{
            /*
             * Let sql_error() try and generate more understandable errors
             */
            load_libs('sql_error');
            sql_error($e, $query, $execute, isset_get($core->sql[$connector]));

        }catch(Exception $e){
            throw new bException(tr('sql_query(:connector): Query ":query" failed', array(':connector' => $connector, ':query' => $query)), $e);
        }
    }
}



/*
 * Prepare specified query
 */
function sql_prepare($query, $connector = 'core'){
    global $core;

    try{
        $connector = sql_init($connector);
        return $core->sql[$connector]->prepare($query);

    }catch(Exception $e){
        throw new bException('sql_prepare(): Failed', $e);
    }
}



/*
 * Fetch and return data from specified resource
 */
function sql_fetch($r, $single_column = false, $fetch_style = PDO::FETCH_ASSOC){
    try{
        if(!is_object($r)){
            throw new bException('sql_fetch(): Specified resource is not a PDO object', 'invalid');
        }

        $result = $r->fetch($fetch_style);

        if($result === false){
            /*
             * There are no entries
             */
            return null;
        }

        if($single_column === true){
            /*
             * Return only the first column
             */
            if(count($result) !== 1){
                throw new bException(tr('sql_fetch(): Failed for query ":query" to fetch single column, specified query result contains not 1 but ":count" columns', array(':count' => count($result), ':query' => $r->queryString)), 'multiple');
            }

            return array_shift($result);
        }

        if($single_column){
            if(!array_key_exists($single_column, $result)){
                throw new bException(tr('sql_fetch(): Failed for query ":query" to fetch single column ":column", specified query result does not contain the requested column', array(':column' => $single_column, ':query' => $r->queryString)), 'multiple');
            }

            return $result[$single_column];
        }

        /*
         * Return everything
         */
        return $result;

    }catch(Exception $e){
        throw new bException('sql_fetch(): Failed', $e);
    }
}



/*
 * Execute query and return only the first row
 */
function sql_get($query, $single_column = null, $execute = null, $connector = 'core'){
    try{
        if(is_object($query)){
            return sql_fetch($query, $single_column);

        }else{
            if(is_array($single_column)){
                /*
                 * Argument shift, no columns were specified.
                 */
                $tmp            = $execute;
                $execute        = $single_column;
                $single_column  = $tmp;
                unset($tmp);
            }

            $result = sql_query($query, $execute, true, $connector);

            if($result->rowCount() > 1){
                throw new bException(tr('sql_get(): Failed for query ":query" to fetch single row, specified query result contains not 1 but ":count" results', array(':count' => $result->rowCount(), ':query' => debug_sql($result->queryString, $execute, true))), 'multiple');
            }

            return sql_fetch($result, $single_column);
        }

    }catch(Exception $e){
        if(is_object($query)){
            $query = $query->queryString;
        }

        if(strtolower(substr(trim($query), 0, 6)) != 'select'){
            throw new bException('sql_get(): Query "'.str_log(debug_sql($query, $execute, true), 4096).'" is not a select query and as such cannot return results', $e);
        }

        throw new bException('sql_get(): Failed', $e);
    }
}



/*
 * Execute query and return only the first row
 */
function sql_list($query, $execute = null, $numerical_array = false, $connector = 'core'){
    try{
        if(is_object($query)){
            $r     = $query;
            $query = $r->queryString;

        }else{
            $r = sql_query($query, $execute, true, $connector);
        }

        $retval = array();

        while($row = sql_fetch($r)){
            if(is_scalar($row)){
                $retval[] = $row;

            }else{
                switch($numerical_array ? 0 : count($row)){
                    case 0:
                        /*
                         * Force numerical array
                         */
                        $retval[] = $row;
                        break;

                    case 1:
                        $retval[] = array_shift($row);
                        break;

                    case 2:
                        $retval[array_shift($row)] = array_shift($row);
                        break;

                    default:
                        $retval[array_shift($row)] = $row;
                }
            }
        }

        return $retval;

    }catch(Exception $e){
        throw new bException('sql_list(): Failed', $e);
    }
}



/*
 * Connect with the main database
 */
function sql_init($connector = 'core'){
    global $_CONFIG, $core;

    try{
        $connector = sql_connector_name($connector);

        if(!empty($core->sql[$connector])){
            /*
             * Already connected to requested DB
             */
            return $connector;
        }

        if(empty($_CONFIG['db'][$connector])){
            throw new bException(tr('sql_init(): Specified database connector ":connector" has not been configured', array(':connector' => $connector)), 'not-exist');
        }

        $db = $_CONFIG['db'][$connector];

        /*
         * Set the MySQL rand() seed for this session
         */
// :TODO: On PHP7, update to random_int() for better cryptographic numbers
        $_SESSION['sql_random_seed'] = mt_rand();

        /*
         * Connect to database
         */
        $core->sql[$connector] = sql_connect($db);

        /*
         * This is only required for the system connection
         */
        if((PLATFORM_CLI) and (SCRIPT == 'init') and FORCE and !empty($_CONFIG['db'][$connector]['init'])){
            include(__DIR__.'/handlers/sql_init_force.php');
        }

        if(!defined('FRAMEWORKDBVERSION')){
            /*
             * Get database version
             *
             * This can be disabled by setting $_CONFIG[db][CONNECTORNAME][init] to false
             */
            if(!empty($_CONFIG['db'][$connector]['init'])){
                try{
                    $r = $core->sql[$connector]->query('SELECT `project`, `framework`, `offline_until` FROM `versions` ORDER BY `id` DESC LIMIT 1;');

                }catch(Exception $e){
                    if($e->getCode() !== '42S02'){
                        if($e->getMessage() === 'SQLSTATE[42S22]: Column not found: 1054 Unknown column \'offline_until\' in \'field list\''){
                            $r = $core->sql[$connector]->query('SELECT `project`, `framework` FROM `versions` ORDER BY `id` DESC LIMIT 1;');

                        }else{
                            /*
                             * Compatibility issue, this happens when older DB is running init.
                             * Just ignore it, since in these older DB's the functionality
                             * wasn't even there
                             */
                            throw $e;
                        }
                    }
                }

                try{
                    if(empty($r) or !$r->rowCount()){
                        log_console(tr('sql_init(): No versions table found or no versions in versions table found, assumed empty database ":db"', array(':db' => $_CONFIG['db'][$connector]['db'])), 'yellow');

                        define('FRAMEWORKDBVERSION', 0);
                        define('PROJECTDBVERSION'  , 0);

                        $core->register['no-db'] = true;

                    }else{
                        $versions = $r->fetch(PDO::FETCH_ASSOC);

                        if(!empty($versions['offline_until'])){
                            if(PLATFORM_HTTP){
                                page_show(503, array('offline_until' => $versions['offline_until']));
                            }
                        }

                        define('FRAMEWORKDBVERSION', $versions['framework']);
                        define('PROJECTDBVERSION'  , $versions['project']);

                        if(version_compare(FRAMEWORKDBVERSION, '0.1.0') === -1){
                            $core->register['no-db'] = true;
                        }
                    }

                }catch(Exception $e){
                    /*
                     * Database version lookup failed. Usually, this would be due to the database being empty,
                     * and versions table does not exist (yes, that makes a query fail). Just to be sure that
                     * it did not fail due to other reasons, check why the lookup failed.
                     */
                    load_libs('init');
                    init_process_version_fail($e);
                }

                /*
                 * On console, show current versions
                 */
                if((PLATFORM_CLI) and VERBOSE){
                    log_console(tr('sql_init(): Found framework code version ":frameworkcodeversion" and framework database version ":frameworkdbversion"', array(':frameworkcodeversion' => FRAMEWORKCODEVERSION, ':frameworkdbversion' => FRAMEWORKDBVERSION)));
                    log_console(tr('sql_init(): Found project code version ":projectcodeversion" and project database version ":projectdbversion"'        , array(':projectcodeversion'   => PROJECTCODEVERSION  , ':projectdbversion'   => PROJECTDBVERSION)));
                }


                /*
                 * Validate code and database version. If both FRAMEWORK and PROJECT versions of the CODE and DATABASE do not match,
                 * then check exactly what is the version difference
                 */
                if((FRAMEWORKCODEVERSION != FRAMEWORKDBVERSION) or (PROJECTCODEVERSION != PROJECTDBVERSION)){
                    load_libs('init');
                    init_process_version_diff();
                }
            }
        }

        return $connector;

    }catch(Exception $e){
        include(__DIR__.'/handlers/sql_init_fail.php');
    }
}



/*
 * Close the connection for the specified connector
 */
function sql_close($connector = 'core'){
    global $_CONFIG, $core;

    try{
        $connector = sql_connector_name($connector);
        unset($core->sql[$connector]);

    }catch(Exception $e){
        throw new bException(tr('sql_close(): Failed for connector ":connector"', array(':connector' => $connector)), $e);
    }
}



/*
 * Connect to database and do a DB version check.
 * If the database was already connected, then just ignore and continue.
 * If the database version check fails, then exception
 */
function sql_connect($connector, $use_database = true){
    global $_CONFIG;

    try{
        array_params($connector);
        array_default($connector, 'driver' , null);
        array_default($connector, 'host'   , null);
        array_default($connector, 'user'   , null);
        array_default($connector, 'pass'   , null);
        array_default($connector, 'charset', null);

        if(!class_exists('PDO')){
            /*
             * Wulp, PDO class not available, PDO driver is not loaded somehow
             */
            throw new bException('sql_connect(): Could not find the "PDO" class, does this PHP have PDO available?', 'dbdriver');
        }

        /*
         * Connect!
         */
        try{
            $connector['pdo_attributes'][PDO::ATTR_ERRMODE]                  = PDO::ERRMODE_EXCEPTION;
            $connector['pdo_attributes'][PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = !(boolean) $connector['buffered'];
            $connector['pdo_attributes'][PDO::MYSQL_ATTR_INIT_COMMAND]       = 'SET NAMES '.strtoupper($connector['charset']);
            $retries = 3;

            while(--$retries >= 0){
                try{
                    $pdo = new PDO($connector['driver'].':host='.$connector['host'].(empty($connector['port']) ? '' : ';port='.$connector['port']).((empty($connector['db']) or !$use_database) ? '' : ';dbname='.$connector['db']), $connector['user'], $connector['pass'], $connector['pdo_attributes']);

                }catch(Exception $e){
// :TODO: This is a workaround and should be fixed properly!!!
                    /*
                     * This is a work around for the weird PHP MySQL error
                     * PDO::__construct(): send of 5 bytes failed with errno=32 Broken pipe
                     * So far we have not been able to find a fix for this but
                     * we have noted that you always have to connect 3 times,
                     * and the 3rd time the bug magically disappars. The work
                     * around will detect the error and retry up to 3 times to
                     * work around this issue for now
                     */
                    $message = $e->getMessage();
                    if(strstr($message, 'errno=32') === false){
                        /*
                         * This is a different error. Continue throwing the
                         * exception as normal
                         */
                        throw $e;
                    }
                }
            }

// :DELETE: The characterset is now set in the mysql init command
//            /*
//             * Ensure correct character set and timezone usage
//             */
//            $pdo->query('SET NAMES '.$connector['charset']);
//            $pdo->query('SET CHARACTER SET '.$connector['charset']);

            try{
                $pdo->query('SET time_zone = "'.$connector['timezone'].'";');

            }catch(Exception $e){
                include(__DIR__.'/handlers/sql_timezone_fail.php');
            }

            if(!empty($connector['mode'])){
                $pdo->query('SET sql_mode="'.$connector['mode'].'";');
            }

        }catch(Exception $e){
            include(__DIR__.'/handlers/sql_connect_exception.php');
        }

        return $pdo;

    }catch(Exception $e){
        throw new bException('sql_connect(): Failed', $e);
    }
}



/*
 * Import data from specified file
 */
function sql_import($file, $connector = 'core'){
    global $core;

    try {
        $connector = sql_connector_name($connector);

        if(!file_exists($file)){
            throw new bException(tr('sql_import(): Specified file ":file" does not exist', array(':file' =>$file)), 'not-exist');
        }

        $tel    = 0;
        $handle = @fopen($file, 'r');

        if(!$handle){
            throw new isException('sql_import(): Could not open file', 'notopen');
        }

        while (($buffer = fgets($handle)) !== false){
            $buffer = trim($buffer);

            if(!empty($buffer)){
                $core->sql[$connector]->query(trim($buffer));

                $tel++;
// :TODO:SVEN:20130717: Right now it updates the display for each record. This may actually slow down import. Make display update only every 10 records or so
                echo 'Importing SQL data ('.$file.') : '.number_format($tel)."\n";
                //one line up!
                echo "\033[1A";
            }
        }

        echo "\nDone\n";

        if(!feof($handle)){
            throw new isException(tr('sql_import(): Unexpected EOF'), 'invalid');
        }

        fclose($handle);

    }catch(Exception $e){
        throw new bException(tr('sql_import(): Failed to import file ":file"', array(':file' => $file)), $e);
    }
}



/*
 *
 */
function sql_columns($source, $columns){
    try{
        if(!is_array($source)){
            throw new bException('sql_columns(): Specified source is not an array');
        }

        $columns = array_force($columns);
        $retval  = array();

        foreach($source as $key => $value){
            if(in_array($key, $columns)){
                $retval[] = '`'.$key.'`';
            }
        }

        if(!count($retval)){
            throw new bException('sql_columns(): Specified source contains non of the specified columns "'.str_log(implode(',', $columns)).'"');
        }

        return implode(', ', $retval);

    }catch(Exception $e){
        throw new bException('sql_columns(): Failed', $e);
    }
}



// :OBSOLETE: Remove this function soon
///*
// *
// */
//function sql_set($source, $columns, $filter = 'id'){
//    try{
//        if(!is_array($source)){
//            throw new bException('sql_set(): Specified source is not an array', 'invalid');
//        }
//
//        $columns = array_force($columns);
//        $filter  = array_force($filter);
//        $retval  = array();
//
//        foreach($source as $key => $value){
//            /*
//             * Add all in columns, but not in filter (usually to skip the id column)
//             */
//            if(in_array($key, $columns) and !in_array($key, $filter)){
//                $retval[] = '`'.$key.'` = :'.$key;
//            }
//        }
//
//        foreach($filter as $item){
//            if(!isset($source[$item])){
//                throw new bException('sql_set(): Specified filter item "'.str_log($item).'" was not found in source', 'notfound');
//            }
//        }
//
//        if(!count($retval)){
//            throw new bException('sql_set(): Specified source contains non of the specified columns "'.str_log(implode(',', $columns)).'"', 'empty');
//        }
//
//        return implode(', ', $retval);
//
//    }catch(Exception $e){
//        throw new bException('sql_set(): Failed', $e);
//    }
//}



/*
 *
 */
function sql_values($source, $columns, $prefix = ':'){
    try{
        if(!is_array($source)){
            throw new bException('sql_values(): Specified source is not an array');
        }

        $columns = array_force($columns);
        $retval  = array();

        foreach($source as $key => $value){
            if(in_array($key, $columns) or ($key == 'id')){
                $retval[$prefix.$key] = $value;
            }
        }

        return $retval;

    }catch(Exception $e){
        throw new bException('sql_values(): Failed', $e);
    }
}



/*
 *
 */
function sql_insert_id($connector = 'core'){
    global $core;

    try{
        return $core->sql[sql_connector_name($connector)]->lastInsertId();

    }catch(Exception $e){
        throw new bException(tr('sql_insert_id(): Failed for connector ":connector"', array(':connector' => $connector)), $e);
    }
}



/*
 *
 */
function sql_get_id_or_name($entry, $seo = true, $code = false){
    try{
        if(is_array($entry)){
            if(!empty($entry['id'])){
                $entry = $entry['id'];

            }elseif(!empty($entry['name'])){
                $entry = $entry['name'];

            }elseif(!empty($entry['seoname'])){
                $entry = $entry['seoname'];

            }elseif(!empty($entry['code'])){
                $entry = $entry['code'];

            }else{
                throw new bException('sql_get_id_or_name(): Invalid entry array specified', 'invalid');
            }
        }

        if(is_numeric($entry)){
            $retval['where']   = '`id` = :id';
            $retval['execute'] = array(':id'   => $entry);

        }elseif(is_string($entry)){
            if($seo){
                if($code){
                    $retval['where']   = '`name` = :name OR `seoname` = :seoname OR `code` = :code';
                    $retval['execute'] = array(':code'    => $entry,
                                               ':name'    => $entry,
                                               ':seoname' => $entry);

                }else{
                    $retval['where']   = '`name` = :name OR `seoname` = :seoname';
                    $retval['execute'] = array(':name'    => $entry,
                                               ':seoname' => $entry);
                }

            }else{
                if($code){
                    $retval['where']   = '`name` = :name OR `code` = :code';
                    $retval['execute'] = array(':code' => $entry,
                                               ':name' => $entry);

                }else{
                    $retval['where']   = '`name` = :name';
                    $retval['execute'] = array(':name' => $entry);
                }
            }

        }else{
            throw new bException('sql_get_id_or_name(): Invalid entry with type "'.gettype($entry).'" specified', 'invalid');
        }

        return $retval;

    }catch(bException $e){
        throw new bException('sql_get_id_or_name(): Failed (use either numeric id, name sting, or entry array with id or name)', $e);
    }
}



/*
 * Return a unique, non existing ID for the specified table.column
 */
function sql_unique_id($table, $column = 'id', $max = 10000000, $connector = 'core'){
    try{
        $retries    =  0;
        $maxretries = 50;

        while(++$retries < $maxretries){
            $id = mt_rand(1, $max);

            if(!sql_get('SELECT `'.$column.'` FROM `'.$table.'` WHERE `'.$column.'` = :id', array(':id' => $id), null, $connector)){
                return $id;
            }
        }

        throw new bException('sql_unique_id(): Could not find a unique id in "'.$maxretries.'" retries', 'notfound');

    }catch(bException $e){
        throw new bException('sql_unique_id(): Failed', $e);
    }
}



/*
 *
 */
function sql_filters($params, $columns, $table = ''){
    try{
        $retval  = array('filters' => array(),
                         'execute' => array());

        $filters = array_keep($params, $columns);

        foreach($filters as $key => $value){
            $safe_key = str_replace('`.`', '_', $key);

            if($value === null){
                $retval['filters'][] = ($table ? '`'.$table.'`.' : '').'`'.$key.'` IS NULL';

            }else{
                $retval['filters'][]              = ($table ? '`'.$table.'`.' : '').'`'.$key.'` = :'.$safe_key;
                $retval['execute'][':'.$safe_key] = $value;
            }
        }

        return $retval;

    }catch(bException $e){
        throw new bException('sql_filters(): Failed', $e);
    }
}



/*
 * Return a sequential array that can be used in sql_in
 */
function sql_in($source, $column = ':value'){
    try{
        if(empty($source)){
            throw new bException('sql_in(): Specified source is empty', 'empty');
        }

        load_libs('array');
        return array_sequential_keys(array_force($source), $column);

    }catch(bException $e){
        throw new bException('sql_in(): Failed', $e);
    }
}



/*
 *
 */
function sql_where($query, $required = true){
    try{
        if(!$query){
            if($required){
                throw new bException('sql_where(): No filter query specified, but it is required', 'required');
            }

            return ' ';
        }

        return ' WHERE '.$query;

    }catch(bException $e){
        throw new bException('sql_filters(): Failed', $e);
    }
}



/*
 * Try to get single data entry from memcached. If not available, get it from
 * MySQL and store results in memcached for future use
 */
function sql_get_cached($key, $query, $column = false, $execute = false, $expiration_time = 86400, $connector = 'core'){
    try{
        if(($value = mc_get($key, 'sql_')) === false){
            /*
             * Keyword data not found in cache, get it from MySQL with
             * specified query and store it in cache for next read
             */
            if(is_array($column)){
                /*
                 * Argument shift, no columns were specified.
                 */
                $tmp     = $execute;
                $execute = $column;
                $column  = $tmp;
                unset($tmp);
            }

            if(is_numeric($column)){
                /*
                 * Argument shift, no columns were specified.
                 */
                $tmp             = $expiration_time;
                $expiration_time = $execute;
                $execute         = $tmp;
                unset($tmp);
            }

            $value = sql_get($query, $column, $execute, $connector);

            mc_put($value, $key, 'sql_', $expiration_time);
        }

        return $value;

    }catch(bException $e){
        throw new bException('sql_get_cached(): Failed', $e);
    }
}



/*
 * Try to get data list from memcached. If not available, get it from
 * MySQL and store results in memcached for future use
 */
function sql_list_cached($key, $query, $execute = false, $numerical_array = false, $connector = 'core', $expiration_time = 86400){
    try{
        if(($list = mc_get($key, 'sql_')) === false){
            /*
             * Keyword data not found in cache, get it from MySQL with
             * specified query and store it in cache for next read
             */
            $list = sql_list($query, $execute, $numerical_array, $connector);

            mc_put($list, $key, 'sql_', $expiration_time);
        }

        return $list;

    }catch(bException $e){
        throw new bException('sql_list_cached(): Failed', $e);
    }
}



/*
 *
 */
function sql_valid_limit($limit, $connector = null){
    global $_CONFIG;

    try{
        $limit     = force_natural($limit);
        $connector = sql_connector_name($connector);

        if($limit > $_CONFIG['db'][$connector]['limit_max']){
            return $_CONFIG['db'][$connector]['limit_max'];
        }

        return $limit;

    }catch(Exception $e){
        throw new bException('sql_valid_limit(): Failed', $e);
    }
}



/*
 * Fetch and return data from specified resource
 */
function sql_fetch_column($r, $column){
    try{
        $row = sql_fetch($r);

        if(!isset($row[$column])){
            throw new bException('sql_fetch_column(): Specified column "'.str_log($column).'" does not exist', $e);
        }

        return $row[$column];

    }catch(Exception $e){
        throw new bException('sql_fetch_column(): Failed', $e);
    }
}



/*
 * Merge database entry with new posted entry, overwriting the old DB values,
 * while skipping the values specified in $filter
 */
function sql_merge($db, $post, $skip = 'id,status'){
    try{
        if(!is_array($db)){
            if($db !== null){
                throw new bException(tr('sql_merge(): Specified database source data type should be an array but is a ":type"', array(':type' => gettype($db))), 'invalid');
            }

            /*
             * Nothing to merge
             */
            $db = array();
        }

        if(!is_array($post)){
            if($post !== null){
                throw new bException(tr('sql_merge(): Specified post source data type should be an array but is a ":type"', array(':type' => gettype($post))), 'invalid');
            }

            /*
             * Nothing to merge
             */
            $post = array();
        }

        $post = array_remove($post, $skip);

        /*
         * Copy all POST variables over DB
         * Skip POST variables that have NULL value
         */
        foreach($post as $key => $value){
            if($value === null) continue;
            $db[$key] = $value;
        }

        return $db;

    }catch(Exception $e){
        throw new bException('sql_merge(): Failed', $e);
    }
}



/*
 * Ensure that $connector is default in case its not specified
 */
function sql_connector_name($connector){
    global $_CONFIG;

    try{
        if($connector === null){
            return $_CONFIG['db']['default'];
        }

        if(!is_string($connector)){
            throw new bException(tr('sql_connector_name(): Invalid connector ":connector" specified', array(':connector' => $connector)), 'invalid');
        }

        return $connector;

    }catch(Exception $e){
        throw new bException('sql_connector_name(): Failed', $e);
    }
}



/*
 * Use correct SQL in case NULL is used in queries
 */
function sql_is($value, $not = false){
    try{
        if($not){
            if($value === null){
                return ' IS NOT ';
            }

            return ' != ';
        }

        if($value === null){
            return ' IS ';
        }

        return ' = ';

    }catch(Exception $e){
        throw new bException('sql_is(): Failed', $e);
    }
}



/*
 * Enable / Disable all query logging on mysql server
 */
function sql_log($enable){
    try{
        if($enable){
            sql_query('SET global log_output = "FILE";');
            sql_query('SET global general_log_file="/var/log/mysql/queries.log";');
            sql_query('SET global general_log = 1;');

        }else{
            sql_query('SET global log_output = "OFF";');
        }

    }catch(Exception $e){
        throw new bException('sql_log(): Failed', $e);
    }
}



/*
 *
 */
function sql_exists($table, $column, $value, $id = null){
    try{
        if($id){
            return sql_get('SELECT `id` FROM `'.$table.'` WHERE `'.$column.'` = :'.$column.' AND `id` != :id', true, array($column => $value, ':id' => $id));
        }

        return sql_get('SELECT `id` FROM `'.$table.'` WHERE `'.$column.'` = :'.$column.'', true, array($column => $value));

    }catch(Exception $e){
        throw new bException(tr('sql_exists(): Failed'), $e);
    }
}



/*
 * NOTE: Use only on huge tables (> 1M rows)
 *
 * Return table row count by returning results count for SELECT `id`
 * Results will be cached in a counts table
 */
function sql_count($table, $where = '', $execute = null, $column = '`id`'){
    global $_CONFIG;

    try{
        load_config('sql_large');

        $expires = $_CONFIG['sql_large']['cache']['expires'];
        $hash    = hash('sha1', $table.$where.$column.json_encode($execute));
        $count   = sql_get('SELECT `count` FROM `counts` WHERE `hash` = :hash AND `until` > NOW()', 'count', array(':hash' => $hash));

        if($count){
            return $count;
        }

        /*
         * Count value was not found cached, count it directly
         */
        $count = sql_get('SELECT COUNT('.$column.') AS `count` FROM `'.$table.'` '.$where, 'count', $execute);

        sql_query('INSERT INTO `counts` (`createdby`, `count`, `hash`, `until`)
                   VALUES               (:createdby , :count , :hash , NOW() + INTERVAL :expires SECOND)

                   ON DUPLICATE KEY UPDATE `count`      = :update_count,
                                           `modifiedon` = NOW(),
                                           `modifiedby` = :update_modifiedby,
                                           `until`      = NOW() + INTERVAL :update_expires SECOND',

                   array(':createdby'         => isset_get($_SESSION['user']['id']),
                         ':hash'              => $hash,
                         ':count'             => $count,
                         ':expires'           => $expires,
                         ':update_expires'    => $expires,
                         ':update_modifiedby' => isset_get($_SESSION['user']['id']),
                         ':update_count'      => $count));

        return $count;

    }catch(Exception $e){
        throw new bException('sql_count(): Failed', $e);
    }
}



/*
 * Returns what database currently is selected
 */
function sql_current_database(){
    try{
        return sql_get('SELECT DATABASE() AS `database` FROM DUAL;');

    }catch(Exception $e){
        throw new bException('sql_current_database(): Failed', $e);
    }
}



/*
 * OBSOLETE / COMPATIBILITY FUNCTIONS
 *
 * These functions below exist only for compatibility between pdo.php and mysqli.php
 *
 * Return affected rows
 */
function sql_affected_rows($r){
    return $r->rowCount();
}

/*
 * Return number of rows in the specified resource
 */
function sql_num_rows(&$r){
    return $r->rowCount();
}

function sql_merge_entry($db, $post, $skip = null){
    return sql_merge($db, $post, $skip);
}
?>
