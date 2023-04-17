import { __ } from "@wordpress/i18n";
import {
  PanelBody,
  PanelRow,
  TextControl,
  SelectControl,
  Button,
} from "@wordpress/components";
import { useEffect } from "react";
import {
  InspectorControls,
  useBlockProps,
  InnerBlocks,
} from "@wordpress/block-editor";
import { getBlockTypes } from "@wordpress/blocks";
import "../../../scss/admin/editor.scss";
import { getEthereumNetworksFromSettings } from "../../admin/utils";
import { AdminLocks } from "../../admin-locks";

/**
 * Helper function to check if locks are all set and valid
 * @param {*} locks
 * @returns
 */
const locksValid = (locks) => {
  let valid = true;
  if (locks.length === 0) {
    valid = false;
  }
  locks.forEach((lock) => {
    if (lock.network === -1 || !lockValid(lock)) {
      valid = false;
    }
  });
  return valid;
};

/**
 * Helper function to check if a single lock is valid
 * @param {*} lock
 * @returns
 */
const lockValid = (lock) => {
  if (lock.network === -1) {
    return true;
  }
  if (!lock.address) {
    return false;
  }
  let regexp = "^0x[a-fA-F0-9]{40}$";
  let result = new RegExp(regexp, "g").test(lock.address);
  if (!result) {
    return false;
  }
  return true;
};

export default function Edit({ attributes, setAttributes }) {
  const { locks } = attributes;
  // Preventing the own block.
  const ALLOWED_BLOCKS = getBlockTypes()
    .map((block) => block.name)
    .filter((blockName) => blockName !== "unlock-protocol/unlock-box");


  const showInnerBlock = () => {
    wp.data.dispatch("core/editor").unlockPostSaving("my-lock");

    return <InnerBlocks allowedBlocks={ALLOWED_BLOCKS} />;
  };

  const lockWarning = () => {
    wp.data.dispatch("core/editor").lockPostSaving("my-lock");

    return (
      <div className="no-lock-address">
        <p>
          {__("Please configure the lock(s) on this block.", "unlock-protocol")}
        </p>
      </div>
    );
  };

  const addLock = (lock) => {
    if (!locks) {
      setAttributes({ locks: [lock] });
    } else {
      setAttributes({ locks: [...locks, lock] });
    }
  };

  const removeLock = (id) => {
    const newLocks = [...locks];
    newLocks.splice(id, 1); // Remove 1 item
    setAttributes({ locks: newLocks });
  };

  return (
    <div {...useBlockProps()}>
      <InspectorControls>
        <PanelBody title={__("Locks", "unlock-protocol")}>
          <AdminLocks onSaveNewLock={addLock} removeLock={removeLock} locks={locks} />
        </PanelBody>
      </InspectorControls>

      <div className="unlock-header-icon"></div>

      {locksValid(locks) ? showInnerBlock() : lockWarning()}
    </div>
  );
}
