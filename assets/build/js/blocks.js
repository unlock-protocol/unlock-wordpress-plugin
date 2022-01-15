!function(){"use strict";var e={n:function(t){var o=t&&t.__esModule?function(){return t.default}:function(){return t};return e.d(o,{a:o}),o},d:function(t,o){for(var n in o)e.o(o,n)&&!e.o(t,n)&&Object.defineProperty(t,n,{enumerable:!0,get:o[n]})},o:function(e,t){return Object.prototype.hasOwnProperty.call(e,t)}},t=window.wp.i18n,o=window.wp.blocks,n=window.wp.blockEditor,r=window.wp.components,c=window.React,l=window.wp.apiFetch,a=e.n(l);function u(e){return function(e){if(Array.isArray(e))return s(e)}(e)||function(e){if("undefined"!=typeof Symbol&&null!=e[Symbol.iterator]||null!=e["@@iterator"])return Array.from(e)}(e)||i(e)||function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function i(e,t){if(e){if("string"==typeof e)return s(e,t);var o=Object.prototype.toString.call(e).slice(8,-1);return"Object"===o&&e.constructor&&(o=e.constructor.name),"Map"===o||"Set"===o?Array.from(e):"Arguments"===o||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(o)?s(e,t):void 0}}function s(e,t){(null==t||t>e.length)&&(t=e.length);for(var o=0,n=new Array(t);o<t;o++)n[o]=e[o];return n}var d=function(e){return-1===e.network||!!e.address&&!!new RegExp("^0x[a-fA-F0-9]{40}$","g").test(e.address)};(0,o.registerBlockType)("unlock-protocol/unlock-box",{title:(0,t.__)("Unlock Protocol","unlock-protocol"),category:"common",icon:"lock",description:(0,t.__)("A block to add lock(s) to the content inside of WordPress.","unlock-protocol"),attributes:{locks:{type:"array",default:[]},ethereumNetworks:{type:"array",default:[]}},supports:{align:!0},edit:function(e){var l=e.attributes,s=e.setAttributes,k=l.locks,f=l.ethereumNetworks,p=(0,o.getBlockTypes)().map((function(e){return e.name})).filter((function(e){return"unlock-protocol/unlock-box"!==e}));(0,c.useEffect)((function(){a()({path:"/unlock-protocol/v1/settings"}).then((function(e){var o=e.networks,n=[{label:(0,t.__)("None","unlock-protocol"),value:-1}];Object.entries(o).forEach((function(e){var t,o,r=(o=2,function(e){if(Array.isArray(e))return e}(t=e)||function(e,t){var o=null==e?null:"undefined"!=typeof Symbol&&e[Symbol.iterator]||e["@@iterator"];if(null!=o){var n,r,c=[],_n=!0,l=!1;try{for(o=o.call(e);!(_n=(n=o.next()).done)&&(c.push(n.value),!t||c.length!==t);_n=!0);}catch(e){l=!0,r=e}finally{try{_n||null==o.return||o.return()}finally{if(l)throw r}}return c}}(t,o)||i(t,o)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()),c=(r[0],r[1]);n.push({label:c.network_name,value:c.network_id})})),s({ethereumNetworks:n})})).catch((function(e){}))}),[]);var m=function(e,t,o){k[e][t]=o,s({locks:u(k)})};return React.createElement(React.Fragment,null,React.createElement("div",(0,n.useBlockProps)(),React.createElement(n.InspectorControls,null,React.createElement(r.PanelBody,{title:(0,t.__)("Locks","unlock-protocol")},k.map((function(e,o){return React.createElement("div",{class:"setting-lock"},React.createElement(r.SelectControl,{label:(0,t.__)("Network","unlock-protocol"),value:e.network,options:f,onChange:function(e){return m(o,"network",parseInt(e))}}),-1!==e.network?React.createElement(React.Fragment,null,React.createElement("p",{className:"block-label"},(0,t.__)("Lock Address","unlock-protocol")),React.createElement(r.TextControl,{value:e.address,onChange:function(e){return m(o,"address",e)}})):"",!d(e)&&React.createElement("p",{className:"lock-warning"},(0,t.__)("Lock address is not valid","unlock-protocol")),React.createElement(r.Button,{isSmall:!0,isDestructive:!0,onClick:function(){!function(e){var t=u(k);t.splice(e,1),s({locks:t})}(o)}},"Remove"))})),React.createElement(r.PanelRow,null,React.createElement(r.Button,{className:"add-lock",variant:"primary",onClick:function(){s(k?{locks:[].concat(u(k),[{address:"",network:-1}])}:{locks:[{address:"",network:-1}]})}},"Add Lock")),React.createElement("div",{className:"docs"},React.createElement("a",{rel:"noopener noreferrer",target:"_blank",href:unlockProtocol.unlock_docs.docs},(0,t.__)("Unlock's documentation","unlock-protocol")),React.createElement("br",null),React.createElement("a",{rel:"noopener noreferrer",target:"_blank",href:unlockProtocol.unlock_docs.deploy_lock},(0,t.__)("Deploy a lock","unlock-protocol"))))),React.createElement("div",{className:"unlock-header-icon"}),function(e){var t=!0;return 0===e.length&&(t=!1),e.forEach((function(e){-1!==e.network&&d(e)||(t=!1)})),t}(k)?(wp.data.dispatch("core/editor").unlockPostSaving("my-lock"),React.createElement(n.InnerBlocks,{allowedBlocks:p})):(wp.data.dispatch("core/editor").lockPostSaving("my-lock"),React.createElement("div",{className:"no-lock-address"},React.createElement("p",null,(0,t.__)("Please configure the lock(s) on this block.","unlock-protocol"))))))},save:function(){return React.createElement(n.InnerBlocks.Content,null)}})}();