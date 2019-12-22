const { registerBlockType } = window.wp.blocks
const { RichText, InnerBlocks, InspectorControls } = wp.editor
const { PanelBody, SelectControl } = wp.components

// Block for content that is only available to owners of keys!
registerBlockType("unlock/unlock-box", {
  title: "Unlock Protocol Block",
  icon: "lock",
  category: "common",
  attributes: {
    unlockState: {
      type: "string",
      default: "locked"
    }
  },

  edit: ({ attributes, setAttributes, className }) => {
    return [
      <InspectorControls>
        <PanelBody title={"Content Settings"}>
          <SelectControl
            label={"Hide this if user:"}
            value={attributes.unlockState}
            options={[
              { label: "Is not a member", value: "locked" },
              { label: "Is a member", value: "unlocked" }
            ]}
            onChange={unlockState => {
              setAttributes({ unlockState })
            }}
          />
        </PanelBody>
      </InspectorControls>,
      <div
        className={[
          className,
          `unlock-protocol__${attributes.unlockState}`
        ].join(" ")}
      >
        <InnerBlocks />
      </div>
    ]
  },

  save: ({ attributes }) => {
    return (
      <div className={`unlock-protocol__${attributes.unlockState}`}>
        <InnerBlocks.Content />
      </div>
    )
  }
})

// Block for content that is only visible while the status of the lock is pending (loading)
// We just extend the coreButton functionality when it's loaded
registerBlockType("unlock/checkout-button", {
  title: "Checkout Button - Visible to non members only",
  icon: "lock",
  category: "common",
  attributes: {
    anchor: { type: "string" }
  },

  edit: ({ setAttributes, attributes }) => {
    const onChangeContent = value => {
      setAttributes({ anchor: value })
    }

    return (
      <div className="wp-block-button">
        <RichText
          placeholder="Become a member now!"
          value={attributes.anchor}
          onChange={onChangeContent}
          className="wp-block-button__link"
        />
      </div>
    )
  },

  save: ({ attributes }) => {
    return (
      <div className="wp-block-button">
        <RichText.Content
          tagName="a"
          onClick="window.unlockProtocol && window.unlockProtocol.loadCheckoutModal()"
          value={attributes.anchor}
          className="wp-block-button__link"
        />
      </div>
    )
  }
})
