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

class WordCampEntryPass
{

    private $namespace = 'wordcamp-entry-pass/v1';

    public function boot()
    {
        $this->loadDependencies();

        add_action('admin_menu', [$this, 'registerAdminMenu']);
        add_action('rest_api_init', [$this, 'registerRestApi']);

        (new \WordCampEntryPass\Classes\IdPrinter())->register();
        (new \WordCampEntryPass\Classes\Hooks())->register();

        if (defined('WP_SYL_ORGANIZER_PORTAL_SLUG')) {
            // add a custom url endpoint with the WP_SYL_ORGANIZER_PORTAL_SLUG
            add_action('template_redirect', function ($template) {
                if (get_query_var('name') == WP_SYL_ORGANIZER_PORTAL_SLUG) {
                    if(is_user_logged_in()) {
                        header('HTTP/1.1 200 OK');
                        $adminVars = $this->getAppVars();
                        include WP_SYL_ENTRY_PASS_PLUGIN_DIR . 'assets/portal.php';
                        exit();
                    }
                }
            });
        }

        if (defined('WP_CLI') && WP_CLI) {
            require_once WP_SYL_ENTRY_PASS_PLUGIN_DIR . '/classes/CLITool.php';
            \WP_CLI::add_command('syl_event', '\WordCampEntryPass\Classes\CLITool');
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

        $router->get('/events', [$checkInController, 'events'], []);
        $router->post('/events', [$checkInController, 'createEvent'], ['manage_options']);
        $router->post('/attendees/import', [$checkInController, 'importAttendeeCsv'], ['manage_options']);
        $router->get('/events/{id}', [$checkInController, 'getEvent'], []);
        $router->post('/checkin', [$checkInController, 'recordAttendance'], []);
        $router->get('/search-attendee', [$checkInController, 'searchAttendee'], []);
        $router->get('/attendees', [$checkInController, 'getAttendees'], []);
        $router->get('/attendees/card-types', [$checkInController, 'getCardTypes'], []);
        $router->post('/attendees/mark-print-status', [$checkInController, 'updatePrintStatus'], []);
    }

    public function checkPermission()
    {
        return current_user_can('manage_options');
    }

    private function loadDependencies()
    {
        require_once WP_SYL_ENTRY_PASS_PLUGIN_DIR . '/classes/AttendeeModel.php';
        require_once WP_SYL_ENTRY_PASS_PLUGIN_DIR . '/classes/Router.php';
        require_once WP_SYL_ENTRY_PASS_PLUGIN_DIR . '/classes/CheckInController.php';
        require_once WP_SYL_ENTRY_PASS_PLUGIN_DIR . '/classes/IdPrinter.php';
        require_once WP_SYL_ENTRY_PASS_PLUGIN_DIR . '/classes/Hooks.php';
    }

    private function getAppVars()
    {
        $user = get_user_by('ID', get_current_user_id());

        return [
            "name"  => 'WordCamp Entry Pass',
            "slug"  => 'wordcamp-entry-pass',
            "nonce" => wp_create_nonce("wp_rest"),
            'site_url' => site_url('/'),
            'ajax_url' => admin_url('admin-ajax.php'),
            'rest'  => [
                'url'       => rest_url($this->namespace),
                'nonce'     => wp_create_nonce('wp_rest'),
                'namespace' => $this->namespace,
                'version'   => '1'
            ],
            'me'    => [
                'full_name' => $user->display_name
            ],
            'i18n'  => [
                'search_attendee' => __('Search Attendee', 'wordcamp-entry-pass'),
            ]
        ];
    }
}

function wp_entry_pass_activate()
{
    require_once WP_SYL_ENTRY_PASS_PLUGIN_DIR . '/classes/Activate.php';
    (new \WordCampEntryPass\Classes\Activate())->migrateDb();
}

register_activation_hook(__FILE__, 'wp_entry_pass_activate');

add_action('plugins_loaded', function () {
    (new WordCampEntryPass())->boot();
});
