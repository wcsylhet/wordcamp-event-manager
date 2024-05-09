<?php

/**
 * Plugin Name: WordCamp Entry Pass
 * Plugin URI: https://sylhet.wordcamp.org
 * Description: WordCamp Entry pass for WordCamp Envets
 * Version: 1.0.0
 * Author: WordCamp
 * Author URI: https://sylhet.wordcamp.org/2023/
 * License:  GPL-3.0
 * License URI:  http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: wordcamp-entry-pass
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

define('WP_SYL_ENTRY_PASS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_SYL_ENTRY_PASS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_SYL_ENTRY_PASS_PLUGIN_VERSION', time());
if (!defined('WP_SYL_ORGANIZER_PORTAL_SLUG')) {
    define('WP_SYL_ORGANIZER_PORTAL_SLUG', 'portal');
}

if (!defined('WP_SYL_COUNTER_FINDER')) {
    define('WP_SYL_COUNTER_FINDER', 'registration');
}

class WordCampEntryPass
{

    private $namespace = 'wordcamp-entry-pass/v1';

    public function boot()
    {

        add_action('init', function () {
            if (!isset($_REQUEST['attendee_mail'])) {
                return;
            }

            $email = $_REQUEST['attendee_mail'];
            $uid = (int)$_REQUEST['attendee_uid'];

            $attendee = \WordCampEntryPass\Classes\AttendeeModel::getAttendee($uid, 'attendee_uid');

            if (!$attendee || $attendee->email != $email) {
                header('Content-Type: image/png');
                readfile(WP_SYL_ENTRY_PASS_PLUGIN_DIR . 'dist/img/counters/pending.png');
                die();
            }

            $generator = new \WordCampEntryPass\Classes\QRCode($attendee->secret_key, [
                'w' => 300,
                'h' => 300
            ]);

            header('Content-Type: image/png');
            $generator->output_image();
            die();

        }, 1);

        $this->loadDependencies();

        add_action('admin_menu', [$this, 'registerAdminMenu']);
        add_action('rest_api_init', [$this, 'registerRestApi']);

        (new \WordCampEntryPass\Classes\IdPrinter())->register();
        (new \WordCampEntryPass\Classes\Hooks())->register();

        if (defined('WP_SYL_ORGANIZER_PORTAL_SLUG')) {
            // add a custom url endpoint with the WP_SYL_ORGANIZER_PORTAL_SLUG
            add_action('template_redirect', function ($template) {
                if (get_query_var('name') == WP_SYL_ORGANIZER_PORTAL_SLUG) {
                    if (is_user_logged_in()) {
                        header('HTTP/1.1 200 OK');
                        $adminVars = $this->getAppVars();
                        include WP_SYL_ENTRY_PASS_PLUGIN_DIR . 'assets/portal.php';
                        exit();
                    }
                }
            });
        }

        if (defined('WP_SYL_COUNTER_FINDER')) {
            // add a custom url endpoint with the WP_SYL_ORGANIZER_PORTAL_SLUG
            add_action('template_redirect', function ($template) {
                if (get_query_var('name') == WP_SYL_COUNTER_FINDER) {

                    $email = $this->get($_REQUEST, 'attendee');
                    $uid = (int) $this->get($_REQUEST, 'uid');

                    $attendee = \WordCampEntryPass\Classes\AttendeeModel::getAttendee($uid, 'attendee_uid');

                    if(!$attendee || $attendee->email != $email) {
                        $attendee = null;
                    }

                    header('HTTP/1.1 200 OK');

                    include WP_SYL_ENTRY_PASS_PLUGIN_DIR . 'assets/counter.php';
                    exit();
                }

            });
        }

        if (defined('WP_CLI') && WP_CLI) {
            require_once WP_SYL_ENTRY_PASS_PLUGIN_DIR . 'Classes/CLITool.php';
            \WP_CLI::add_command('syl_event', '\WordCampEntryPass\Classes\CLITool');
        }

        if (isset($_REQUEST['find-counter'])) {
            $attendeeId = (int)$_REQUEST['find-counter'];
            $attendee = null;
            if ($attendeeId) {
                $attendee = \WordCampEntryPass\Classes\AttendeeModel::getAttendee($attendeeId, 'attendee_uid');
            }

            $file = WP_SYL_ENTRY_PASS_PLUGIN_DIR . 'dist/img/counters/pending.png';

            if ($attendee && $attendee->counter) {
                $file = WP_SYL_ENTRY_PASS_PLUGIN_DIR . 'dist/img/counters/counter_' . strtolower($attendee->counter) . '.png';
            }

            header('Content-Encoding: none');
            header('Content-Type: image/png');
            header('Content-Length: ' . filesize($file));
            echo readfile($file);
            die();
        }
    }

    public function registerAdminMenu()
    {
        add_menu_page(
            'Entry Pass',
            'Entry Pass',
            'manage_options',
            'wordcamp-entry-pass',
            [$this, 'renderAdminPage'],
            'dashicons-tickets',
            6
        );
    }

    public function renderAdminPage()
    {
        wp_enqueue_script('wc_syl_entry_pass', WP_SYL_ENTRY_PASS_PLUGIN_URL . 'dist/app.js', ['jquery'], WP_SYL_ENTRY_PASS_PLUGIN_VERSION, true);
        wp_localize_script('wc_syl_entry_pass', 'WordCampEntryAdmin', $this->getAppVars());

        echo '<div id="wordcamp_entry_pass_app"></div>';
    }

    public function registerRestApi()
    {
        $checkInController = new \WordCampEntryPass\Classes\CheckInController();

        $router = new \WordCampEntryPass\Classes\Router($this->namespace);

        $router->get('/events', [$checkInController, 'events'], ['edit_posts']);
        $router->post('/events', [$checkInController, 'createEvent'], ['edit_posts']);
        $router->post('/attendees/import', [$checkInController, 'importAttendeeCsv'], ['manage_options']);
        $router->get('/events/{id}', [$checkInController, 'getEvent'], ['edit_posts']);
        $router->post('/checkin', [$checkInController, 'recordAttendance'], ['edit_posts']);
        $router->get('/search-attendee', [$checkInController, 'searchAttendee'], ['edit_posts']);
        $router->get('/attendees', [$checkInController, 'getAttendees'], ['manage_options']);
        $router->get('/attendees/card-types', [$checkInController, 'getCardTypes'], ['edit_posts']);
        $router->post('/attendees/mark-print-status', [$checkInController, 'updatePrintStatus'], ['manage_options']);

    }

    public function checkPermission()
    {
        return current_user_can('manage_options');
    }

    private function loadDependencies()
    {
        require_once WP_SYL_ENTRY_PASS_PLUGIN_DIR . 'Classes/AttendeeModel.php';
        require_once WP_SYL_ENTRY_PASS_PLUGIN_DIR . 'Classes/Router.php';
        require_once WP_SYL_ENTRY_PASS_PLUGIN_DIR . 'Classes/CheckInController.php';
        require_once WP_SYL_ENTRY_PASS_PLUGIN_DIR . 'Classes/IdPrinter.php';
        require_once WP_SYL_ENTRY_PASS_PLUGIN_DIR . 'Classes/Hooks.php';
        require_once WP_SYL_ENTRY_PASS_PLUGIN_DIR . 'Classes/QrCode.php';
    }

    private function getAppVars()
    {
        $user = get_user_by('ID', get_current_user_id());

        return [
            "name"     => 'WordCamp Entry Pass',
            "slug"     => 'wordcamp-entry-pass',
            "nonce"    => wp_create_nonce("wp_rest"),
            'site_url' => site_url('/'),
            'ajax_url' => admin_url('admin-ajax.php'),
            'rest'     => [
                'url'       => rest_url($this->namespace),
                'nonce'     => wp_create_nonce('wp_rest'),
                'namespace' => $this->namespace,
                'version'   => '1'
            ],
            'me'       => [
                'full_name' => $user->display_name
            ],
            'i18n'     => [
                'search_attendee' => __('Search Attendee', 'wordcamp-entry-pass'),
            ],
            'is_admin' => current_user_can('manage_options') ? 'yes' : 'no'
        ];
    }

    public function get($arr, $key)
    {
        return $arr[$key] ?? null;
    }
}

function wp_entry_pass_activate()
{
    require_once WP_SYL_ENTRY_PASS_PLUGIN_DIR . 'Classes/Activate.php';
    (new \WordCampEntryPass\Classes\Activate())->migrateDb();
}

register_activation_hook(__FILE__, 'wp_entry_pass_activate');

add_action('plugins_loaded', function () {
    (new WordCampEntryPass())->boot();
});
