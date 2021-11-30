import { __ } from '@wordpress/i18n';
import { PanelBody, TextControl, SelectControl } from '@wordpress/components';
import { useEffect } from 'react';
import { InspectorControls, useBlockProps, InnerBlocks } from '@wordpress/block-editor';
import { getBlockTypes } from '@wordpress/blocks';
import apiFetch from '@wordpress/api-fetch';
import '../../../scss/admin/editor.scss';

export default function Edit( { attributes, setAttributes } ) {

	const {	lockAddress, ethereumNetwork, ethereumNetworks } = attributes;
	let lockAddressValidation;

	//Preventing the own block.
	const ALLOWED_BLOCKS = getBlockTypes().map( block => block.name ).filter( blockName => blockName !== 'unlock-protocol/unlock-box' );

	useEffect( () => {
		apiFetch( {
			path: '/unlock-protocol/v1/settings'
		} )
			.then( ( resp ) => {
				let networks = resp.networks;

                let selectOptions = [
					{
						label: __( 'None', 'unlock-protocol' ),
						value: -1
					}
				];

				Object.entries( networks ).forEach( ( [key, item] ) => {
					selectOptions.push( {
                        label: item.network_name,
                        value: key
                    } );
				} );

                setAttributes( { ethereumNetworks : selectOptions } );
			} )
			.catch( ( err ) => { } );

	}, [] );

    /**
     * Set values in state attribute.
     * @param {*} key
     * @param {*} value
     */
	const onChangeValue = ( key, value ) => {
		setAttributes( { [ key ] : value } );
	}

	const showInnerBlock = () => {
		wp.data.dispatch( 'core/editor' ).unlockPostSaving( 'my-lock' );

		return (
			<InnerBlocks allowedBlocks={ ALLOWED_BLOCKS } />
		);
	}

	const lockWarning = () => {
		wp.data.dispatch( 'core/editor' ).lockPostSaving( 'my-lock' );

		return (
			<div className="no-lock-address">
				<p>{ __( 'Please configure the lock.', 'unlock-protocol' ) }</p>
			</div>
		);
	}

	const checkEthereumNetworkValidation = () => {
		if ( '' === lockAddress ) {
			return;
		}

		let regexp = '^0x[a-fA-F0-9]{40}$';
		let result = new RegExp(regexp, 'g').test( lockAddress );

		if ( ! result ) {
			lockAddressValidation = false;

			return (
				<p className="lock-warning">{ __( 'Lock address is not valid', 'unlock-protocol' ) }</p>
			);
		} else {
			lockAddressValidation = true;
		}
	}

	return (
		<>
			<div { ...useBlockProps() }>
				<InspectorControls>
					<PanelBody title={ __( 'Settings', 'unlock-protocol' ) }>
						<SelectControl
							label={ __( 'Ethereum Network', 'unlock-protocol' ) }
							value={ ethereumNetwork }
							options={ ethereumNetworks }
							onChange={ ( value ) => onChangeValue( 'ethereumNetwork', parseInt( value ) ) }
						/>

						{ -1 !== ethereumNetwork ? (
							<>
								<p><strong>{ __( 'Lock Address', 'unlock-protocol' ) }</strong></p>
								<TextControl
									value={ lockAddress }
									onChange={ ( value ) => {
										onChangeValue( 'lockAddress', value )
									} }
								/>
								{ checkEthereumNetworkValidation() }
							</>
						) : '' }

						<a rel="noopener noreferrer" target="_blank" href={ unlockProtocol.unlock_docs.docs }>
							{ __( 'Unlock\'s documentation', 'unlock-protocol' ) }
						</a>

						<br />
						<br />

						<a rel="noopener noreferrer" target="_blank" href={ unlockProtocol.unlock_docs.deploy_lock }>
							{ __( 'Deploy a lock using the Unlock Dashboard', 'unlock-protocol' ) }
						</a>
					</PanelBody>
				</InspectorControls>

				<div className="unlock-header-icon"></div>

				{ -1 !== ethereumNetwork && '' !== lockAddress && lockAddressValidation ? showInnerBlock() : lockWarning() }
			</div>
		</>
	);
}