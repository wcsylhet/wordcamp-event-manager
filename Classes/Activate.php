<?php

namespace WordCampEntryPass\Classes;

class Activate
{
    public function migrateDb()
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $this->attendeesTable();
        $this->eventsTable();
        $this->migrateAttendeeEventsTable();
    }

    private function attendeesTable()
    {
        global $wpdb;

        $tableName = $wpdb->prefix . 'wep_attendees';
        $charsetCollate = $wpdb->get_charset_collate();

        if ($wpdb->get_var("SHOW TABLES LIKE '$tableName'") != $tableName) {
            $sql = "CREATE TABLE $tableName (
                id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
                attendee_uid varchar(30) NOT NULL,
                ticket_type varchar(194) NULL,
                attendee_type varchar(50) NULL,
                counter varchar(192) NULL,
                tshirt_size varchar(100) NULL,
                first_name varchar(192) NOT NULL,
                last_name varchar(192) NULL,
                email varchar(194) NOT NULL,
                twiter_username varchar(194) NOT NULL,
                phone_number varchar(50) NULL,
                country varchar(50) NULL, 
                purchase_at timestamp  NULL DEFAULT NULL,
                last_modified_at timestamp NULL DEFAULT NULL,
                other_detals LONGTEXT NULL DEFAULT NULL,
                PRIMARY KEY  (id),
                KEY attendee_uid (attendee_uid),
                KEY email (email)
            ) $charsetCollate;";
            dbDelta($sql);
        }

    }

    private function eventsTable()
    {
        global $wpdb;

        $tableName = $wpdb->prefix . 'wep_attendee_events';
        $charsetCollate = $wpdb->get_charset_collate();

        if ($wpdb->get_var("SHOW TABLES LIKE '$tableName'") != $tableName) {
            $sql = "CREATE TABLE $tableName (
                id bigint UNSIGNED NOT NULL AUTO_INCREMENT, 
                attendee_id BIGINT NOT NULL,
                event_id BIGINT NOT NULL,
                remarks TEXT NULL DEFAULT NULL,
                created_by BIGINT NULL,
                created_at timestamp NOT NULL,
                PRIMARY KEY  (id),
                KEY attendee_id (attendee_id),
                KEY event_id (event_id)
            ) $charsetCollate;";
            dbDelta($sql);
        }

    }

    private function migrateAttendeeEventsTable()
    {
        global $wpdb;

        $tableName = $wpdb->prefix . 'wep_attendee_events';
        $charsetCollate = $wpdb->get_charset_collate();

        if ($wpdb->get_var("SHOW TABLES LIKE '$tableName'") != $tableName) {
            $sql = "CREATE TABLE $tableName (
                id bigint UNSIGNED NOT NULL AUTO_INCREMENT, 
                attendee_id bigint NOT NULL,
                event_id bigint NOT NULL,
                remarks text NULL DEFAULT NULL,
                created_at timestamp NOT NULL,
                PRIMARY KEY  (id)
            ) $charsetCollate;";
            dbDelta($sql);
        }

    }
}
