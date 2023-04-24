import { __ } from "@wordpress/i18n";
import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import EditFullPostPage from "./edit";


// FullPostPageLockWrapper wraps the FullPostPageLock component inside the PluginDocumentSettingPanel
// This creates a panel in the Gutenberg editor's document settings sidebar
const FullPostPageLockWrapper = () => (
  <PluginDocumentSettingPanel
    name="full-post-page-lock"
    title={__('Unlock Protocol', 'unlock-protocol')}
  >
    <p>
      {__(
        "Add lock(s) to restrict access to the full post content inside of WordPress.",
        "unlock-protocol"
      )} <a
        rel="noopener noreferrer"
        target="_blank"
        href={unlockProtocol.unlock_docs.docs}
      >
        {__("Documentation", "unlock-protocol")}
      </a>
    </p>
    <EditFullPostPage />
  </PluginDocumentSettingPanel>
);

// Registers the FullPostPageLockWrapper as a plugin in the Gutenberg editor
// This makes the PluginDocumentSettingPanel with the FullPostPageLock component appear in the editor options sidebar panel
registerPlugin('unlock-protocol-unlock-box-full-post-page', {
  render: FullPostPageLockWrapper,
});
