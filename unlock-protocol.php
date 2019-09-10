<?php
/**
* Plugin Name: Unlock Protocol
* Plugin URI: https://unlock-protocol.com
* Description: A plugin to add lock(s) to blocks of content inside of Wordpress
* Version: 1.0
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
      "<?php echo get_option('lock_address'); ?>":{}
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
      document.querySelectorAll('.unlock-protocol__unlocked').forEach(element => {
        element.style.display = "none";
      })
      document.querySelectorAll('.unlock-protocol__locked').forEach(element => {
        element.style.display = "block";
      })
    } else if (state === 'unlocked') {
      document.querySelectorAll('.unlock-protocol__unlocked').forEach(element => {
        element.style.display = "block";
      })
      document.querySelectorAll('.unlock-protocol__locked').forEach(element => {
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
    plugin_dir_url(__FILE__) . 'unlock-blocks.js',
    array('wp-blocks','wp-editor'),
    true
  );
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
  <input type="text" name="lock_address" id="lock_address" value="<?php echo get_option('lock_address'); ?>" />
  <?php
}
?>