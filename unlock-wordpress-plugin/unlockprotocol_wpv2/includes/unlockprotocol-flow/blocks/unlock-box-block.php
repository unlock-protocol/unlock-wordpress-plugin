<?php

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}




// Include needed external files and their functions
require_once plugin_dir_path( __FILE__ ) . './unlock.php';




/**
 * Register block type.
 *
 * @since 3.0.0
 */
function register_block_type() {
    register_block_type(
        'unlock-protocol/unlock-box',
        array(
            'render_callback' => 'render_block',
            'attributes'      => array(
                'locks'      => array(
                    'type'    => 'array',
                    'default' => array(),
                ),
                'ethereumNetworks' => array(
                    'type'    => 'array',
                    'default' => array(),
                ),
            ),
            'supports'        => array(
                'align' => true,
            ),
        )
    );
}
add_action('init', 'register_block_type');



/**
 * Render block.
 *
 * @param array  $attributes List of attributes passed in block.
 * @param string $content Block Content.
 *
 * @since 3.0.0
 *
 * @return string HTML elements.
 */
function render_block( $attributes, $content ) 
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

	$locks = $attributes['locks'];

	$settings = get_option( 'unlock_protocol_settings', array() );
	$networks = isset( $settings['networks'] ) ? $settings['networks'] : array();

	if ( has_access( $networks, $locks ) ) {
		return $content;
	}

	return get_checkout_url( $attributes );
}



/**
 * Get checkout url for block
 *
 * @param array $attributes attributes.
 * @param array $networks networks from configuration.
 *
 * @return mixed|void
 */
function get_checkout_url( $attributes ) 
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

