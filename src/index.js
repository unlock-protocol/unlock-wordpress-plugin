const { registerBlockType } = window.wp.blocks;
const { RichText } = wp.editor;

// Block for content that is only available to owners of keys!
registerBlockType( 'unlock/unlocked-box', {
	title: 'Unlocked Box - Visible to members only',
	icon: 'lock',
	category: 'common',
	attributes: {
		content: { type: 'string' },
	},

	edit: ( { setAttributes, attributes } ) => {
		const onChangeContent = ( value ) => {
			setAttributes( { content: value } );
		};
		return <RichText
			placeholder="Add your content which will only be visible by key holders!"
			value={ attributes.content }
			onChange={ onChangeContent }
			className="unlock__unlocked-box"
		/>;
	},

	save: ( { attributes } ) => {
		return <RichText.Content
			tagName="p"
			className="unlock-protocol__unlocked"
			value={ attributes.content }
		/>;
	},
} );

// Block for content that is only available for people with no key
registerBlockType( 'unlock/locked-box', {
	title: 'Locked Box - Visible to non members only',
	icon: 'lock',
	category: 'common',
	attributes: {
		content: { type: 'string' },
	},

	edit: ( { setAttributes, attributes } ) => {
		const onChangeContent = ( value ) => {
			setAttributes( { content: value } );
		};

		return <RichText
			placeholder="Add your content which will only be visible by users who do not have a key yet!"
			value={ attributes.content }
			onChange={ onChangeContent }
			className="lock__unlocked-box" />;
	},

	save: ( { attributes } ) => {
		return <RichText.Content
			tagName="p"
			className="unlock-protocol__locked"
			value={ attributes.content }
		/>;
	},
} );

// Block for content that is only visible while the status of the lock is pending (loading)
// We just extend the coreButton functionality when it's loaded
registerBlockType( 'unlock/checkout-button', {
	title: 'Checkout Button - Visible to non members only',
	icon: 'lock',
	category: 'common',
	attributes: {
		anchor: { type: 'string' },
	},

	edit: ( { setAttributes, attributes } ) => {
		const onChangeContent = ( value ) => {
			setAttributes( { anchor: value } );
		};

		return <div className="wp-block-button">
			<RichText
				placeholder="Become a member now!"
				value={ attributes.anchor }
				onChange={ onChangeContent }
				className="wp-block-button__link" />
		</div>;
	},

	save: ( { attributes } ) => {
		return <div className="wp-block-button unlock-protocol__locked">
			<RichText.Content
				tagName="a"
				onClick="window.unlockProtocol && window.unlockProtocol.loadCheckoutModal()"
				value={ attributes.anchor }
				className="wp-block-button__link"
			/>
		</div>;
	},
} );
