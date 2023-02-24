import { __ } from "@wordpress/i18n";
import { Button, TextControl, Notice } from "@wordpress/components";
import { useState, useEffect } from "react";
import apiFetch from "@wordpress/api-fetch";
import Swal from "sweetalert2";

import "../../scss/admin/style.scss";

function Networks() {
  const [isSubmitted, setIsSubmitted] = useState(false);
  const [isDeleting, setIsDeleting] = useState(false);
  const [networks, setNetworks] = useState([]);
  const [networkId, setNetworkId] = useState("");
  const [networkName, setNetworkName] = useState("");
  const [networkRpcEndpoint, setNetworkRpcEndpoint] = useState("");
  const [noticeType, setNoticeType] = useState("");
  const [notice, setNotice] = useState("");

  useEffect(() => {
    apiFetch({
      path: "/unlock-protocol/v1/settings",
    })
      .then((resp) => {
        if (resp?.networks) {
          setNetworks(resp.networks);
        }
      })
      .catch((err) => { });
  }, []);

  /**
   * Save Settings
   */
  const saveNetwork = () => {
    setIsSubmitted(true);

    let data = {
      section: "networks",
      network_id: networkId,
      network_name: networkName,
      network_rpc_endpoint: networkRpcEndpoint,
    };

    apiFetch({
      path: "/unlock-protocol/v1/settings",
      method: "POST",
      data: data,
    })
      .then((resp) => {
        setIsSubmitted(false);

        if (resp?.networks) {
          setNetworks(resp.networks);
        }

        setNoticeType("success");
        setNotice(__("Added Successfully!", "unlock-protocol"));

        setNetworkId("");
        setNetworkName("");
        setNetworkRpcEndpoint("");
      })
      .catch((err) => {
        setIsSubmitted(false);
        setNoticeType("error");
        setNotice(__(err.message, "unlock-protocol"));
      });
  };

  /**
   * Remvoe a network.
   */
  const removeNetwork = (index) => {
    setIsDeleting(true);

    let data = {
      network_index: index,
    };

    Swal.fire({
      title: __("Are you sure?", "unlock-protocol"),
      text: __("You won't be able to revert this!", "unlock-protocol"),
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: __("Yes, delete it!", "unlock-protocol"),
    }).then((result) => {
      if (result.isConfirmed) {
        apiFetch({
          path: "/unlock-protocol/v1/settings/delete",
          method: "POST",
          data: data,
        })
          .then((resp) => {
            setIsDeleting(false);

            if (resp?.networks) {
              setNetworks(resp.networks);
            }

            Swal.fire(
              __("Deleted!", "unlock-protocol"),
              __("Network has been deleted successfully!", "unlock-protocol"),
              "success"
            );
          })
          .catch((err) => {
            setIsDeleting(false);
            setNoticeType("error");
            setNotice(__(err.message, "unlock-protocol"));
          });
      } else {
        setIsDeleting(false);
      }
    });
  };

  const renderNotice = () => {
    if ("" === notice) {
      return;
    }

    return (
      <Notice status={noticeType} onRemove={() => setNotice("")}>
        {notice}
      </Notice>
    );
  };

  return (
    <>
      <div className="settings_container__networks">
        {renderNotice()}

        <div className="input-container">
          <h3>
            {__("Add a new network", "unlock-protocol")}

            <a
              href={unlockProtocol.network_help_url}
              className="tooltip"
              target="_blank"
            >
              {" "}
              ?
              <span className="tooltiptext">
                {unlockProtocol.network_help_text}
              </span>
            </a>
          </h3>

          <div className="form-inputs">
            <div className="group">
              <TextControl
                label={__("Network name", "unlock-protocol")}
                className={"network-name-input"}
                value={networkName}
                onChange={(value) => setNetworkName(value)}
              />
            </div>

            <div className="group">
              <TextControl
                label={__("Network ID", "unlock-protocol")}
                className={"network-id-input"}
                value={networkId}
                type="number"
                onChange={(value) => setNetworkId(value)}
              />
            </div>

            <div className="group">
              <TextControl
                label={__("Network RPC endpoint", "unlock-protocol")}
                className={"network-rpc-input"}
                value={networkRpcEndpoint}
                type="url"
                onChange={(value) => setNetworkRpcEndpoint(value)}
              />
            </div>
          </div>

          <Button
            type="submit"
            isPrimary={true}
            onClick={() => saveNetwork()}
            isBusy={isSubmitted}
            disabled={isSubmitted}
          >
            {isSubmitted
              ? __("Adding...", "unlock-protocol")
              : __("Save", "unlock-protocol")}
          </Button>
        </div>

        <div className="all_networks_container">
          {Object.keys(networks).map((index) => {
            return (
              <div className="single_network" key={index}>
                <p>
                  <span>{__("Network ID", "unlock-protocol")}</span> :{" "}
                  {networks[index]?.network_id}
                </p>
                <p>
                  <span>{__("Network Name", "unlock-protocol")}</span> :{" "}
                  {networks[index]?.network_name}
                </p>
                <p>
                  <span>{__("Network RPC Endpoint", "unlock-protocol")}</span> :{" "}
                  {networks[index]?.network_rpc_endpoint}
                </p>

                <Button
                  variant="tertiary"
                  className="remove-network"
                  showTooltip={true}
                  label={__("Delete", "unlock-protocol")}
                  onClick={() => removeNetwork(index)}
                  disabled={isDeleting}
                >
                  X
                </Button>
              </div>
            );
          })}
        </div>
      </div>
    </>
  );
}

export default Networks;
