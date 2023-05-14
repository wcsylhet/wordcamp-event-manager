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

    public function searchAttendee(\WP_REST_Request $request,)
    {
        global $wpdb;

        $id = sanitize_text_field($request->get_param('search'));

        $tableName = $wpdb->prefix . 'wep_attendees';

        $attendee = null;

        if(is_numeric($id)) {
            $attendee = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM $tableName WHERE attendee_uid = %d",
                    $id
                )
            );
        } else if(is_string($id)) {
            $id = esc_sql($id);
            $attendee = $wpdb->get_row(
                "SELECT * FROM $tableName WHERE `email` LIKE '%$id%'",
            );
        }

        if (!$attendee) {
            return new \WP_Error(423, 'No Attendee Found');
        }

        $attendee->events = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT e.id, e.title, ae.created_at FROM {$wpdb->prefix}wep_attendee_events as ae
                    INNER JOIN {$wpdb->prefix}wep_events as e ON ae.event_id = e.id 
                    WHERE ae.attendee_id = %d",
                $attendee->id
            )
        );

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

        $timezone = new \DateTimeZone('Asia/Dhaka');
        $date = new \DateTime('now', $timezone);
        $current_timestamp = $date->format('Y-m-d H:i:s');

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

        $attendee->events = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT e.id, e.title, ae.created_at FROM {$wpdb->prefix}wep_attendee_events as ae
                    INNER JOIN {$wpdb->prefix}wep_events as e ON ae.event_id = e.id 
                    WHERE ae.attendee_id = %d",
                $attendee->id
            )
        );

        return [
            'attendee' => $attendee
        ];
    }
}
