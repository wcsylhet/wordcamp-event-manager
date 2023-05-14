<?php

namespace WordCampEntryPass\Classes;

class CheckinController
{
    public function events(\WP_REST_Request $request)
    {
        global $wpdb;

        $tableName = $wpdb->prefix . 'wep_events';

        $events = $wpdb->get_results("SELECT * FROM $tableName");

        return [
            'events' => $events
        ];
    }

    public function createEvent(\WP_REST_Request $request)
    {
        $title = sanitize_text_field($request->get_param('title'));
        $description = sanitize_textarea_field($request->get_param('description'));

        if (!$title) {
            return new \WP_Error(423, 'Title is required');
        }

        global $wpdb;

        // check if event exists
        $tableName = $wpdb->prefix . 'wep_events';
        $event = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $tableName WHERE title = %s",
                $title
            )
        );

        if ($event) {
            return new \WP_Error(423, 'Event already exists with the same name');
        }

        $wpdb->insert(
            $tableName,
            [
                'title'       => $title,
                'description' => $description,
                'created_by'  => get_current_user_id()
            ]
        );

        return [
            'message' => 'Event created successfully'
        ];
    }

    public function getEvent(\WP_REST_Request $request)
    {
        global $wpdb;

        $id = (int)$request->get_param('id');

        $tableName = $wpdb->prefix . 'wep_events';

        $event = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $tableName WHERE id = %d",
                $id
            )
        );

        if (!$event) {
            return new \WP_Error(423, 'Event not found');
        }

        return [
            'event' => $event
        ];
    }

    public function importAttendeeCsv(\WP_REST_Request $request)
    {
        $files = $request->get_file_params();

        if (empty($files['attendee_csv'])) {
            return new \WP_Error(423, 'Attendee CSV is required');
        }

        $csv = $files['attendee_csv'];

        if ($csv['type'] !== 'text/csv') {
            return new \WP_Error(423, 'Invalid file type');
        }

        $csv = array_map('str_getcsv', file($csv['tmp_name']));

        // get the first row
        $header = array_shift($csv);

        // make the header lowercase
        $header = array_map('strtolower', $header);

        $validPrimaryColumns = [
            'attendee_uid',
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
            'other_details'
        ];

        $requiredColumns = [
            'attendee_uid',
            'first_name',
            'email'
        ];

        // check header has $requiredColumns
        $missingColumns = array_diff($requiredColumns, $header);
        if ($missingColumns) {
            return new \WP_Error(423, 'Missing required columns ' . implode(', ', $missingColumns));
        }

        $validItems = [];
        // now make all the entries as key pair
        foreach ($csv as $i => $row) {
            $data = array_combine($header, $row);
            // get only the valid columns
            $validData = array_intersect_key($data, array_flip($validPrimaryColumns));
            $otherData = array_diff_key($data, array_flip($validPrimaryColumns));
            $validData = array_filter($validData);
            // check if all the required columns are present
            $missingColumns = array_diff_key(array_flip($requiredColumns), $validData);
            if (!empty($missingColumns) || !is_email($validData['email'])) {
                return new \WP_Error(423, 'Missing required columns for attendee ' . $data['attendee_id']);
            }

            // now fill $validPrimaryColumns with $validData if any key is missing
            foreach ($validPrimaryColumns as $key) {
                if (!isset($validData[$key])) {
                    $validData[$key] = '';
                }
            }
            $validData['other_details'] = maybe_serialize($otherData);

            $validItems[] = $validData;
        }

        $willUpdate = $request->get_param('update_if_exist') === 'on';

        foreach ($validItems as $validItem) {
            $this->maybeInsertAttendee($validItem, $willUpdate);
        }

        return [
            'message' => sprintf('%d attendees has been imported', count($validItems))
        ];
    }

    public function getAttendees(\WP_REST_Request $request)
    {
        global $wpdb;

        $tableName = $wpdb->prefix . 'wep_attendees';

        $perPage = (int) $request->get_param('per_page') ?: 10;
        $page = (int) $request->get_param('page') ?: 1;
        $offset = ($page - 1) * $perPage;

        $sqlWhere = '';
        $search = sanitize_text_field($request->get_param('search'));

        $placeholders = [];

        if($search) {
            $wherePlaceholders = [];
            $sqlWhere = "WHERE (attendee.attendee_uid LIKE '%s' OR attendee.first_name LIKE '%s' OR attendee.last_name LIKE '%s' OR attendee.email LIKE '%s')";
            $wherePlaceholders[] = '%'.$search.'%';
            $wherePlaceholders[] = '%'.$search.'%';
            $wherePlaceholders[] = '%'.$search.'%';
            $wherePlaceholders[] = '%'.$search.'%';
            $sqlWhere = $wpdb->prepare($sqlWhere, $wherePlaceholders);
        }

        $sql = "SELECT * FROM $tableName as attendee";

        $eventId = $request->get_param('event_id');

        if($eventId) {
            $sql .= " ".$wpdb->prepare("INNER JOIN {$wpdb->prefix}wep_attendee_events as event ON event.attendee_id = attendee.id", );
            $sqlWhere .= " AND event.event_id = $eventId";
        }

        if($sqlWhere) {
            $sql .= ' '.$sqlWhere;
        }

        $placeholders[] = $perPage;
        $placeholders[] = $offset;

        $attendees = $wpdb->get_results($wpdb->prepare($sql." LIMIT %d OFFSET %d", $placeholders));

        foreach ($attendees as $attendee) {
            $attendee->other_details = maybe_unserialize($attendee->other_details);
        }

        return [
            'attendees' => $attendees,
            'total' => $wpdb->get_var(str_replace('SELECT *', 'SELECT COUNT(*)', $sql.' LIMIT 1'))
        ];
    }

    public function searchAttendee(\WP_REST_Request $request,)
    {
        global $wpdb;

        $id = sanitize_text_field($request->get_param('search'));

        $tableName = $wpdb->prefix . 'wep_attendees';

        $attendee = null;

        if (is_numeric($id)) {
            $attendee = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM $tableName WHERE attendee_uid = %d",
                    $id
                )
            );
        } else if (is_string($id)) {
            $id = esc_sql($id);
            $attendee = $wpdb->get_row(
                "SELECT * FROM $tableName WHERE `email` LIKE ' % $id % '",
            );
        }

        if (!$attendee) {
            return new \WP_Error(423, 'No Attendee Found');
        }

        $attendee->events = $this->getEventsByAttendedId($attendee->id);

        $attendee->avatar = get_avatar_url($attendee->email);

        return [
            'attendee' => $attendee
        ];
    }

    public function recordAttendance(\WP_REST_Request $request)
    {
        $attendee_id = (int)$request->get_param('attendee_id');
        $event_id = (int)$request->get_param('event_id');

        global $wpdb;

        $attendee = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}wep_attendees WHERE id = %d",
                $attendee_id
            )
        );

        if (!$attendee) {
            return new \WP_Error(423, 'No Attendee Found');
        }

        $event = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}wep_events WHERE id = %d",
                $event_id
            )
        );

        if (!$event) {
            return new \WP_Error(423, 'Event not found');
        }

        $current_timestamp = current_time('mysql');

        $wpdb->get_results(
            $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}wep_attendee_events
                    (attendee_id, event_id, created_at)
                    VALUES (%s, %d, %s)",
                $attendee_id,
                $event_id,
                $current_timestamp
            )
        );

        $attendee->events = $this->getEventsByAttendedId($attendee->id);

        return [
            'attendee' => $attendee,
            'message'  => 'Checkin Successfully'
        ];
    }

    private function getEventsByAttendedId($id)
    {
        global $wpdb;
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT e.id, e.title, ae.created_at FROM {$wpdb->prefix}wep_attendee_events as ae
                    INNER JOIN {$wpdb->prefix}wep_events as e ON ae.event_id = e.id 
                    WHERE ae.attendee_id = %d",
                $id
            )
        );
    }

    private function maybeInsertAttendee($data, $willUpdate = false)
    {
        global $wpdb;
        // check if attendee_uid is present
        if (isset($data['attendee_uid'])) {
            $attendee = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}wep_attendees WHERE attendee_uid = %d",
                    $data['attendee_uid']
                )
            );

            if ($attendee) {
                if (!$willUpdate) {
                    return $attendee->id;
                }
                $wpdb->update(
                    $wpdb->prefix . 'wep_attendees',
                    $data,
                    ['attendee_uid' => $attendee->attendee_uid]
                );
                return $attendee->id;
            }

        }

        $wpdb->insert($wpdb->prefix . 'wep_attendees', $data);
        return $wpdb->insert_id;
    }
}
