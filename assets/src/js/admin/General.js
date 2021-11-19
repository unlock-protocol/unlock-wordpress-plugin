import { __ } from '@wordpress/i18n';
import { Button, TextControl, Notice, ColorPalette, ColorIndicator } from '@wordpress/components';
import { useState, useEffect } from 'react';
import apiFetch from '@wordpress/api-fetch';
import Swal from 'sweetalert2';

import '../../scss/admin/style.scss';

function General() {
	const [ isSubmitted, setIsSubmitted ] = useState( false );
	const [ generalSettings, setGeneralSettings ] = useState( {} );
	const [ noticeType, setNoticeType ] = useState( '' );
	const [ notice, setNotice ] = useState( '' );

	useEffect( () => {
		apiFetch( {
			path: '/unlock-protocol/v1/settings'
		} )
			.then( ( resp ) => {
                if ( resp?.general ) {
                    setGeneralSettings( resp.general );
                }
			} )
			.catch( ( err ) => { } );

	}, [] );

	/**
	 * Save Settings
	 */
	const saveGeneralSettings = () => {
		setIsSubmitted( true );

		let data = {
			section: 'general',
			settings: generalSettings
		};

		apiFetch( {
			path: '/unlock-protocol/v1/settings',
			method: 'POST',
			data: data
		} )
			.then( ( resp ) => {
				setIsSubmitted( false );

				if ( resp?.general ) {
                    setGeneralSettings( resp.general );
                }

                setNoticeType( 'success' );
				setNotice( __( 'Updated Successfully!', 'unlock-protocol' ) );
			} )
			.catch( ( err ) => {
				setIsSubmitted( false );
				setNoticeType( 'error' );
				setNotice( __( err.message, 'unlock-protocol' ) );
			} );
	};

	const renderNotice = () => {
		if ( '' === notice ) {
			return;
		}

		return <Notice status={ noticeType } onRemove={ () => setNotice( '' ) }>{ notice }</Notice>;
	};

	/**
	 * Update state on change value.
	 *
	 * @param {*} key
	 * @param {*} value
	 *
	 * @returns void
	 */
	const onChangeValue = ( key, value ) => {
		setGeneralSettings( {
			...generalSettings,
			[key] : value
		} );
	}

	return (
		<>
			<div className="settings_container__general">
				<h4>{ __( 'General Settings', 'unlock-protocol' ) }</h4>

                { renderNotice() }

                <div className="input-container">
                    <div className="form-inputs">
                        <div className="group">
                            <TextControl
                                label={ __( 'Login Button Text', 'unlock-protocol' ) }
                                className={ 'login-button-text-input' }
                                value={ generalSettings?.login_button_text }
                                onChange={ ( value ) => onChangeValue( 'login_button_text', value ) }
                            />
                        </div>

                        <div className="group">
							<p>{ __( 'Login Button Background Color', 'unlock-protocol' ) }</p>

							<div className="color-picker-container">
								<ColorIndicator colorValue={ generalSettings?.login_button_bg_color } />

								<ColorPalette
									colors={ [] }
									value={ generalSettings?.login_button_bg_color }
									onChange={ ( color ) =>  onChangeValue( 'login_button_bg_color', color ) }
								/>
							</div>
                        </div>

                        <div className="group">
							<p>{ __( 'Login Button Text Color', 'unlock-protocol' ) }</p>

							<div className="color-picker-container">
								<ColorIndicator colorValue={ generalSettings?.login_button_text_color } />

								<ColorPalette
									colors={ [] }
									value={ generalSettings?.login_button_text_color }
									onChange={ ( color ) =>  onChangeValue( 'login_button_text_color', color ) }
								/>
							</div>
                        </div>

						<hr />

						<div className="group">
                            <TextControl
                                label={ __( 'Checkout Button Text', 'unlock-protocol' ) }
                                className={ 'checkout-button-text-input' }
                                value={ generalSettings?.checkout_button_text }
                                onChange={ ( value ) => onChangeValue( 'checkout_button_text', value ) }
                            />
                        </div>

                        <div className="group">
							<p>{ __( 'Checkout Button Background Color', 'unlock-protocol' ) }</p>

							<div className="color-picker-container">
								<ColorIndicator colorValue={ generalSettings?.checkout_button_bg_color } />

								<ColorPalette
									colors={ [] }
									value={ generalSettings?.checkout_button_bg_color }
									onChange={ ( color ) =>  onChangeValue( 'checkout_button_bg_color', color ) }
								/>
							</div>
                        </div>

                        <div className="group">
							<p>{ __( 'Checkout Button Text Color', 'unlock-protocol' ) }</p>

							<div className="color-picker-container">
								<ColorIndicator colorValue={ generalSettings?.checkout_button_text_color } />

								<ColorPalette
									colors={ [] }
									value={ generalSettings?.checkout_button_text_color }
									onChange={ ( color ) =>  onChangeValue( 'checkout_button_text_color', color ) }
								/>
							</div>
                        </div>
                    </div>
                </div>

				<Button
					type="submit"
					isPrimary={ true }
					onClick={ () => saveGeneralSettings() }
					isBusy={ isSubmitted }
					disabled={ isSubmitted }
				>
					{ isSubmitted ? __( 'Saving', 'unlock-protocol' ) : __( 'Save', 'unlock-protocol' ) }
				</Button>
            </div>
		</>
	);
}

export default ( General );
