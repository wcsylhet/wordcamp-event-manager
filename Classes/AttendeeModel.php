<?php

namespace WordCampEntryPass\Classes;

class AttendeeModel
{

    private static $table = 'wep_attendees';

    public static function getAttendees($args = [], $paginate = false)
    {
        global $wpdb;

        $tableName = $wpdb->prefix . self::$table;

        $sqlWhere = '';
        $placeholders = [];

        if (!empty($args['search'])) {
            $search = sanitize_text_field($args['search']);

            $wherePlaceholders = [];
            // check if the search is for column with column:value format
            if (strpos($search, ':') !== false) {
                $searchParts = explode(':', $search);
                $searchColumn = $searchParts[0];
                $searchValue = $searchParts[1];
                $sqlWhere = "WHERE attendee.$searchColumn LIKE '%s'";
                $wherePlaceholders[] = '%' . $searchValue . '%';
            } else {
                $sqlWhere = "WHERE (attendee.attendee_uid LIKE '%s' OR attendee.first_name LIKE '%s' OR attendee.last_name LIKE '%s' OR attendee.email LIKE '%s')";
                $wherePlaceholders[] = '%' . $search . '%';
                $wherePlaceholders[] = '%' . $search . '%';
                $wherePlaceholders[] = '%' . $search . '%';
                $wherePlaceholders[] = '%' . $search . '%';
            }

            $sqlWhere = $wpdb->prepare($sqlWhere, $wherePlaceholders);
        }

        $sql = "SELECT * FROM $tableName as attendee";

        if (!empty($args['event_id'])) {
            $eventId = (int)$args['event_id'];
            $sql .= " " . $wpdb->prepare("INNER JOIN {$wpdb->prefix}wep_attendee_events as event ON event.attendee_id = attendee.id",);
            $sqlWhere .= " AND event.event_id = $eventId";
        }

        if ($sqlWhere) {
            $sql .= ' ' . $sqlWhere;
        }

        $mainSql = $sql;

        if ($paginate) {
            $perPage = !empty($args['per_page']) ? (int)$args['per_page'] : 10;
            $page = !empty($args['page']) ? (int)$args['page'] : 1;
            $offset = ($page - 1) * $perPage;
            $placeholders[] = $perPage;
            $placeholders[] = $offset;
            $sql = $wpdb->prepare($sql . " LIMIT %d OFFSET %d", $placeholders);
        }

        $attendees = $wpdb->get_results($sql);

        foreach ($attendees as $attendee) {
            $attendee->other_details = maybe_unserialize($attendee->other_details);
        }

        if ($paginate) {
            return [
                'data'  => $attendees,
                'total' => $wpdb->get_var(str_replace('SELECT *', 'SELECT COUNT(*)', $mainSql . ' LIMIT 1'))
            ];
        }

        return $attendees;
    }

    public static function getPrimaryColumns()
    {
        return [
            'attendee_uid',
            'card_id',
            'ticket_type',
            'attendee_type',
            'counter',
            'first_name',
            'last_name',
            'email',
            'purchase_at',
            'last_modified_at',
            'twitter_username',
            'tshirt_size',
            'phone_number',
            'id_printed',
            'buyer_name',
            'buyer_email',
            'country',
            'purchase_at',
            'last_modified_at',
            'secret_key'
        ];
    }

    public static function getAttendee($value, $column = 'id')
    {
        global $wpdb;

        $tableName = $wpdb->prefix . self::$table;

        $sql = "SELECT * FROM $tableName WHERE $column = %s";

        $attendee = $wpdb->get_row($wpdb->prepare($sql, $value));

        if ($attendee) {
            $attendee->other_details = maybe_unserialize($attendee->other_details);
        }

        return $attendee;
    }

    public static function getAttendeesBy($value, $column = 'id')
    {
        global $wpdb;

        $tableName = $wpdb->prefix . self::$table;

        $sql = "SELECT * FROM $tableName WHERE $column = %s";

        $attendees = $wpdb->get_results($wpdb->prepare($sql, $value));

        foreach ($attendees as $attendee) {
            $attendee->other_details = maybe_unserialize($attendee->other_details);
        }

        return $attendees;
    }

    public static function update($id, $data)
    {
        global $wpdb;

        $tableName = $wpdb->prefix . self::$table;

        return $wpdb->update($tableName, $data, ['id' => $id]);
    }

    public static function filterBy($column, $value)
    {
        global $wpdb;

        $tableName = $wpdb->prefix . self::$table;

        $sql = "SELECT * FROM $tableName WHERE $column = %s";

        $attendees = $wpdb->get_results($wpdb->prepare($sql, $value));

        foreach ($attendees as $attendee) {
            $attendee->other_details = maybe_unserialize($attendee->other_details);
        }

        return $attendees;
    }

    public static function getAll()
    {
        global $wpdb;

        $tableName = $wpdb->prefix . self::$table;

        $sql = "SELECT * FROM $tableName";

        return $wpdb->get_results($sql);
    }

}
