import { __ } from '@wordpress/i18n';
import { Button, TextControl, Notice, ColorPalette, ColorIndicator, ToggleControl } from '@wordpress/components';
import { useState, useEffect } from 'react';

function MediaUpload( {
    handle,
    label = __( 'Upload your image', 'unlock-protocol' ),
    buttonTitle = __( 'Media Upload', 'unlock-protocol' ),
    value = ''
} ) {
    const [ image, setImage ] = useState( '' );

    const openMediaUpload = () => {
        let image = wp.media({
            title: __( 'Insert Image', 'unlock-protocol' ),
            library : {
                type : 'image'
            },
            button: {
                text: __( 'Use this image', 'unlock-protocol' ) // button label text
            },
            multiple: false
        }).on('select', function( el ) { // it also has "open" and "close" events
            let uploadedImage = image.state().get('selection').first().toJSON();

            handle( uploadedImage );

            setImage( uploadedImage );
        }).open();
    };

    return (
        <>
            <div className="group">
                <p className="components-base-control__label">{ label }</p>

                <Button isSmall={ true } isPrimary={ true } onClick={ openMediaUpload }>{ buttonTitle }</Button>

                { value ? (
                    <>
                        <div>
                            <img className="media-placeholder-image" src={ value } alt={ __( 'Unlock Protocol Image', 'unlock-protocol' ) } />
                        </div>
                    </>
                ) : '' }
            </div>
        </>
    );
};

export default MediaUpload;