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
  // TODO: add more settings (CTA, more locks... etc)
  ?>
  <script>
  var unlockProtocolConfig = {
    "locks": {
      "<?php echo esc_html(get_option('lock_address')); ?>":{}
    },
    "icon": "https://unlock-protocol.com/static/images/svg/unlock-word-mark.svg",
    "callToAction": {
      "default": "Become a member today!"
    }
  }
  </script>
  <style>
  .unlock-protocol__unlocked, .unlock-protocol__locked {
    display: none;
  }
  </style>
  <?php
  wp_enqueue_script( 'unlock_paywall_script', 'https://paywall.unlock-protocol.com/static/unlock.1.0.min.js');
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
    plugin_dir_url(__FILE__) . 'build/index.js',
    array('wp-blocks', 'wp-editor'),
    true
  );
  wp_enqueue_style( 'block-styles-stylesheet',
    plugins_url( 'src/editor.css', __FILE__ ));
}
add_action('enqueue_block_editor_assets', 'load_unlock_blocks');

// Adds Menu item for Unlock settings
function add_unlock_menu_items() {
  add_options_page("Unlock", "Unlock", "manage_options", "unlock", "unlock_options_page");
}
add_action("admin_menu", "add_unlock_menu_items");

// Adds Unlock settings page
function unlock_options_page() {
  ?>
  <h1>Unlock Settings</h1>
  <form method="post" action="options.php">
  <?php

  //add_settings_section callback is displayed here. For every new section we need to call settings_fields.
  settings_fields("header_section");

  // all the add_settings_field callbacks is displayed here
  do_settings_sections("unlock-settings");

  // Add the submit button to serialize the options
  submit_button();

  ?>
  </form>
  <?php
}

// Displays the unlock options
function display_unlock_options() {
  //section name, display name, callback to print description of section, page to which section is attached.
  add_settings_section("header_section", "", "display_header_options_content", "unlock-settings");

  //setting name, display name, callback to print form element, page in which field is displayed, section to which it belongs.
  //last field section is optional.
  add_settings_field("lock_address", "Lock Address", "lock_address_form_element", "unlock-settings", "header_section");

  //section name, form element name, callback for sanitization
  register_setting("header_section", "lock_address");
}
add_action("admin_init", "display_unlock_options");

// Header for the unlock options
function display_header_options_content() {
  ?>
  <p>Once you have <a href="https://unlock-protocol.com/" target="_blank">deployed your lock</a>, please enter its address.</p>
  <?php
}

// Section for the unlock options
function lock_address_form_element() {
  ?>
  <input type="text" name="lock_address" id="lock_address" value="<?php echo esc_html(get_option('lock_address')); ?>" />
  <?php
}


add_filter('plugin_action_links_unlock-protocol/unlock-protocol.php', 'unlock_settings_link' );
function unlock_settings_link($links) {
  $url = esc_url( add_query_arg('page', 'unlock', get_admin_url() . 'admin.php') );
  // Create the link.
  $settings_link = "<a href='$url'>" . __( 'Settings' ) . '</a>';
  // Adds the link to the end of the array.
  array_push($links, $settings_link);
  return $links;
}



?>
