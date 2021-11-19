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
					'lockAddress'      => array(
						'type'    => 'string',
						'default' => '',
					),
					'ethereumNetworks' => array(
						'type'    => 'array',
						'default' => array(),
					),
					'ethereumNetwork'  => array(
						'type'    => 'integer',
						'default' => -1,
					),
				),
				'supports'        => array(
					'align' => true,
				),
			)
		);
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

		if (
			! is_user_logged_in() ||
			( is_user_logged_in() && ! up_get_user_ethereum_address() )
		) {
			$login_button_text       = up_get_general_settings( 'login_button_text', __( 'Login with Unlock', 'unlock-protocol' ) );
			$login_button_bg_color   = up_get_general_settings( 'login_button_bg_color', '#000' );
			$login_button_text_color = up_get_general_settings( 'login_button_text_color', '#fff' );

			$template_data = array(
				'login_url'               => Unlock::get_login_url( get_permalink() ),
				'login_button_text'       => $login_button_text,
				'login_button_bg_color'   => $login_button_bg_color,
				'login_button_text_color' => $login_button_text_color,
			);

			$html_template = unlock_protocol_get_template( 'login/button', $template_data );

			return apply_filters( 'unlock_protocol_login_content', $html_template, $template_data );
		}

		$ethereum_network = $attributes['ethereumNetwork'];

		if ( -1 === $ethereum_network || '' === $attributes['lockAddress'] ) {
			return '';
		}

		$settings = get_option( 'unlock_protocol_settings', array() );
		$networks = isset( $settings['networks'] ) ? $settings['networks'] : array();

		if ( ! isset( $networks[ $ethereum_network ] ) ) {
			return '';
		}

		$selected_network = $networks[ $ethereum_network ];

		if ( isset( $selected_network['network_rpc_endpoint'] ) && Unlock::has_access( $selected_network['network_rpc_endpoint'], $attributes['lockAddress'] ) ) {
			return $content;
		}

		return $this->get_checkout_url( $attributes, $selected_network );
	}

	/**
	 * Get checkout url for block
	 *
	 * @param array $attributes Attributes.
	 * @param array $selected_network Selected Network.
	 *
	 * @return mixed|void
	 */
	private function get_checkout_url( $attributes, $selected_network ) {
		$checkout_url = Unlock::get_checkout_url( $attributes['lockAddress'], $selected_network['network_id'], get_permalink() );

		$checkout_button_text       = up_get_general_settings( 'checkout_button_text', __( 'Purchase this', 'unlock-protocol' ) );
		$checkout_button_bg_color   = up_get_general_settings( 'checkout_button_bg_color', '#000' );
		$checkout_button_text_color = up_get_general_settings( 'checkout_button_text_color', '#fff' );

		$template_data = array(
			'checkout_url'               => $checkout_url,
			'checkout_button_text'       => $checkout_button_text,
			'checkout_button_bg_color'   => $checkout_button_bg_color,
			'checkout_button_text_color' => $checkout_button_text_color,
		);

		$html_template = unlock_protocol_get_template( 'login/checkout-button', $template_data );

		return apply_filters( 'unlock_protocol_checkout_content', $html_template, $template_data );
	}
}
