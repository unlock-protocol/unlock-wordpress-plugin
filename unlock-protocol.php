<?php
/**
* Plugin Name: Unlock Protocol Plugin
* Plugin URI: https://github.com/unlock-protocol/unlock-wordpress-plugin
* Description: A plugin to add lock(s) to blocks of content inside of Wordpress, on both pages and posts.
  See https://www.ibenic.com/enable-inner-blocks-gutenberg/ for details about the implementation
* Version: 1.6.0
* Author: Unlock
* Author URI: https://unlock-protocol.com
*/

// Add the config to the head + styles
function load_unlock() {
  $unlockConfig = get_post_meta( get_the_ID(), '_unlock_protocol_config', true );
  // Only on posts with a an unlock config
  if($unlockConfig) {
    wp_enqueue_script( 'unlock_paywall_script', 'https://paywall.unlock-protocol.com/static/unlock.1.0.min.js');
    ?>
    <script>
    var unlockProtocolConfig = <?php echo $unlockConfig; ?>
    </script>
    <style>
      .unlock-protocol__unlocked, .unlock-protocol__locked {
        display: none;
      }
    </style>
  <?php
  }
}
add_action('wp_head', 'load_unlock');

// Add the event listener at the end of the <body>
function add_unlock_event_listener() {
  ?>
  <script>
  window.addEventListener('unlockProtocol', function(e) {
    var state = e.detail
    document.querySelectorAll('.unlock-protocol__pending').forEach(element => {
      element.style.display = "none";
    })
    if (state === 'locked') {
      console.log('Unlock: visitor is not a member')
      document.querySelectorAll('.unlock-protocol__locked').forEach(element => {
        element.style.display = "none";
      })
      document.querySelectorAll('.unlock-protocol__unlocked').forEach(element => {
        element.style.display = "block";
      })
    } else if (state === 'unlocked') {
      console.log('Unlock: visitor is a member')
      document.querySelectorAll('.unlock-protocol__locked').forEach(element => {
        element.style.display = "block";
      })
      document.querySelectorAll('.unlock-protocol__unlocked').forEach(element => {
        element.style.display = "none";
      })
    }
  })
  </script>
  <?php
}
add_action('wp_footer', 'add_unlock_event_listener');

// Adds the blocks for the Gutember Editor
function load_unlock_blocks() {
  wp_enqueue_script(
    'locked_block',
    plugin_dir_url(__FILE__) . 'build/blocks.js',
    array('wp-blocks', 'wp-editor'),
    true
  );
  wp_enqueue_style( 'block-styles-stylesheet',
    plugins_url( 'src/editor.css', __FILE__ ));
}
add_action('enqueue_block_editor_assets', 'load_unlock_blocks');

// Adds Sidebar to add Unlock configuration to each post
function sidebar_plugin_register() {
  wp_enqueue_script(
      'unlock-sidebar',
      plugin_dir_url(__FILE__) . 'build/sidebar.js',
      array( 'wp-plugins', 'wp-edit-post', 'wp-element' )
  );
}
add_action( 'enqueue_block_editor_assets', 'sidebar_plugin_register' );

// Register meta for posts
// TODO: add for page as well?
function register_meta_unlock_protocol_config() {
  register_meta('post', '_unlock_protocol_config', array(
    'show_in_rest' => true,
    'type' => 'string',
    'single' => true,
    'sanitize_callback' => 'sanitize_text_field',
    'auth_callback' => function() {
      return current_user_can('edit_posts');
    }
  ));
}
add_action('init', 'register_meta_unlock_protocol_config');


?>
