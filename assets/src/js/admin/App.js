import { __ } from "@wordpress/i18n";
import { useState, useEffect } from "react";
import Networks from "./Networks";
import General from "./General";
import "../../scss/admin/style.scss";

function App() {
  const [currentTab, setCurrentTab] = useState("general");

  return (
    <>
      <div className="wrap">
        <h2 className="unlock-settings-heading">
          {__("Unlock Protocol", "unlock-protocol")}
        </h2>

        <div className="settings_container">
          <div className="left-menu">
            <ul>
              <li
                className={"general" === currentTab ? "active" : ""}
                onClick={() => setCurrentTab("general")}
              >
                {__("General", "unlock-protocol")}
              </li>

              <li
                className={"networks" === currentTab ? "active" : ""}
                onClick={() => setCurrentTab("networks")}
              >
                {__("Networks", "unlock-protocol")}
              </li>
            </ul>
          </div>

          <div className="right-content">
            {"networks" === currentTab ? <Networks /> : ""}
            {"general" === currentTab ? <General /> : ""}
          </div>
        </div>
      </div>
    </>
  );
}

export default App;
