import { __ } from '@wordpress/i18n';
import { Button, TextControl, Notice, ColorPalette, ColorIndicator, ToggleControl } from '@wordpress/components';
import { useState, useEffect } from 'react';
import apiFetch from '@wordpress/api-fetch';
import MediaUpload from './utils/media-upload';

import '../../scss/admin/style.scss';

function General() {
	const [ isSubmitted, setIsSubmitted ] = useState( false );
	const [ isChanged, setIsChanged ] = useState( false );
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
				setIsChanged( false );
			} )
			.catch( ( err ) => {
				setIsSubmitted( false );
				setNoticeType( 'error' );
				setNotice( __( err.message, 'unlock-protocol' ) );
				setIsChanged( false );
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

		setIsChanged( true );
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
								label={ __( 'Post Login Button Text', 'unlock-protocol' ) }
								className={ 'login-button-text-input' }
								value={ generalSettings?.login_button_text }
								onChange={ ( value ) => onChangeValue( 'login_button_text', value ) }
							/>
						</div>

						<div className="group">
							<p className="components-base-control__label">{ __( 'Login Button Type', 'unlock-protocol' ) }</p>

							<ToggleControl
								label={ __( 'Enable for button with blurred image', 'unlock-protocol' ) }
								help={
								generalSettings?.login_blurred_image_button
								? __( 'Button with blurred image is activated', 'unlock-protocol' )
								: __( 'Simple button is activated', 'unlock-protocol' )
								}
								checked={ generalSettings?.login_blurred_image_button??false }
								onChange={ () => onChangeValue( 'login_blurred_image_button', ! generalSettings?.login_blurred_image_button ) }
							/>
						</div>

						{/* description option for blurred image button */}
						{ generalSettings?.login_blurred_image_button??false ? (
							<>
								<div className="group">
									<TextControl
										label={ __( 'Description', 'unlock-protocol' ) }
										className={ 'login-button-text-input' }
										value={ generalSettings?.login_button_description }
										onChange={ ( value ) => onChangeValue( 'login_button_description', value ) }
									/>
								</div>

								<MediaUpload
									label={ __( 'Upload Login Background Image', 'unlock-protocol' ) }
									value={ generalSettings?.login_bg_image??'' }
									handle={ ( data ) => {
										onChangeValue( 'login_bg_image', data.url??'' );
									} }
								/>
							</>
						) : '' }

						<div className="group">
							<p className="components-base-control__label">{ __( 'Login Button Background Color', 'unlock-protocol' ) }</p>

							<div className="color-picker-container">
								<ColorIndicator colorValue={ generalSettings?.login_button_bg_color??'#000' } />

								<ColorPalette
									colors={ [] }
									value={ generalSettings?.login_button_bg_color??'#000' }
									onChange={ ( color ) =>  onChangeValue( 'login_button_bg_color', color ) }
								/>
							</div>
						</div>

						<div className="group">
							<p className="components-base-control__label">{ __( 'Login Button Text Color', 'unlock-protocol' ) }</p>

							<div className="color-picker-container">
								<ColorIndicator colorValue={ generalSettings?.login_button_text_color??'#fff' } />

								<ColorPalette
									colors={ [] }
									value={ generalSettings?.login_button_text_color??'#fff' }
									onChange={ ( color ) =>  onChangeValue( 'login_button_text_color', color ) }
								/>
							</div>
						</div>

						<hr />

						{/* checkout options */}

						<div className="group">
							<TextControl
								label={ __( 'Checkout Button Text', 'unlock-protocol' ) }
								className={ 'checkout-button-text-input' }
								value={ generalSettings?.checkout_button_text }
								onChange={ ( value ) => onChangeValue( 'checkout_button_text', value ) }
							/>
						</div>

						<div className="group">
							<p className="components-base-control__label">{ __( 'Checkout Button Type', 'unlock-protocol' ) }</p>

							<ToggleControl
								label={ __( 'Enable for button with blurred image', 'unlock-protocol' ) }
								help={
								generalSettings?.checkout_blurred_image_button
								? __( 'Button with blurred image is activated', 'unlock-protocol' )
								: __( 'Simple button is activated', 'unlock-protocol' )
								}
								checked={ generalSettings?.checkout_blurred_image_button??false }
								onChange={ () => onChangeValue( 'checkout_blurred_image_button', ! generalSettings?.checkout_blurred_image_button ) }
							/>
						</div>

						{/* description option for blurred image button */}
						{ generalSettings?.checkout_blurred_image_button??false ? (
							<>
								<div className="group">
									<TextControl
										label={ __( 'Description', 'unlock-protocol' ) }
										className={ 'checkout-button-text-input' }
										value={ generalSettings?.checkout_button_description }
										onChange={ ( value ) => onChangeValue( 'checkout_button_description', value ) }
									/>
								</div>

								<MediaUpload
									label={ __( 'Upload Checkout Background Image', 'unlock-protocol' ) }
									value={ generalSettings?.checkout_bg_image??'' }
									handle={ ( data ) => {
										onChangeValue( 'checkout_bg_image', data.url??'' )
									} }
								/>
							</>
						) : '' }

						<div className="group">
							<p className="components-base-control__label">{ __( 'Checkout Button Background Color', 'unlock-protocol' ) }</p>

							<div className="color-picker-container">
								<ColorIndicator colorValue={ generalSettings?.checkout_button_bg_color??'#000' } />

								<ColorPalette
									colors={ [] }
									value={ generalSettings?.checkout_button_bg_color??'#000' }
									onChange={ ( color ) =>  onChangeValue( 'checkout_button_bg_color', color ) }
								/>
							</div>
						</div>

						<div className="group">
							<p className="components-base-control__label">{ __( 'Checkout Button Text Color', 'unlock-protocol' ) }</p>

							<div className="color-picker-container">
								<ColorIndicator colorValue={ generalSettings?.checkout_button_text_color??'#fff' } />

								<ColorPalette
									colors={ [] }
									value={ generalSettings?.checkout_button_text_color??'#fff' }
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
					disabled={ isSubmitted || ! isChanged }
				>
					{ isSubmitted ? __( 'Saving', 'unlock-protocol' ) : __( 'Save', 'unlock-protocol' ) }
				</Button>
			</div>
		</>
	);
}

export default ( General );
