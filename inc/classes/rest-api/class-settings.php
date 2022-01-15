<?php
/**
 * Settings API class.
 *
 * @since 3.0.0
 *
 * @package unlock-protocol
 */

namespace Unlock_Protocol\Inc\Rest_Api;

use WP_Http;
use WP_REST_Server;

/**
 * Class Settings API
 *
 * @since 3.0.0
 */
class Settings extends Rest_Base {

	/**
	 * Initialize the class
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		$this->rest_base = 'settings';
	}

	/**
	 * Registers the routes for the objects of the controller.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->get_namespace(),
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_settings_data' ),
					'permission_callback' => array( $this, 'admin_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_settings' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
					'permission_callback' => array( $this, 'admin_permissions_check' ),
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);

		register_rest_route(
			$this->get_namespace(),
			'/' . $this->rest_base . '/delete',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'delete_network' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::DELETABLE ),
					'permission_callback' => array( $this, 'admin_permissions_check' ),
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);
	}

	/**
	 * Get settings data.
	 *
	 * @param object $request Request Object.
	 *
	 * @since 3.0.0
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_settings_data( $request ) {
		$settings = get_option( 'unlock_protocol_settings', array() );

		return rest_ensure_response( $settings );
	}

	/**
	 * Update settings.
	 *
	 * @param object $request Request Object.
	 *
	 * @since 3.0.0
	 *
	 * @return bool|void|\WP_Error|\WP_REST_Response
	 */
	public function update_settings( $request ) {
		$section = sanitize_text_field( $request->get_param( 'section' ) );

		if ( 'general' === $section ) {
			$update = $this->update_general_settings( $request->get_param( 'settings' ) );

			if ( is_wp_error( $update ) ) {
				return $update;
			}
		}

		if ( 'networks' === $section ) {
			$update = $this->update_networks_setting( $request->get_param( 'network_id' ), $request->get_param( 'network_name' ), $request->get_param( 'network_rpc_endpoint' ) );

			if ( is_wp_error( $update ) ) {
				return $update;
			}
		}

		return $this->get_settings_data( $request );
	}

	/**
	 * Delete item.
	 *
	 * @param object $request Request Object.
	 *
	 * @since 3.0.0
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function delete_network( $request ) {
		$network_index = (int) sanitize_text_field( $request->get_param( 'network_index' ) );

		if ( '' === $network_index || ! is_int( $network_index ) ) {
			return new \WP_Error(
				'unlock_protocol_empty_data',
				__( 'Inputs can not be empty!', 'unlock-protocol' ),
				[ 'status' => WP_Http::BAD_REQUEST ]
			);
		}

		$networks = array();

		$settings = get_option( 'unlock_protocol_settings', array() );

		if ( isset( $settings['networks'] ) ) {
			$networks = $settings['networks'];
		}

		// Removed network details.
		$removed_network = $networks[ $network_index ];

		// Removing network.
		unset( $networks[ $network_index ] );

		$settings['networks'] = $networks;

		update_option( 'unlock_protocol_settings', $settings, false );

		do_action( 'unlock_protocol_network_deleted', $removed_network, $settings['networks'] );

		return $this->get_settings_data( $request );
	}

	/**
	 * Update general settings.
	 *
	 * @param array $data General settings data.
	 *
	 * @since 3.0.0
	 *
	 * @return bool
	 */
	public function update_general_settings( $data ) {
		$data = array_map( 'sanitize_text_field', $data );

		$settings = get_option( 'unlock_protocol_settings', array() );

		$settings['general'] = $data;

		$update = update_option( 'unlock_protocol_settings', $settings, false );

		return $update;
	}

	/**
	 * Update network settings.
	 *
	 * @param string $network_id Network ID.
	 * @param string $network_name Network Name.
	 * @param string $network_rpc_endpoint Network RPC endpoint.
	 *
	 * @since 3.0.0
	 *
	 * @return \WP_Error|bool
	 */
	public function update_networks_setting( $network_id, $network_name, $network_rpc_endpoint ) {
		$network_id           = sanitize_text_field( $network_id );
		$network_name         = sanitize_text_field( $network_name );
		$network_rpc_endpoint = sanitize_text_field( $network_rpc_endpoint );

		if ( empty( $network_id ) || empty( $network_name ) || empty( $network_rpc_endpoint ) ) {
			return new \WP_Error(
				'unlock_protocol_empty_data',
				__( 'Inputs can not be empty!', 'unlock-protocol' ),
				[ 'status' => WP_Http::BAD_REQUEST ]
			);
		}

		$networks = array();

		$settings = get_option( 'unlock_protocol_settings', array() );

		if ( isset( $settings['networks'] ) ) {
			$networks = $settings['networks'];

			foreach ( $networks as $item ) {
				if ( (int) $network_id === $item['network_id'] ) {
					return new \WP_Error(
						'unlock_protocol_duplicate_network_id',
						__( 'This network ID already exists.', 'unlock-protocol' ),
						[ 'status' => WP_Http::BAD_REQUEST ]
					);
				}
			}
		}

		array_push(
			$networks,
			array(
				'network_id'           => (int) $network_id,
				'network_name'         => $network_name,
				'network_rpc_endpoint' => $network_rpc_endpoint,
			)
		);

		$settings['networks'] = $networks;

		$update = update_option( 'unlock_protocol_settings', $settings, false );

		do_action( 'unlock_protocol_network_updated', $settings['networks'] );

		return $update;
	}
}
