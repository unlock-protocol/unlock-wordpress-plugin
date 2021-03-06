const { registerBlockType } = window.wp.blocks;
const { RichText, InnerBlocks, BlockControls } = wp.editor;

// Block for content that is only available to owners of keys!
registerBlockType( 'unlock/unlock-box', {
	title: 'Unlock Protocol Block',
	icon: 'lock',
	category: 'common',
	attributes: {
		unlockState: {
			type: 'string',
			default: 'locked',
		},
	},

	edit: ( { attributes, setAttributes, className } ) => {
		const unlockConfig = wp.data
			.select( 'core/editor' )
			.getEditedPostAttribute( 'meta' )._unlock_protocol_config;

		return (
			<div
				className={ [
					className,
					`unlock-protocol__${ attributes.unlockState }`,
				].join( ' ' ) }
			>
				<BlockControls>
					<div className="filter components-toolbar">
						{ unlockConfig && (
							<>
								<span>Only visible by&nbsp;</span>
								<select
									onBlur={ ( event ) =>
										setAttributes( {
											unlockState: event.target.value,
										} )
									}
								>
									<option
										value="locked"
										selected={
											attributes.unlockState === 'locked'
										}
									>
										Members
									</option>
									<option
										value="unlocked"
										selected={
											attributes.unlockState ===
											'unlocked'
										}
									>
										Non Members
									</option>
								</select>
							</>
						) }
						{ ! unlockConfig && (
							<span className="warning">
								Please set Unlock config for document
							</span>
						) }
					</div>
				</BlockControls>
				<InnerBlocks />
			</div>
		);
	},

	save: ( { attributes } ) => {
		return (
			<div className={ `unlock-protocol__${ attributes.unlockState }` }>
				<InnerBlocks.Content />
			</div>
		);
	},
} );

// Block for content that is only visible while the status of the lock is pending (loading)
// We just extend the coreButton functionality when it's loaded
registerBlockType( 'unlock/checkout-button', {
	title: 'Checkout Button',
	icon: 'lock',
	category: 'common',
	attributes: {
		anchor: { type: 'string' },
	},

	edit: ( { setAttributes, attributes } ) => {
		const onChangeContent = ( value ) => {
			setAttributes( { anchor: value } );
		};

		return (
			<div className="wp-block-button">
				<RichText
					placeholder="Become a member now!"
					value={ attributes.anchor }
					onChange={ onChangeContent }
					className="wp-block-button__link"
				/>
			</div>
		);
	},

	save: ( { attributes } ) => {
		return (
			<div className="wp-block-button">
				<RichText.Content
					tagName="a"
					onClick="window.unlockProtocol && window.unlockProtocol.loadCheckoutModal()"
					value={ attributes.anchor }
					className="wp-block-button__link"
				/>
			</div>
		);
	},
} );
