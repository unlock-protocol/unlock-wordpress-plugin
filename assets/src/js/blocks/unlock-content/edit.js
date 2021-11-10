import { __ } from '@wordpress/i18n';
import { PanelBody, TextControl, SelectControl } from '@wordpress/components';
import { useEffect } from 'react';
import { InspectorControls, useBlockProps, InnerBlocks } from '@wordpress/block-editor';
import apiFetch from '@wordpress/api-fetch';

export default function Edit( { attributes, setAttributes } ) {

	const {	lockAddress, ethereumNetwork, ethereumNetworks } = attributes;

    useEffect( () => {
		apiFetch( {
			path: '/unlock-protocol/v1/settings'
		} )
			.then( ( resp ) => {
				let networks = resp.networks;
                let selectOptions = [];

                networks.map( ( item, index ) => {
                    selectOptions.push( {
                        label: item.network_name,
                        value: item.network_rpc_endpoint
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

		console.log( key, value );

		setAttributes( { [ key ] : value } );
	}

	return (
		<>
			<div { ...useBlockProps() }>
				<InspectorControls>
					<PanelBody title={ __( 'Settings', 'unlock-protocol' ) }>
						<p><strong>{ __( 'Lock Address', 'unlock-protocol' ) }</strong></p>
						<TextControl value={ lockAddress }
							onChange={ ( value ) => onChangeValue( 'lockAddress', value ) }
						/>

						<SelectControl
							label={ __( 'Ethereum Network', 'unlock-protocol' ) }
							value={ ethereumNetwork }
							options={ ethereumNetworks }
							onChange={ ( value ) => onChangeValue( 'ethereumNetwork', value ) }
						/>
					</PanelBody>
				</InspectorControls>

				<InnerBlocks />
			</div>
		</>
	);
}