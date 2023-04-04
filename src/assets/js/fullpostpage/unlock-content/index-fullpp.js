import { __ } from "@wordpress/i18n";
import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { withSelect, withDispatch } from '@wordpress/data';
import { compose } from '@wordpress/compose';
import EditFullPP from "./edit-fullpp";

// FullPostPageLock is a higher-order component that wraps around EditFullPP
// It maps the state and dispatch functions to the EditFullPP component props
const FullPostPageLock = compose([
  // withSelect maps the state to the component props
  withSelect((select) => {
    const { getEditedPostAttribute } = select('core/editor');
    return {
      locks: getEditedPostAttribute('unlockp_fullpp_locks') || [],
      ethereumNetworks: getEditedPostAttribute('unlockp_fullpp_ethereumnetworks') || [],
    };
  }),
  // withDispatch maps the dispatch functions to the component props
  withDispatch((dispatch) => {
    const { editPost } = dispatch('core/editor');
    return {
      onUpdateLocks: (locks) => editPost({ unlockp_fullpp_locks: locks }),
      onUpdateEthereumNetworks: (ethereumNetworks) => editPost({ unlockp_fullpp_ethereumnetworks: ethereumNetworks }),
    };
  }),
])(EditFullPP);

// FullPostPageLockWrapper wraps the FullPostPageLock component inside the PluginDocumentSettingPanel
// This creates a panel in the Gutenberg editor's document settings sidebar
const FullPostPageLockWrapper = () => (
  <PluginDocumentSettingPanel
    name="full-post-page-lock"
    title={__('Unlock Protocol', 'unlock-protocol')}
  >
    <p>
      {__(
        "Add lock(s) to restrict access to the full post/page content inside of WordPress.",
        "unlock-protocol"
      )}
    </p>
    <FullPostPageLock />
  </PluginDocumentSettingPanel>
);

// Registers the FullPostPageLockWrapper as a plugin in the Gutenberg editor
// This makes the PluginDocumentSettingPanel with the FullPostPageLock component appear in the editor options sidebar panel
registerPlugin('unlock-protocol-unlock-box-fullpp', {
  render: FullPostPageLockWrapper,
});
