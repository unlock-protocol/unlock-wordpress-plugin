<?php
/**
 * Edit Full Post Page class.
 *
 * @since 3.0.0
 *
 * @package Full_Post_Page
 */

namespace Unlock_Protocol\Inc\Fullpostpage;

use Unlock_Protocol\Inc\Traits\Singleton;

/**
 * Class Editfullpp
 *
 * @since 3.0.0
 */
class Editfullpp {

    use Singleton;

    /**
     * Construct method.
     *
     * @since 3.0.0
     */
    protected function __construct() {

        $this->setup_hooks();

    }

    /**
     * Setup hooks.
     *
     * @since 3.0.0
     */
    protected function setup_hooks() {
        /**
         * Actions.
         */
        add_action('add_meta_boxes', [$this, 'unlock_protocol_add_meta_box']);
        add_action('wp_ajax_save_lock_attributes', [$this, 'save_lock_attributes_ajax']);
        add_action('admin_enqueue_scripts', [$this, 'unlock_protocol_enqueue_scripts']);
    }

    // Register and create the Unlock Protocol meta box for posts and pages
    public function unlock_protocol_add_meta_box() {
        add_meta_box(
            'unlock_protocol_meta_box',
            'Unlock Protocol',
            [$this, 'unlock_protocol_meta_box_callback'],
            ['post', 'page'],
            'side',
            'default'
        );
    }

    // Output the contents of the Unlock Protocol meta box
    public function unlock_protocol_meta_box_callback($post) 
    {
        wp_nonce_field('unlock_protocol_save_meta', 'unlock_protocol_meta_nonce');

        $attributes = get_post_meta($post->ID, 'unlock_box_attributes', true);
    
        // Get the up-to-date network list from the plugin user's saved settings
        $networkList = get_option('unlock_protocol_settings');
    
        // Pass the attributes and network list to JavaScript
        $formattedNetworks = array_map(function ($net) {
            return array(
                'name' => $net['network_name'],
                'value' => $net['network_id'],
            );
        }, array_values($networkList['networks']));
    
        // Create the meta box HTML structure
        echo '<div id="unlock-protocol-meta-box">';
        echo '<p><button id="add-lock" type="button" class="button" style="background-color: blue; color: white;">Add Lock</button></p>';
        echo '<p><div id="lock-list"></div></p>';
        echo '<p><div id="feedback"></div></p>';
        echo '<p><button id="save-lock" type="button" class="button" style="background-color: green; color: white;">Save Lock</button></p>';
        echo '</div>';
    
        echo '<script>';
        echo 'var unlockAttributes = ' . json_encode($attributes) . ';';
        echo 'var ethereumNetworks = ' . json_encode($formattedNetworks);
        echo '</script>';
    }

    // AJAX action to save lock attributes
    public function save_lock_attributes_ajax() 
    {
        check_ajax_referer('unlock_protocol_save_meta', 'nonce');

        $post_id = intval($_POST['post_id']);
        if (isset($_POST['lock_attributes'])) {
            $attributes = json_decode(stripslashes($_POST['lock_attributes']), true);
            update_post_meta($post_id, 'unlock_box_attributes', $attributes);
            wp_send_json_success();
        } else {
            wp_send_json_error();
        }
    }

    // Enqueue the necessary JavaScript for the Unlock Protocol meta box
    public function unlock_protocol_enqueue_scripts() 
    {
        wp_enqueue_script(
            'unlock-protocol-edit-fullpp',
            plugins_url('class-editfullpp.js', __FILE__),
            array('jquery'), 
            filemtime(plugin_dir_path(__FILE__) . 'class-editfullpp.js'), 
            true
        );
    }

}