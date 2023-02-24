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
import apiFetch from "@wordpress/api-fetch";
import "../../../scss/admin/editor.scss";

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
  const { locks, ethereumNetworks } = attributes;

  // Preventing the own block.
  const ALLOWED_BLOCKS = getBlockTypes()
    .map((block) => block.name)
    .filter((blockName) => blockName !== "unlock-protocol/unlock-box");

  useEffect(() => {
    apiFetch({
      path: "/unlock-protocol/v1/settings",
    })
      .then((resp) => {
        let networks = resp.networks;
        let selectOptions = [
          {
            label: __("None", "unlock-protocol"),
            value: -1,
          },
        ];

        Object.entries(networks).forEach(([key, item]) => {
          selectOptions.push({
            label: item.network_name,
            value: item.network_id,
          });
        });

        setAttributes({ ethereumNetworks: selectOptions });
      })
      .catch((err) => { });
  }, []);

  /**
   * Set values in state attribute.
   * @param {*} key
   * @param {*} value
   */
  const onChangeLockValue = (id, key, value) => {
    locks[id][key] = value;
    setAttributes({ locks: [...locks] });
  };

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

  const addLock = () => {
    if (!locks) {
      setAttributes({ locks: [{ address: "", network: -1 }] });
    } else {
      setAttributes({ locks: [...locks, { address: "", network: -1 }] });
    }
  };

  const removeLock = (id) => {
    const newLocks = [...locks];
    newLocks.splice(id, 1); // Remove 1 item
    setAttributes({ locks: newLocks });
  };

  return (
    <>
      <div {...useBlockProps()}>
        <InspectorControls>
          <PanelBody title={__("Locks", "unlock-protocol")}>
            {locks.map((lock, id) => {
              return (
                <div class="setting-lock">
                  <SelectControl
                    label={__("Network", "unlock-protocol")}
                    value={lock.network}
                    options={ethereumNetworks}
                    onChange={(value) =>
                      onChangeLockValue(id, "network", parseInt(value))
                    }
                  />

                  {-1 !== lock.network ? (
                    <>
                      <p className="block-label">
                        {__("Lock Address", "unlock-protocol")}
                      </p>
                      <TextControl
                        value={lock.address}
                        onChange={(value) =>
                          onChangeLockValue(id, "address", value)
                        }
                      />
                    </>
                  ) : (
                    ""
                  )}

                  {!lockValid(lock) && (
                    <p className="lock-warning">
                      {__("Lock address is not valid", "unlock-protocol")}
                    </p>
                  )}

                  <Button
                    isSmall
                    isDestructive
                    onClick={() => {
                      removeLock(id);
                    }}
                  >
                    Remove
                  </Button>
                </div>
              );
            })}
            <PanelRow>
              <Button className="add-lock" variant="primary" onClick={addLock}>
                Add Lock
              </Button>
            </PanelRow>
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
          </PanelBody>
        </InspectorControls>

        <div className="unlock-header-icon"></div>

        {locksValid(locks) ? showInnerBlock() : lockWarning()}
      </div>
    </>
  );
}
