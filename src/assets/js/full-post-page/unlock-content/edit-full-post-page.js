import { __ } from "@wordpress/i18n";
import {
  PanelBody,
  PanelRow,
  TextControl,
  SelectControl,
  Button,
  Notice,
} from "@wordpress/components";
import { useEffect, useState } from "react";
import apiFetch from "@wordpress/api-fetch";
import { useSelect } from '@wordpress/data';
import "../../../scss/admin/editor.scss";

const EditFullPostPage = ({ locks, ethereumNetworks, onUpdateLocks, onUpdateEthereumNetworks }) => {
  
  const [saveMessage, setSaveMessage] = useState(null);
  const [refresh, setRefresh] = useState(false); //refresh lock(s) meta panel to keep track of locks already added


  //get current post id
  const getpost = useSelect((select) => {
    const { getCurrentPostId } = select("core/editor");
    const id = getCurrentPostId();
  
    return {
      id,
    };
  }, []);

  //check if current post is already 
  const { isSavingPost, didPostSaveRequestSucceed } = useSelect((select) => {
    const { isSavingPost, didPostSaveRequestSucceed } = select("core/editor");
    return {
      isSavingPost: isSavingPost(),
      didPostSaveRequestSucceed: didPostSaveRequestSucceed(),
    };
  }, []);


  //fetch locks attributes
  const fetchLockSettings = async (post_id) => {
    try {
      const response = await apiFetch({
        path: `/unlock-protocol/v1/get_unlockp_full_post_page_attributes?post_id=${post_id}`,
      });

      if (response && response.locks) {
        onUpdateLocks(response.locks);
      }
    } catch (error) {
      console.error("Error fetching lock settings:", error);
    }
  };

  //useEffect 1: that fetches the networks
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

        onUpdateEthereumNetworks(selectOptions);

        // Fetch lock settings from the post meta after networks are fetched
        fetchLockSettings(getpost.id);
      })
      .catch((err) => { });
  }, [getpost.id, refresh]); // refresh the useEffect anytime either of this changes


  //useEffect 2: called when post/page publish/update button clicked
  useEffect(() => {
    if (!isSavingPost && didPostSaveRequestSucceed) {

      // refresh the useEffect 1 to reload meta panel in post/page editor sidebar when publish/update button is clicked
      setRefresh(!refresh); 
    }
  }, [isSavingPost, didPostSaveRequestSucceed, getpost.id]);


  // Check if the Ethereum address is valid
  const isAddressValid = (address) => {
    return /^0x[a-fA-F0-9]{40}$/.test(address);
  };

  // Save the attributes object to the post meta
  const saveAttributes = async (locks, post_id) => {

    // Filter the locks to only include those with a valid address and network
    const validLocks = locks.filter(
      (lock) => lock.network !== -1 && lock.address && isAddressValid(lock.address)
    );

    if (validLocks.length === 0) {
      setSaveMessage("No valid lock(s) attributes to save.");
      return;
    }

    const attributes = {
      locks: validLocks,
      ethereumNetworks: ethereumNetworks,
    };

    try {
      const response = await apiFetch({
        path: "/unlock-protocol/v1/save_unlockp_full_post_page_attributes",
        method: "POST",
        data: {
          post_id: post_id,
          unlockp_full_post_page_attributes: JSON.stringify(attributes),
        },
      });
      

      if (response.success) { 
        setSaveMessage("Lock attributes saved successfully");
        setRefresh(!refresh); // refresh the useEffect 1 to reload meta panel in post/page editor sidebar when publish/update button is clicked
      } else {
        const errorData = response.data; // Directly access the `data` property in the response object
        setSaveMessage(
          `Failed to save Lock attributes. Error: ${
            errorData.message || "Unknown error"
          }`
        );
      }
    } catch (error) {
      setSaveMessage(`Catch Error: ${error.message || "Failed to save Lock attributes"}`);
    }
  };

  // Update the lock value and save the lock attributes
  const onChangeLockValue = (id, key, value) => {
    locks[id][key] = value;
    onUpdateLocks([...locks]);
  };

  // Add a new lock and save the lock attributes
  const addLock = () => {
    let updatedLocks;
    if (!locks) {
      updatedLocks = [{ address: "", network: -1 }];
    } else {
      updatedLocks = [...locks, { address: "", network: -1 }];
    }
    onUpdateLocks(updatedLocks);
  };

  
  // Delete a lock from the database
  const deleteLock = async (lock, lockIndex) => {
    try {
      const response = await apiFetch({
        path: `/unlock-protocol/v1/delete_unlockp_full_post_page_attributes`,
        method: "POST",
        data: {
          post_id: getpost.id,
          lock_index: lockIndex,
        },
      });
      return response;
    } catch (error) {
      console.error("Error deleting lock attributes:", error);
      return { success: false, message: error.message };
    }
  };

  // Remove a lock and save the lock attributes
  const removeLock = async (id) => {
    const lockToDelete = locks[id];
  
    try {
      const response = await deleteLock(lockToDelete, id);
      if (response && response.success) {
        const newLocks = [...locks];
        newLocks.splice(id, 1);
        onUpdateLocks(newLocks);
        setRefresh(!refresh); // refresh the useEffect 1 to reload meta panel in post/page editor sidebar when publish/update button is clicked
        setSaveMessage("Lock deleted successfully");
      } else {
        // If the lock is not found in saved attributes object, remove it from the UI
        const newLocks = [...locks];
        newLocks.splice(id, 1);
        onUpdateLocks(newLocks);
        setSaveMessage("Empty lock removed");
      }
    } catch (error) {
      console.error("Error deleting lock:", error);
      setSaveMessage(`Failed to delete lock. Error: ${error.message}`);
    }
  };  

  return (
    <div>
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
              {/* remove lock button */}
              <Button
                isSmall
                isDestructive
                onClick={() => {
                  removeLock(id);
                }}
              >
                Remove
              </Button>
              {/* save lock button */}
              <Button
                className="save-lock"
                variant="primary"
                style={{ backgroundColor: "green", marginLeft: "5px" }}
                onClick={() => saveAttributes(locks, getpost.id, id)}
              >
                Save
              </Button>
            </div>
          );
        })}
        {saveMessage && (
          <Notice
            status={
              saveMessage === "Lock attributes saved successfully"
                ? "success"
                : "error"
            }
            onRemove={() => setSaveMessage(null)}
          >
            {saveMessage}
          </Notice>
        )}
        <PanelRow>
          {/* add lock(s) button */}
          <Button className="add-lock" variant="primary" onClick={addLock}>
            Add Lock
          </Button>
        </PanelRow>
      </PanelBody>
    </div>
  );    
};

export default EditFullPostPage;
