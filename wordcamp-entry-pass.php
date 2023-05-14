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


class WordCampEntryPass
{

    private $namespace = 'wordcamp-entry-pass/v1';

    public function boot()
    {
        $this->loadDependencies();
        add_action('admin_menu', [$this, 'registerAdminMenu']);
        add_action('rest_api_init', [$this, 'registerRestApi']);
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

    public function renderAdminPage() {
        wp_enqueue_script('wc_syl_entry_pass', WP_SYL_ENTRY_PASS_PLUGIN_URL.'dist/app.js', [], WP_SYL_ENTRY_PASS_PLUGIN_VERSION, true);

        $user = get_user_by('ID', get_current_user_id());

        wp_localize_script('wc_syl_entry_pass', 'WordCampEntryAdmin', [
            "name" => 'WordCamp Entry Pass',
            "slug" => 'wordcamp-entry-pass',
            "nonce" => wp_create_nonce("wp_rest"),
            'rest'            => [
                'url'       => rest_url($this->namespace),
                'nonce'     => wp_create_nonce('wp_rest'),
                'namespace' => $this->namespace,
                'version'   => '1'
            ],
            'me' => [
                'full_name' => $user->display_name
            ],
            'i18n' => [
                'search_attendee' => __('Search Attendee', 'wordcamp-entry-pass'),
            ]
        ]);

        echo '<div id="wordcamp_entry_pass_app"></div>';
    }

    public function registerRestApi()
    {
        $checkInController = new \WordCampEntryPass\Classes\CheckInController();

        $router = new \WordCampEntryPass\Classes\Router($this->namespace);

        $router->get('/events', [$checkInController, 'events'], []);
        $router->post('/attendee-event', [$checkInController, 'recordAttendance'], []);
        $router->get('/search-attendee', [$checkInController, 'searchAttendee'], []);
    }

    public function checkPermission()
    {
        return true; //current_user_can('manage_options');
    }

    private function loadDependencies()
    {
        require_once WP_SYL_ENTRY_PASS_PLUGIN_DIR.'/classes/Router.php';
        require_once WP_SYL_ENTRY_PASS_PLUGIN_DIR.'/classes/CheckInController.php';
    }
}

add_action('plugins_loaded', function () {
    (new WordCampEntryPass())->boot();
});
