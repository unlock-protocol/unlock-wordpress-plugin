<?php

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}



// Include needed external files and their functions
require_once plugin_dir_path( __FILE__ ) . '../unlockprotocol-flow/unlock.php';



/**
 * Filter the content of locked posts/pages.
 *
 * @param string $content Post/Page Content.
 *
 * @since 3.0.0
 *
 * @return string HTML elements.
 */
function unlock_box_fullpp_filter($content) {
    global $post;

    // Step 1: Retrieve the attributes from the database
    $attributes = get_post_meta($post->ID, 'unlock_box_attributes', true);

    // Step 2: Retrieve the current up-to-date network list from the database
    $networkList = get_option('unlock_protocol_settings');
    $networks = $networkList['networks'];

    // Step 3: Iterate through the network list to locate the corresponding network ID
    $formattedNetworks = array_map(function ($net) {
        return array(
            'name' => $net['network_name'],
            'value' => $net['network_id'],
        );
    }, array_values($networks));

    // Check if the attributes are valid
    if (!$attributes || !isset($attributes['locks']) || !isset($attributes['ethereumNetworks'])) {
        return $content;
    }


	// Step 4: Reconstruct a new attributes format to match the old structure
	$locks = array();
	foreach ($attributes['locks'] as $index => $lock) {
		$network_id = $formattedNetworks[array_search($attributes['ethereumNetworks'][$index], array_column($formattedNetworks, 'value'))]['value'];
		$locks[] = array(
			'address' => $lock['lockAddress'],
			'network' => $network_id,
		);
	}

	// Pass the reconstructed attributes to the render_fullpp_content function
	$new_attributes = array(
		'locks' => $locks,
		'ethereumNetworks' => $formattedNetworks,
	);
    return render_fullpp_content($new_attributes, $content);
	
}
add_filter('the_content', 'unlock_box_fullpp_filter');




/**
 * Render the locked content.
 *
 * @param array  $attributes List of attributes passed in block.
 * @param string $content Block Content.
 *
 * @since 3.0.0
 *
 * @return string HTML elements.
 */
function render_fullpp_content( $attributes, $content ) 
{
	// Bail out if current user is admin or the author.
	if ( current_user_can( 'manage_options' ) || ( get_the_author_meta( 'ID' ) === get_current_user_id() ) ) {
		return $content;
	}

	if (
		! is_user_logged_in() ||
		( is_user_logged_in() && ! up_get_user_ethereum_address() )
	) {
		$login_button_text       = up_get_general_settings( 'login_button_text', __( 'Login with Unlock', 'unlock-protocol' ) );
		$login_button_bg_color   = up_get_general_settings( 'login_button_bg_color', '#000' );
		$login_button_text_color = up_get_general_settings( 'login_button_text_color', '#fff' );
		$blurred_image_activated = wp_validate_boolean( up_get_general_settings( 'login_blurred_image_button', false ) );

		$template_data = array(
			'login_url'               => get_login_url( get_permalink() ),
			'login_button_text'       => $login_button_text,
			'login_button_bg_color'   => $login_button_bg_color,
			'login_button_text_color' => $login_button_text_color,
			'blurred_image_activated' => $blurred_image_activated,
		);

		// Fetching some more data if blurred image button type is activated.
		if ( $blurred_image_activated ) {
			$login_button_description = up_get_general_settings( 'login_button_description', __( 'To view this content please', 'unlock-protocol' ) );
			$login_bg_image           = up_get_general_settings( 'login_bg_image' );

			$template_data['login_button_description'] = $login_button_description;
			$template_data['login_bg_image']           = $login_bg_image;
		}

		$html_template = unlock_protocol_get_template( 'login/button', $template_data );

		return apply_filters( 'unlock_protocol_login_content', $html_template, $template_data );
	}

// Print $attributes
echo '<pre>NEW $attributes variable output: ';
var_dump($attributes);
echo '</pre>';

		$locks = $attributes['locks'];
// Print $locks
echo '<pre>NEW $locks variable output: ';
var_dump($locks);
echo '</pre>';

		$settings = get_option( 'unlock_protocol_settings', array() );
		$networks = isset( $settings['networks'] ) ? $settings['networks'] : array();
// Print $networks
echo '<pre>NEW $networks variable output: ';
var_dump($networks);
echo '</pre>';

	if ( has_access( $networks, $locks ) ) {
		return $content;
	}

	return get_fullpp_checkout_url( $attributes );
}



/**
 * Get checkout URL for the full post/page.
 *
 * @param array $attributes Attributes.
 *
 * @return mixed|void
 */
function get_fullpp_checkout_url( $attributes ) 
{
	$checkout_url = get_checkout_url( $attributes["locks"], get_permalink() );

	$checkout_button_text       = up_get_general_settings( 'checkout_button_text', __( 'Purchase this', 'unlock-protocol' ) );
	$checkout_button_bg_color   = up_get_general_settings( 'checkout_button_bg_color', '#000' );
	$checkout_button_text_color = up_get_general_settings( 'checkout_button_text_color', '#fff' );
	$blurred_image_activated    = wp_validate_boolean( up_get_general_settings( 'checkout_blurred_image_button', false ) );

	$template_data = array(
		'checkout_url'               => $checkout_url,
		'checkout_button_text'       => $checkout_button_text,
		'checkout_button_bg_color'   => $checkout_button_bg_color,
		'checkout_button_text_color' => $checkout_button_text_color,
		'blurred_image_activated'    => $blurred_image_activated,
	);

	// Fetching some more data if blurred image button type is activated.
	if ( $blurred_image_activated ) {
		$checkout_button_description = up_get_general_settings( 'checkout_button_description', __( 'To view this content please', 'unlock-protocol' ) );
		$checkout_bg_image           = up_get_general_settings( 'checkout_bg_image' );

		$template_data['checkout_button_description'] = $checkout_button_description;
		$template_data['checkout_bg_image']           = $checkout_bg_image;
	}

	$html_template = unlock_protocol_get_template( 'login/checkout-button', $template_data );

	return apply_filters( 'unlock_protocol_checkout_content', $html_template, $template_data );
}
