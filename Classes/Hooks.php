<?php

namespace WordCampEntryPass\Classes;

class Hooks
{
    public function register()
    {
        add_action('wp_ajax_syl_event_attendee_export', [$this, 'exportAttendees']);
    }

    public function exportAttendees()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        $args = [
            'search' => isset($_REQUEST['search']) ? sanitize_text_field($_REQUEST['search']) : '',
            'event_id' => isset($_REQUEST['event_id']) ? (int) $_REQUEST['event_id'] : 0
        ];

        $attendees = AttendeeModel::getAttendees($args, false);

        // Generate and download the csv file from attendees data
        $filename = 'attendees-'.date('Y-m-d_H:i').'.csv';
        $delimiter = ',';

        // Create a file pointer
        $f = fopen('php://memory', 'w');

        // Set column headers
        $fields = AttendeeModel::getPrimaryColumns();

        // Output each row of the data, format line as csv and write to file pointer
        fputcsv($f, $fields, $delimiter);

        foreach ($attendees as $attendee) {
            $lineData = [];
            foreach ($fields as $field) {
                $lineData[] = $attendee->{$field};
            }

            fputcsv($f, $lineData, $delimiter);
        }

        // Move back to beginning of file
        fseek($f, 0);

        // Set headers to download file rather than displayed
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');

        // Output all remaining data on a file pointer
        fpassthru($f);

        // Exit from file
        exit();

    }

}
