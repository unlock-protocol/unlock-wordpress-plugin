import {
  TextControl,
  SelectControl,
  Button,
} from "@wordpress/components";
import { useState, useEffect } from 'react'
import { __ } from "@wordpress/i18n";
import { getEthereumNetworksFromSettings, formatAddress } from "./admin/utils";
import "../scss/admin/editor.scss";


export const AdminLocks = ({ onSaveNewLock, removeLock, locks }) => {
  const [showForm, setShowForm] = useState(false);

  /** Hides the form and call props */
  const onSave = async (lock) => {
    await onSaveNewLock(lock)
    setShowForm(false);
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
      {showForm && <AddLockForm handleSave={onSave} />}
    </div>
  );
}


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

/**
 * Component to show a lock
 * @param {*} param0 
 * @returns 
 */
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
      onClick={onRemove}
    >
      remove
    </Button>
  </li>

}
