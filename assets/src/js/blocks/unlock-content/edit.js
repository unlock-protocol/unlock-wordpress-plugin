import { __ } from '@wordpress/i18n';
import { PanelBody, TextControl, SelectControl } from '@wordpress/components';
import { useEffect } from 'react';
import { InspectorControls, useBlockProps, InnerBlocks } from '@wordpress/block-editor';
import { getBlockTypes } from '@wordpress/blocks';
import apiFetch from '@wordpress/api-fetch';
import '../../../scss/admin/editor.scss';

export default function Edit( { attributes, setAttributes } ) {

	const {	lockAddress, ethereumNetwork, ethereumNetworks } = attributes;

	//Preventing the own block.
	const ALLOWED_BLOCKS = getBlockTypes().map( block => block.name ).filter( blockName => blockName !== 'unlock-protocol/unlock-box' );

	useEffect( () => {

		console.log( 'ethereumNetwork', ethereumNetwork, typeof ethereumNetwork );

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

                networks.map( ( item, index ) => {
                    selectOptions.push( {
                        label: item.network_name,
                        value: index
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
							onChange={ ( value ) => onChangeValue( 'ethereumNetwork', parseInt( value ) ) }
						/>

						<a rel="noopener noreferrer" target="_blank" href={ unlockProtocol.unlock_docs }>
							{ __( 'Unlock\'s documentation', 'unlock-protocol' ) }
						</a>
					</PanelBody>
				</InspectorControls>

				<div className="unlock-header-icon"></div>

				<InnerBlocks allowedBlocks={ ALLOWED_BLOCKS } />
			</div>
		</>
	);
}