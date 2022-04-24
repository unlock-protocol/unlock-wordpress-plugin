<?php
/**
 * Unlock box dynamic block class.
 *
 * @since 3.0.0
 *
 * @package unlock-protocol
 */

namespace Unlock_Protocol\Inc\Blocks;

use Unlock_Protocol\Inc\Login;
use Unlock_Protocol\Inc\Traits\Singleton;
use Unlock_Protocol\Inc\Unlock;

/**
 * Class Unlock_Box_Block
 *
 * @since 3.0.0
 */
class Unlock_Box_Block {

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
		add_action( 'init', array( $this, 'register_block_type' ) );
	}

	/**
	 * Register block type.
	 *
	 * @since 3.0.0
	 */
	public function register_block_type() {
		register_block_type(
			'unlock-protocol/unlock-box',
			array(
				'render_callback' => array( $this, 'render_block' ),
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

	/**
     * Renders a button to connect the user's wallet!
     */
    public function render_connect_wallet_button() {
        $login_button_text       = up_get_general_settings( 'login_button_text', __( 'Login with Unlock', 'unlock-protocol' ) );
        $login_button_bg_color   = up_get_general_settings( 'login_button_bg_color', '#000' );
        $login_button_text_color = up_get_general_settings( 'login_button_text_color', '#fff' );
        $blurred_image_activated = wp_validate_boolean( up_get_general_settings( 'login_blurred_image_button', false ) );

        $template_data = array(
            'login_url'               => Unlock::get_login_url( get_permalink() ),
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

	/**
     * Renders a button to connect the user's wallet!
     */
    public function render_checkout_button() {
		$checkout_url = Unlock::get_checkout_url( $attributes["locks"], get_permalink() );

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
	public function render_block( $attributes, $content ) {
		// Bail out if current user is admin or the author.
		if ( current_user_can( 'manage_options' ) || ( get_the_author_meta( 'ID' ) === get_current_user_id() ) ) {
			return $content;
		}

		// If user is not logged in, render a login button
		if (
			! is_user_logged_in() ||
			( is_user_logged_in() && ! up_get_user_ethereum_address() )
		) {
			return self::render_connect_wallet_button();
		}

		$locks = $attributes['locks'];

		$settings = get_option( 'unlock_protocol_settings', array() );
		$networks = isset( $settings['networks'] ) ? $settings['networks'] : array();

		// If user does not have access, render a checkout button!
		if ( Unlock::has_access( $networks, $locks ) ) {
			return self::render_checkout_button($attributes);
		}

		return $content;
	}


}
