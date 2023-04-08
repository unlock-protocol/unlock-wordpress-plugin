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

		register_rest_route(
			$this->get_namespace(),
			'/save_unlockp_full_post_page_attributes',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'save_unlockp_full_post_page_attributes' ),
				'args'                => array(
					'post_id' => array(
						'required' => true,
						'type'     => 'integer',
					),
					'unlockp_full_post_page_attributes' => array(
						'required' => true,
						'type'     => 'string',
					),
				),
				'permission_callback' => array( $this, 'admin_permissions_check' ),
			)
		);

		register_rest_route(
			$this->get_namespace(),
			'/get_unlockp_full_post_page_attributes',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_unlockp_full_post_page_attributes' ),
				'args'                => array(
					'post_id' => array(
						'required' => true,
						'type'     => 'integer',
					),
				),
				'permission_callback' => array( $this, 'admin_permissions_check' ),
			)
		);
		
		register_rest_route(
			$this->get_namespace(),
			'/delete_unlockp_full_post_page_attributes',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'delete_unlockp_full_post_page_attributes' ),
				'args'                => array(
					'post_id' => array(
						'required' => true,
						'type'     => 'integer',
					),
					'lock_index' => array(
						'required' => true,
						'type'     => 'integer',
					),
				),
				'permission_callback' => array( $this, 'admin_permissions_check' ),
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

	/**
	 * Save unlockp_full_post_page_attributes for a post.
	 *
	 * @param object $request Request Object.
	 *
	 * @since 3.0.0
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function save_unlockp_full_post_page_attributes( $request ) {
		$post_id = $request->get_param( 'post_id' );
		$unlockp_full_post_page_attributes = $request->get_param( 'unlockp_full_post_page_attributes' );
	
		if ( empty( $post_id ) || empty( $unlockp_full_post_page_attributes ) ) {
			return wp_send_json_error(
				[
					'message'   => __( 'Inputs cannot be empty!', 'unlock-protocol' ),
				]
			);
		}
	
		// Check if post_id is valid and exists
		if ( ! is_int( $post_id ) || get_post( $post_id ) === null ) {
			return wp_send_json_error(
				[
					'message' => __( 'Invalid post ID provided.', 'unlock-protocol' ),
				]
			);
		}
	
		// Check if the unlockp_full_post_page_attributes attribute has the correct format
		$decoded_unlockp_full_post_page_attributes = json_decode( $unlockp_full_post_page_attributes, true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return wp_send_json_error(
				[
					'message' => __( 'Invalid format for unlockp full post page attribute.', 'unlock-protocol' ),
				]
			);
		}
	
		$saved_unlockp_full_post_page_attributes = update_post_meta( $post_id, 'unlockp_full_post_page_attributes', $decoded_unlockp_full_post_page_attributes );
	
		if ( $saved_unlockp_full_post_page_attributes ) {
			return wp_send_json_success(
				[
					'message' => __( 'Unlockp full post page attributes saved successfully', 'unlock-protocol' ),
				]
			);
		} else {
			return wp_send_json_error(
				[
					'message' => __( 'Failed to update Unlockp full post page attributes.', 'unlock-protocol' ),
				]
			);
		}
	}
	
	/**
	 * Get unlockp_full_post_page_attributes for a post.
	 *
	 * @param object $request Request Object.
	 *
	 * @since 3.0.0
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_unlockp_full_post_page_attributes( $request ) {
		$post_id = $request->get_param( 'post_id' );

		if ( empty( $post_id ) ) {
			return new \WP_Error(
				'unlock_protocol_empty_data',
				__( 'Post ID cannot be empty!', 'unlock-protocol' ),
				[ 'status' => WP_Http::BAD_REQUEST ]
			);
		}

		// Check if post_id is valid and exists
		if ( ! is_int( $post_id ) || get_post( $post_id ) === null ) {
			return new \WP_Error(
				'unlock_protocol_invalid_post_id',
				__( 'Invalid post ID provided.', 'unlock-protocol' ),
				[ 'status' => WP_Http::BAD_REQUEST ]
			);
		}

		$unlockp_full_post_page_attributes = get_post_meta( $post_id, 'unlockp_full_post_page_attributes', true );

		return rest_ensure_response( $unlockp_full_post_page_attributes );
	}

	/**
	 * Delete a lock from the unlockp_full_post_page_attributes for a specific post/page.
	 *
	 * @param object $request Request Object.
	 *
	 * @since 3.0.0
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function delete_unlockp_full_post_page_attributes( $request ) {
		// Get the post ID and lock index from the request.
		$post_id = $request->get_param( 'post_id' );
		$lock_index = $request->get_param( 'lock_index' );

		// Check if the required parameters are empty or not.
		if ( !isset( $post_id ) || $post_id === '' || !isset( $lock_index ) || $lock_index === '' ) {
			return wp_send_json_error(
				[
					'message'   => __( 'Either Post id and Lock index (or both) inputs cannot be empty!', 'unlock-protocol' ),
				]
			);
		}


		// Check if the post ID is valid and exists.
		if ( ! is_int( $post_id ) || get_post( $post_id ) === null ) {
			return wp_send_json_error(
				[
					'message' => __( 'Invalid post ID provided.', 'unlock-protocol' ),
				]
			);
		}

		// Get the existing unlockpfullpp attributes for the post.
		$unlockp_full_post_page_attributes = get_post_meta( $post_id, 'unlockp_full_post_page_attributes', true );

		// Check if the lock index exists in the unlockpfullpp attributes.
		if ( ! isset( $unlockp_full_post_page_attributes['locks'][ $lock_index ] ) ) {
			return wp_send_json_error(
				[
					'message' => __( 'Lock index not found.', 'unlock-protocol' ),
				]
			);
		}

		// Remove the lock attributes for the specified index.
		unset( $unlockp_full_post_page_attributes['locks'][ $lock_index ] );

		// Re-index the array to remove any gaps caused by deleting the element.
		$unlockp_full_post_page_attributes['locks'] = array_values( $unlockp_full_post_page_attributes['locks'] );

		// Update the unlockpfullpp attributes for the post with the modified array.
		$saved_unlockp_full_post_page_attributes = update_post_meta( $post_id, 'unlockp_full_post_page_attributes', $unlockp_full_post_page_attributes );



		// Retrieve the "attributes" object again to either leave it or delete it from the database.
		// delete if the lock deleted above is the only lock left in the attributes object's lock(s) array.
		// leave the attributes object undeleted if there still remain at least 1 lock in its lock(s) array
		$full_post_page_attributes_object = get_post_meta( $post_id, 'unlockp_full_post_page_attributes', true );

		// Check if any locks are still present in the locks array
		$locks_present = false;
		if ( isset( $full_post_page_attributes_object['locks'] ) && is_array( $full_post_page_attributes_object['locks'] ) && count( $full_post_page_attributes_object['locks'] ) > 0 ) {
			$locks_present = true;
		}

		// Delete the attributes object entirely if no locks are present in the locks array
		if ( ! $locks_present ) {
			delete_post_meta( $post_id, 'unlockp_full_post_page_attributes' );
		}



		// Return the response as either success or error.
		if ( $saved_unlockp_full_post_page_attributes ) {
			return wp_send_json_success(
				[
					'message' => sprintf( __( 'Deleted lock id %s from unlockp full post page attributes successfully.', 'unlock-protocol' ), $lock_index ),
				]
			);
		} else {
			return wp_send_json_error(
				[
					'message' => sprintf( __( 'Failed to deleted lock id %s from unlockp full post page attributes.', 'unlock-protocol' ), $lock_index ),
				]
			);
		}

	}
}
