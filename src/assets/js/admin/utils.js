import apiFetch from "@wordpress/api-fetch";
import { __ } from "@wordpress/i18n";

/**
 * 
 * @returns 
 */
export const getEthereumNetworksFromSettings = () => {
  return apiFetch({
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
          label: item.network_name.charAt(0).toUpperCase()
            + item.network_name.slice(1),
          value: item.network_id,
        });
      });
      return selectOptions
    })
    .catch((err) => {
      console.error(err)
      return []
    });
};

export const formatAddress = (fullStr, strLen = 18, separator = '...') => {
  if (fullStr.length <= strLen) return fullStr;

  separator = separator || '...';

  var sepLen = separator.length,
    charsToShow = strLen - sepLen,
    frontChars = Math.ceil(charsToShow / 2),
    backChars = Math.floor(charsToShow / 2);

  return fullStr.substr(0, frontChars) +
    separator +
    fullStr.substr(fullStr.length - backChars);
}
