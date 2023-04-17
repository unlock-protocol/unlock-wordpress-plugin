import { __ } from "@wordpress/i18n";
import {
  TextControl,
  SelectControl,
  Button,
} from "@wordpress/components";
import { useEffect, useState, useCallback, useMemo } from "react";
import apiFetch from "@wordpress/api-fetch";
import { useSelect, useDispatch } from '@wordpress/data';
import "../../../scss/admin/editor.scss";
import { getEthereumNetworksFromSettings, formatAddress } from "../../admin/utils";

const EditFullPostPage = (props) => {
  const [showForm, setShowForm] = useState(false); //show/hide add lock form

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
    setShowForm(false);
  }

  if (!postId) {
    // Loading
    return null
  }

  return (
    <div>
      <ul>
        {locks.map((lock, id) => {
          return (
            <Lock lock={lock} onRemove={() => {
              removeLock(id);
            }} />
          );
        })}
      </ul>
      <Button className="components-button is-link" onClick={() => setShowForm(!showForm)}>
        Add Lock
      </Button>

      {showForm && <AddLockForm handleSave={onSaveNewLock} />}

    </div>
  );
};

/**
 * Simple Form to add a lock
 * @param {*} param0 
 * @returns 
 */
const AddLockForm = ({ handleSave }) => {
  const [network, setNetwork] = useState()
  const [address, setAddress] = useState()
  const [error, setError] = useState('')
  const [ethereumNetworks, setEthereumNetworks] = useState([])

  useEffect(() => {
    getEthereumNetworksFromSettings().then((ethereumNetworks) => {
      setEthereumNetworks(ethereumNetworks);
    })
  }, []);


  // Check if the Ethereum address is valid
  const isAddressValid = (address) => {
    return /^0x[a-fA-F0-9]{40}$/.test(address);
  };

  const onSave = (e) => {
    e.preventDefault()
    handleSave({ network, address })
    return false
  }

  if (!ethereumNetworks) {
    return null
  }

  return <form onSubmit={onSave}>
    <div className="add-lock-form">
      <SelectControl
        label={__("Network", "unlock-protocol")}
        options={ethereumNetworks}
        value={network}
        onChange={(value) =>
          setNetwork(parseInt(value))
        }
      />
      <p className="label">
        {__("Lock Address", "unlock-protocol")}
      </p>
      <TextControl
        disabled={!network}
        value={address}
        onChange={(value) => {
          if (!isAddressValid(value)) {
            setError(__("Invalid Lock address", "unlock-protocol"));
          } else {
            setError("");
          }
          setAddress(value)
        }}
      />
      {error && <p className="lock-warning">{error}</p>}
      <Button
        disabled={!network || !isAddressValid(address)}
        className="add-lock"
        type="submit"
      >
        {__("Add New Lock", "unlock-protocol")}


      </Button>
    </div>
  </form>

}

const Lock = ({ lock, onRemove }) => {
  const [lockDescription, setLockDescription] = useState()

  useEffect(() => {
    getEthereumNetworksFromSettings().then((ethereumNetworks) => {
      ethereumNetworks.forEach(({ value, label }) => {
        if (value === lock.network) {
          setLockDescription(`${label}: ${formatAddress(lock.address)}`)
        }
      })
    })
  }, [lock.network]);

  return <li style={{
    display: "flex",
    'justify-content': "space-between"
  }}>
    <span>{lockDescription}</span>

    <Button
      isSmall
      isDestructive
      onClick={() => {
        removeLock(id);
      }}
    >
      remove
    </Button>
  </li>

}

export default EditFullPostPage;
