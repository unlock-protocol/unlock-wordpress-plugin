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
						'type'    => 'string',
						'default' => '',
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
		$ethereum_network = $attributes['ethereumNetwork'];

		if ( is_user_logged_in() ) {
			return $content;
		}

		$login = Login::get_instance();

		$login_button_text = get_general_settings( 'login_text', __( 'Login with Unlock', 'unlock-protocol' ) );

		return unlock_protocol_get_template(
			'login/button',
			array(
				'login_url'         => $login->get_login_url(),
				'login_button_text' => $login_button_text,
			)
		);
	}
}
