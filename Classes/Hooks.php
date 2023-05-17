<?php

namespace WordCampEntryPass\Classes;

class Hooks
{
    public function register()
    {
        add_action('wp_ajax_syl_event_attendee_export', [$this, 'exportAttendees']);
        add_action('wp_ajax_syl_attendee_counter', [$this, 'getAttendeeCounter']);
        add_action('wp_ajax_nopriv_syl_attendee_counter', [$this, 'getAttendeeCounter']);
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

    public function getAttendeeCounter()
    {
        $attendeeId = isset($_REQUEST['attendee_uid']) ? (int) $_REQUEST['attendee_uid'] : 0;

        if(!$attendeeId) {
            wp_send_json_error([
                'message' => '<p class="error">A Valid 4 digit attendee ID is required</p>'
            ], 423);
        }

        $attendee = AttendeeModel::getAttendee($attendeeId, 'attendee_uid');

        if(!$attendee) {
            wp_send_json_error([
                'message' => '<p class="error">We could not find your registration counter. Please contact with a volunteer</p>'
            ], 423);
        }

        ob_start();
        ?>

        <div class="result_item">
            <div class="form_field">
                <label for="attendee_id">Your Registration Booth</label>
            </div>
            <div class="form_field">
                <div class="booth_card">
                    <span><?php echo $attendee->counter; ?></span>
                </div>
                <div class="qr_code">
                    <img src="https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl=<?php echo $attendee->attendee_uid; ?>&&chld=L|1&choe=UTF-8" />
                </div>
            </div>
        </div>
        <?php

        $html = ob_get_clean();

        wp_send_json([
            'message' => $html
        ], 200);

    }

}
