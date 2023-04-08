import { __ } from "@wordpress/i18n";
import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { withSelect, withDispatch } from '@wordpress/data';
import { compose } from '@wordpress/compose';
import EditFullPostPage from "./edit-full-post-page";

// FullPostPageLock is a higher-order component that wraps around EditFullPostPage
// It maps the state and dispatch functions to the EditFullPostPage component props
const FullPostPageLock = compose([
  // withSelect maps the state to the component props
  withSelect((select) => {
    const { getEditedPostAttribute } = select('core/editor');
    return {
      locks: getEditedPostAttribute('unlockp_full_post_page_locks') || [],
      ethereumNetworks: getEditedPostAttribute('unlockp_full_post_page_ethereumnetworks') || [],
    };
  }),
  // withDispatch maps the dispatch functions to the component props
  withDispatch((dispatch) => {
    const { editPost } = dispatch('core/editor');
    return {
      onUpdateLocks: (locks) => editPost({ unlockp_full_post_page_locks: locks }),
      onUpdateEthereumNetworks: (ethereumNetworks) => editPost({ unlockp_full_post_page_ethereumnetworks: ethereumNetworks }),
    };
  }),
])(EditFullPostPage);

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
    {/* Documentation links */}
    <div className="docs">
      <a
        rel="noopener noreferrer"
        target="_blank"
        href={unlockProtocol.unlock_docs.docs}
      >
        {__("Unlock's documentation", "unlock-protocol")}
      </a>

      <br />

      <a
        rel="noopener noreferrer"
        target="_blank"
        href={unlockProtocol.unlock_docs.deploy_lock}
      >
        {__("Deploy a lock", "unlock-protocol")}
      </a>
    </div>
    <FullPostPageLock />
  </PluginDocumentSettingPanel>
);

// Registers the FullPostPageLockWrapper as a plugin in the Gutenberg editor
// This makes the PluginDocumentSettingPanel with the FullPostPageLock component appear in the editor options sidebar panel
registerPlugin('unlock-protocol-unlock-box-full-post-page', {
  render: FullPostPageLockWrapper,
});
