import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks } from '@wordpress/block-editor';
import Edit from './edit';

/**
 * Register the block.
 */
 registerBlockType( "unlock-protocol/unlock-box", {

	title: __( 'Unlock Protocol', 'unlock-protocol' ),

	category: "common",

	icon: "lock",

	description: __( 'A block to add lock(s) to the content inside of WordPress.', 'unlock-protocol' ),

	attributes: {
        lockAddress: {
			type: 'string',
			default: ''
		},
        ethereumNetworks: {
			type: 'array',
      		default: []
		},
        ethereumNetwork: {
			type: 'integer',
      		default: -1
		}
    },

	supports: {
		align: true
	},

	/**
	 * @see ./edit.js
	 */
	edit: Edit,

	save: () => {
		return <InnerBlocks.Content/>;
	}
} );