<?php
/*
 * Add offset location support for user
 * Add city / state / country location support for users
 */
sql_column_exists('users', 'offset_latitude' , '!ALTER TABLE `users` ADD COLUMN `offset_latitude`  DECIMAL(18,15) NULL AFTER `longitude`');
sql_column_exists('users', 'offset_longitude', '!ALTER TABLE `users` ADD COLUMN `offset_longitude` DECIMAL(18,15) NULL AFTER `offset_latitude`');

sql_column_exists('users', 'cities_id',    '!ALTER TABLE `users` ADD COLUMN `cities_id`    INT(11) NULL AFTER `fake`');
sql_column_exists('users', 'states_id',    '!ALTER TABLE `users` ADD COLUMN `states_id`    INT(11) NULL AFTER `cities_id`');
sql_column_exists('users', 'countries_id', '!ALTER TABLE `users` ADD COLUMN `countries_id` INT(11) NULL AFTER `states_id`');

sql_index_exists('users', 'cities_id',    '!ALTER TABLE `users` ADD KEY `cities_id`    (`cities_id`)');
sql_index_exists('users', 'states_id',    '!ALTER TABLE `users` ADD KEY `states_id`    (`states_id`)');
sql_index_exists('users', 'countries_id', '!ALTER TABLE `users` ADD KEY `countries_id` (`countries_id`)');

sql_foreignkey_exists('users', 'fk_users_cities_id',    '!ALTER TABLE `users` ADD CONSTRAINT `fk_users_cities_id`    FOREIGN KEY (`cities_id`)    REFERENCES `geo_cities`    (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT');
sql_foreignkey_exists('users', 'fk_users_states_id',    '!ALTER TABLE `users` ADD CONSTRAINT `fk_users_states_id`    FOREIGN KEY (`states_id`)    REFERENCES `geo_states`    (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT');
sql_foreignkey_exists('users', 'fk_users_countries_id', '!ALTER TABLE `users` ADD CONSTRAINT `fk_users_countries_id` FOREIGN KEY (`countries_id`) REFERENCES `geo_countries` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT');

sql_foreignkey_exists('users', 'fk_users_countries', '!ALTER TABLE `users` DROP CONSTRAINT `fk_users_countries`');

$users = sql_query('SELECT `id`, `latitude`, `longitude` FROM `users` WHERE `latitude` IS NO NULL OR `longitude` IS NOT NULL');

while($user = sql_fetch($users)){
    /*
     * Validate LAT/LONG. If one is invalid, reset both to NULL
     */
    $user = geo_validate_location($user);

    /*
     * Depending on configuration, update offset lat / long
     * If GEO library has been installed, update city, state, and country information
     */
    user_update_location($user);
}

/*
 * Sync users blog location information
 */
blogs_sync_location(array('direction' => 'users',
                          'blogs'     => 'escorts'));
?>