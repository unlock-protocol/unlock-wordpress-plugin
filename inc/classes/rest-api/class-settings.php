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
					'callback'            => array( $this, 'update_items' ),
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
					'callback'            => array( $this, 'delete_item' ),
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
		$settings = array(
			'unlock_protocol_networks' => get_option( 'unlock_protocol_networks', array() ),
		);

		return rest_ensure_response( $settings );
	}

	/**
	 * Update items.
	 *
	 * @param object $request Request Object.
	 *
	 * @since 3.0.0
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function update_items( $request ) {
		$network_id           = sanitize_text_field( $request->get_param( 'network_id' ) );
		$network_name         = sanitize_text_field( $request->get_param( 'network_name' ) );
		$network_rpc_endpoint = sanitize_text_field( $request->get_param( 'network_rpc_endpoint' ) );

		if ( empty( $network_id ) || empty( $network_name ) || empty( $network_rpc_endpoint ) ) {
			return new \WP_Error(
				'unlock_protocol_empty_data',
				__( 'Inputs can not be empty!', 'unlock-protocol' ),
				[ 'status' => WP_Http::BAD_REQUEST ]
			);
		}

		$existing_networks = get_option( 'unlock_protocol_networks', array() );

		array_push(
			$existing_networks,
			array(
				'network_id'           => $network_id,
				'network_name'         => $network_name,
				'network_rpc_endpoint' => $network_rpc_endpoint,
			)
		);

		update_option( 'unlock_protocol_networks', $existing_networks, false );

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
	public function delete_item( $request ) {
		$network_index = (int) sanitize_text_field( $request->get_param( 'network_index' ) );

		if ( '' === $network_index || ! is_int( $network_index ) ) {
			return new \WP_Error(
				'unlock_protocol_empty_data',
				__( 'Inputs can not be empty!', 'unlock-protocol' ),
				[ 'status' => WP_Http::BAD_REQUEST ]
			);
		}

		$existing_networks = get_option( 'unlock_protocol_networks', array() );

		// Removing network.
		unset( $existing_networks[ $network_index ] );

		// Reindexing array.
		$networks = array_values( $existing_networks );

		update_option( 'unlock_protocol_networks', $networks, false );

		return $this->get_settings_data( $request );
	}
}
