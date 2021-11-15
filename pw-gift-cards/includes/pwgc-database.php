<?php

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'PWGC_Database' ) ) :

class PWGC_Database {

    function __construct() {
        add_action( 'plugins_loaded', array( $this, 'database_version_check' ) );
    }

    function database_version_check() {
        global $wpdb;

        if ( get_option( 'pwgc_database_version' ) != PWGC_VERSION ) {

            // Switch to the local database in case we're multisite and have switched sites.
            $wpdb->pimwick_gift_card = $wpdb->prefix . 'pimwick_gift_card';
            $wpdb->pimwick_gift_card_activity = $wpdb->prefix . 'pimwick_gift_card_activity';

            $wpdb->query( "
                CREATE TABLE IF NOT EXISTS `{$wpdb->pimwick_gift_card}` (
                    `pimwick_gift_card_id` INT NOT NULL AUTO_INCREMENT,
                    `number` TEXT NOT NULL,
                    `active` TINYINT(1) NOT NULL DEFAULT 1,
                    `create_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `expiration_date` DATE NULL,
                    `pimwick_gift_card_parent` INT NULL,
                    `recipient_email` VARCHAR(255) NULL,
                    PRIMARY KEY (`pimwick_gift_card_id`),
                    UNIQUE `{$wpdb->prefix}ix_pimwick_gift_card_number` ( `number` (128) )
                );
            " );

            if ( $wpdb->last_error != '' ) {
                wp_die( $wpdb->last_error );
            }

            $wpdb->query( "
                CREATE TABLE IF NOT EXISTS `{$wpdb->pimwick_gift_card_activity}` (
                    `pimwick_gift_card_activity_id` INT NOT NULL AUTO_INCREMENT,
                    `pimwick_gift_card_id` INT NOT NULL,
                    `user_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                    `activity_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `action` VARCHAR(60) NOT NULL,
                    `amount` DECIMAL(15,6) NULL DEFAULT NULL,
                    `note` TEXT NULL DEFAULT NULL,
                    `reference_activity_id` INT NULL DEFAULT NULL,
                    PRIMARY KEY (`pimwick_gift_card_activity_id`),
                    INDEX `{$wpdb->prefix}ix_pimwick_gift_card_id` (`pimwick_gift_card_id`)
                );
            " );
            if ( $wpdb->last_error != '' ) {
                wp_die( $wpdb->last_error );
            }

            // Column added v1.94
            $column = $wpdb->get_results( $wpdb->prepare(
                "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ",
                DB_NAME, $wpdb->pimwick_gift_card, 'pimwick_gift_card_parent'
            ) );

            if ( empty( $column ) ) {
                $wpdb->query( "
                    ALTER TABLE `{$wpdb->pimwick_gift_card}` ADD `pimwick_gift_card_parent` INT NULL;
                " );

                if ( $wpdb->last_error != '' ) {
                    wp_die( $wpdb->last_error );
                }
            }

            // Column added v1.141
            $column = $wpdb->get_results( $wpdb->prepare(
                "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ",
                DB_NAME, $wpdb->pimwick_gift_card, 'recipient_email'
            ) );

            if ( empty( $column ) ) {
                $wpdb->query( "
                    ALTER TABLE `{$wpdb->pimwick_gift_card}` ADD `recipient_email` VARCHAR(255) NULL;
                " );

                if ( $wpdb->last_error != '' ) {
                    wp_die( $wpdb->last_error );
                }

                $wpdb->query( "
                    UPDATE
                        `{$wpdb->pimwick_gift_card}` AS gift_card
                    JOIN
                        `{$wpdb->prefix}woocommerce_order_itemmeta` AS oim ON (oim.meta_key = 'pw_gift_card_number' AND COALESCE(oim.meta_value, '') IS NOT NULL AND oim.meta_value = gift_card.number)
                    JOIN
                        `{$wpdb->prefix}woocommerce_order_items` AS oi ON (oi.order_item_id = oim.order_item_id)
                    JOIN
                        `{$wpdb->postmeta}` AS billing_email ON (billing_email.post_id = oi.order_id AND billing_email.meta_key = '_billing_email')
                    SET
                        gift_card.recipient_email = billing_email.meta_value
                    ;
                " );

                if ( $wpdb->last_error != '' ) {
                    wp_die( $wpdb->last_error );
                }
            }

            // Drop the foreign key constraint if it exists.
            $foreign_keys = $wpdb->get_results( "
                SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = '{$wpdb->pimwick_gift_card_activity}' AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            " );

            foreach ( $foreign_keys as $row ) {
                $wpdb->query( "
                    ALTER TABLE `{$wpdb->pimwick_gift_card_activity}` DROP FOREIGN KEY `{$row->CONSTRAINT_NAME}`;
                " );
            }

            update_option( 'pwgc_database_version', PWGC_VERSION );

            pwgc_set_table_names();
        }
    }
}

new PWGC_Database();

endif;

?>