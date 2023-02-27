import { __ } from "@wordpress/i18n";
import {
  Button,
  TextControl,
  Notice,
  ColorPalette,
  ColorIndicator,
  ToggleControl,
} from "@wordpress/components";
import { useState, useEffect } from "react";
import apiFetch from "@wordpress/api-fetch";
import MediaUpload from "./utils/media-upload";

import "../../scss/admin/style.scss";

function General() {
  const [isSubmitted, setIsSubmitted] = useState(false);
  const [isChanged, setIsChanged] = useState(false);
  const [generalSettings, setGeneralSettings] = useState({});
  const [noticeType, setNoticeType] = useState("");
  const [notice, setNotice] = useState("");

  useEffect(() => {
    apiFetch({
      path: "/unlock-protocol/v1/settings",
    })
      .then((resp) => {
        if (resp?.general) {
          setGeneralSettings(resp.general);
        }
      })
      .catch((err) => { });
  }, []);

  /**
   * Save Settings
   */
  const saveGeneralSettings = () => {
    setIsSubmitted(true);

    let data = {
      section: "general",
      settings: generalSettings,
    };

    apiFetch({
      path: "/unlock-protocol/v1/settings",
      method: "POST",
      data: data,
    })
      .then((resp) => {
        setIsSubmitted(false);

        if (resp?.general) {
          setGeneralSettings(resp.general);
        }

        setNoticeType("success");
        setNotice(__("Updated Successfully!", "unlock-protocol"));
        setIsChanged(false);
      })
      .catch((err) => {
        setIsSubmitted(false);
        setNoticeType("error");
        setNotice(__(err.message, "unlock-protocol"));
        setIsChanged(false);
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

  /**
   * Update state on change value.
   *
   * @param {*} key
   * @param {*} value
   *
   * @returns void
   */
  const onChangeValue = (key, value) => {
    setGeneralSettings({
      ...generalSettings,
      [key]: value,
    });

    setIsChanged(true);
  };

  return (
    <>
      <div className="settings_container__general">

        {renderNotice()}


        <div className="input-container">
          <h2>{__("Login Button", "unlock-protocol")}</h2>

          <div className="form-inputs">
            <div className="group">
              <TextControl
                label={__("Login button text", "unlock-protocol")}
                value={generalSettings?.login_button_text}
                onChange={(value) => onChangeValue("login_button_text", value)}
              />
            </div>

            <div className="group">
              <p className="components-base-control__label">
                {__("Login button type", "unlock-protocol")}
              </p>

              <ToggleControl
                label={__(
                  "Enable image background for Login button",
                  "unlock-protocol"
                )}
                checked={generalSettings?.login_blurred_image_button ?? false}
                onChange={() =>
                  onChangeValue(
                    "login_blurred_image_button",
                    !generalSettings?.login_blurred_image_button
                  )
                }
              />
            </div>

            {/* description option for blurred image button */}
            {generalSettings?.login_blurred_image_button ?? false ? (
              <>
                <div className="group">
                  <TextControl
                    label={__("Call to action text", "unlock-protocol")}
                    value={generalSettings?.login_button_description}
                    onChange={(value) =>
                      onChangeValue("login_button_description", value)
                    }
                  />
                </div>

                <MediaUpload
                  label={__("Upload login background image", "unlock-protocol")}
                  value={generalSettings?.login_bg_image ?? ""}
                  handle={(data) => {
                    onChangeValue("login_bg_image", data.url ?? "");
                  }}
                />
              </>
            ) : (
              ""
            )}

            <div className="group">
              <p className="components-base-control__label">
                {__("Login button background color", "unlock-protocol")}
              </p>

              <div className="color-picker-container">
                <ColorIndicator
                  colorValue={generalSettings?.login_button_bg_color ?? "#000"}
                />

                <ColorPalette
                  colors={[]}
                  value={generalSettings?.login_button_bg_color ?? "#000"}
                  onChange={(color) =>
                    onChangeValue("login_button_bg_color", color)
                  }
                />
              </div>
            </div>

            <div className="group">
              <p className="components-base-control__label">
                {__("Login button text color", "unlock-protocol")}
              </p>

              <div className="color-picker-container">
                <ColorIndicator
                  colorValue={
                    generalSettings?.login_button_text_color ?? "#fff"
                  }
                />

                <ColorPalette
                  colors={[]}
                  value={generalSettings?.login_button_text_color ?? "#fff"}
                  onChange={(color) =>
                    onChangeValue("login_button_text_color", color)
                  }
                />
              </div>
            </div>
          </div>
        </div>


        <div className="input-container">
          <h2>{__("Checkout Button", "unlock-protocol")}</h2>
          <div className="form-inputs">

            {/* checkout options */}

            <div className="group">
              <TextControl
                label={__("Checkout button text", "unlock-protocol")}
                className={"checkout-button-text-input"}
                value={generalSettings?.checkout_button_text}
                onChange={(value) =>
                  onChangeValue("checkout_button_text", value)
                }
              />
            </div>

            <div className="group">
              <p className="components-base-control__label">
                {__("Checkout button type", "unlock-protocol")}
              </p>

              <ToggleControl
                label={__(
                  "Enable image background for Checkout button",
                  "unlock-protocol"
                )}
                checked={
                  generalSettings?.checkout_blurred_image_button ?? false
                }
                onChange={() =>
                  onChangeValue(
                    "checkout_blurred_image_button",
                    !generalSettings?.checkout_blurred_image_button
                  )
                }
              />
            </div>

            {/* description option for blurred image button */}
            {generalSettings?.checkout_blurred_image_button ?? false ? (
              <>
                <div className="group">
                  <TextControl
                    label={__("Call to action text", "unlock-protocol")}
                    className={"checkout-button-text-input"}
                    value={generalSettings?.checkout_button_description}
                    onChange={(value) =>
                      onChangeValue("checkout_button_description", value)
                    }
                  />
                </div>

                <MediaUpload
                  label={__(
                    "Upload checkout background image",
                    "unlock-protocol"
                  )}
                  value={generalSettings?.checkout_bg_image ?? ""}
                  handle={(data) => {
                    onChangeValue("checkout_bg_image", data.url ?? "");
                  }}
                />
              </>
            ) : (
              ""
            )}

            <div className="group">
              <p className="components-base-control__label">
                {__("Checkout button background color", "unlock-protocol")}
              </p>

              <div className="color-picker-container">
                <ColorIndicator
                  colorValue={
                    generalSettings?.checkout_button_bg_color ?? "#000"
                  }
                />

                <ColorPalette
                  colors={[]}
                  value={generalSettings?.checkout_button_bg_color ?? "#000"}
                  onChange={(color) =>
                    onChangeValue("checkout_button_bg_color", color)
                  }
                />
              </div>
            </div>

            <div className="group">
              <p className="components-base-control__label">
                {__("Checkout button text color", "unlock-protocol")}
              </p>

              <div className="color-picker-container">
                <ColorIndicator
                  colorValue={
                    generalSettings?.checkout_button_text_color ?? "#fff"
                  }
                />

                <ColorPalette
                  colors={[]}
                  value={generalSettings?.checkout_button_text_color ?? "#fff"}
                  onChange={(color) =>
                    onChangeValue("checkout_button_text_color", color)
                  }
                />
              </div>
            </div>
          </div>
        </div>

        <div className="input-container">
          <h2>{__("Checkout URL", "unlock-protocol")}</h2>

          <div className="form-inputs">
            <TextControl
              label={__("Custom Paywall Config", "unlock-protocol")}
              value={generalSettings?.custom_paywall_config}
              onChange={(value) => onChangeValue("custom_paywall_config", value)}
              help="See `Configuring Checkout` in the Unlock Protocol docs."
            />
          </div>
        </div>


        <div className="input-container">
          <h2>{__("Advanced", "unlock-protocol")}</h2>

          <div className="form-inputs">
            <TextControl
              label={__("Checkout URL base", "unlock-protocol")}
              value={generalSettings?.checkout_url_base}
              onChange={(value) => onChangeValue("checkout_url_base", value)}
              help="Default: https://app.unlock-protocol.com/checkout"
            />
          </div>

          <div className="form-inputs">
            <TextControl
              label={__("Locksmith URL base", "unlock-protocol")}
              value={generalSettings?.locksmith_url_base}
              onChange={(value) => onChangeValue("locksmith_url_base", value)}
              help="Default: https://locksmith.unlock-protocol.com/api/oauth"
            />
          </div>
        </div>

        <Button
          type="submit"
          isPrimary={true}
          onClick={() => saveGeneralSettings()}
          isBusy={isSubmitted}
          disabled={isSubmitted || !isChanged}
        >
          {isSubmitted
            ? __("Saving", "unlock-protocol")
            : __("Save", "unlock-protocol")}
        </Button>
      </div>
    </>
  );
}

export default General;
