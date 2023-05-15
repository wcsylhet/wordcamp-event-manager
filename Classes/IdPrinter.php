<?php

namespace WordCampEntryPass\Classes;

class IdPrinter
{
    public function register()
    {
        add_action('init', function () {
            if(!isset($_REQUEST['sc_print_id']) || !current_user_can('manage_options')) {
                return;
            }

            $type = sanitize_text_field($_REQUEST['sc_print_id']);
            $printStatus = 'all';
            if(isset($_REQUEST['sc_print_status'])) {
                $printStatus = sanitize_text_field($_REQUEST['sc_print_status']);
            }

            $this->printIds($type, $printStatus);
        });
    }

    public function printIds($type, $printStatus = 'all')
    {
        global $wpdb;

        if($printStatus == 'all') {
            $attendees = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wep_attendees WHERE attendee_type = '{$type}'");
        } else {
            $attendees = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wep_attendees WHERE attendee_type = '{$type}' AND id_printed = '{$printStatus}'");
        }

        foreach ($attendees as $attendee) {
            $attendee->image = $this->getAttendeePhoto($attendee);
            $attendee->full_name = $this->formatName($attendee->first_name.' '.$attendee->last_name);
        }

        // set wp headers
        header('Content-Type: text/html');

        require_once WP_SYL_ENTRY_PASS_PLUGIN_DIR.'assets/badge_html.php';
        exit();
    }

    private function formatName($name)
    {
        $name = trim($name);
        // remove multiple spaces
        $name = preg_replace('/\s+/', ' ', $name);
        // make the name as Title case
        return ucwords(strtolower($name));
    }

    private function getAttendeePhoto($attendee)
    {
        if($attendee->attendee_type != 'Organizer' ) {
            return "https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl=".$attendee->attendee_uid."&chld=L|1&choe=UTF-8";
        }

        $photos = [
            2370 => 'https://sylhet.wordcamp.org/2023/files/2023/05/Reza-Bg-white-Syed-Rezwanul-Haque-300x300.jpeg',
            2371 => 'https://sylhet.wordcamp.org/2023/files/2023/05/arif-Mahmudul-Hasan-300x300.jpeg',
            2372 => 'https://sylhet.wordcamp.org/2023/files/2023/05/IMG_20230422_151203_479-min-Ahsan-Chowdhury-300x300.jpg',
            2373 => 'https://sylhet.wordcamp.org/2023/files/2023/05/Piya-300x300.png',
            2374 => 'https://sylhet.wordcamp.org/2023/files/2023/05/pic-sheuly-shila-150x150.jpg',
            2375 => 'https://sylhet.wordcamp.org/2023/files/2023/05/Adnan-Haque-300x300.jpeg',
            2376 => 'https://sylhet.wordcamp.org/2023/files/2023/05/devutpol-Utpol-Deb-Nath-150x150.jpeg',
            2380 => 'https://sylhet.wordcamp.org/2023/files/2023/05/Take-a-look-at-my-Canva-design-Md.-Habibur-Rahaman-300x300.png',
            2382 => 'https://sylhet.wordcamp.org/2023/files/2023/05/FB_IMG_1682935564010-Md.-Mehdi-Hasan-300x300.jpg',
            2383 => 'https://sylhet.wordcamp.org/2023/files/2023/05/omarsohrab-Omar-Sohrab-300x300.jpg',
            2384 => 'https://sylhet.wordcamp.org/2023/files/2023/05/sumon-FullStack-Web-300x300.jpg',
            2385 => 'https://sylhet.wordcamp.org/2023/files/2023/05/rsz_tasnova-Tasnova-Chowdhury-300x300.jpeg',
            2386 => 'https://sylhet.wordcamp.org/2023/files/2023/05/F91E926A-2D30-4F53-BA70-C5E1DAD7214E-Shovonix-300x300.jpeg',
            2387 => 'https://sylhet.wordcamp.org/2023/files/2023/05/WP-Md.-Shamsul-Islam-300x300.jpg',
            2388 => 'https://sylhet.wordcamp.org/2023/files/2023/05/faizus-Faizus-Saleheen-300x300.png',
            2389 => 'https://sylhet.wordcamp.org/2023/files/2023/05/India-Visa-Photo-Shekh-Al-Raihan-300x300.jpg',
            2394 => 'https://sylhet.wordcamp.org/2023/files/2023/05/asadojjaman-500X500px-Md-Asadojjaman-300x300.jpg',
            2395 => 'https://sylhet.wordcamp.org/2023/files/2023/05/hasanuzzaman-300x300.jpg',
            2398 => 'https://sylhet.wordcamp.org/2023/files/2023/05/ishtiaq-Ishtiaq-Khan-Parag-300x300.jpeg',
            2399 => 'https://sylhet.wordcamp.org/2023/files/2023/05/raju-Ishtiaq-Khan-Parag-300x300.jpeg',
            2402 => 'https://sylhet.wordcamp.org/2023/files/2023/05/rsz_3screenshot_20230405-1107302-Kawsar-Chowdhury-300x300.jpeg',
            2403 => 'https://sylhet.wordcamp.org/2023/files/2023/05/2023-04-29-14.56.27-Md.-Kamrul-Islam-300x300.jpg',
            3715 => 'https://sylhet.wordcamp.org/2023/files/2023/05/ershad_photo_2_2-3-ershadur-rahman-300x300.jpeg',
            3880 => 'https://sylhet.wordcamp.org/2023/files/2023/05/abu-shariar-300x300.png',
            4046 => 'https://sylhet.wordcamp.org/2023/files/2023/05/2023-05-03-09.52.51-150x150.jpg',
            4048 => 'https://sylhet.wordcamp.org/2023/files/2023/05/me-small-AL-EMRAN-150x150.png',
            4049 => 'https://sylhet.wordcamp.org/2023/files/2023/05/image-1-Mansurul-Haque-300x300.jpeg',
            4052 => 'https://sylhet.wordcamp.org/2023/files/2023/05/nakib-Lukman-Nakib-300x300.jpg',
            4053 => 'https://sylhet.wordcamp.org/2023/files/2023/05/M0n3CyF6_400x400-300x300.jpeg',
            2572 => 'https://sylhet.wordcamp.org/2023/files/2023/05/Rahabi-Khan-Rahabi-Khan-300x300.png',
        ];

        if(isset($photos[$attendee->attendee_uid])) {
            return $photos[$attendee->attendee_uid];
        }

        return "https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl=".$attendee->attendee_uid."&chld=L|1&choe=UTF-8";
    }
}
