import { __ } from "@wordpress/i18n";
import { useCallback } from "react";
import { useSelect, useDispatch } from '@wordpress/data';
import { AdminLocks } from "../../admin-locks";

/**
 * 
 */
const EditFullPostPage = (props) => {

  // Loads locks and postId
  const { locks, postId } = useSelect((select) => {
    const { getCurrentPostId, getEditedPostAttribute } = select("core/editor");
    const postMeta = getEditedPostAttribute('meta');
    let savedPosts = []
    if (postMeta.unlock_protocol_post_locks) {
      savedPosts = JSON.parse(postMeta.unlock_protocol_post_locks)
    }
    return {
      postId: getCurrentPostId(),
      locks: savedPosts
    };
  }, []);

  // Create function to save locks
  const { editPost } = useDispatch('core/editor', [locks]);
  const saveLocks = useCallback((locks) => {
    return editPost({
      meta: {
        'unlock_protocol_post_locks': JSON.stringify(locks)
      }
    })
  }, [editPost])

  // Remove a lock and save the lock attributes
  const removeLock = async (index) => {
    const newLocks = [...locks];
    newLocks.splice(index, 1);
    await saveLocks(newLocks);
  };

  // Save a new lock and hide form
  const onSaveNewLock = async (lock) => {
    await saveLocks([...locks, lock]);
  }

  if (!postId) {
    // Loading
    return null
  }

  return <AdminLocks onSaveNewLock={onSaveNewLock} removeLock={removeLock} locks={locks} />
};

export default EditFullPostPage;
