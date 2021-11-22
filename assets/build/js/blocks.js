!function(){"use strict";var e={n:function(t){var o=t&&t.__esModule?function(){return t.default}:function(){return t};return e.d(o,{a:o}),o},d:function(t,o){for(var n in o)e.o(o,n)&&!e.o(t,n)&&Object.defineProperty(t,n,{enumerable:!0,get:o[n]})},o:function(e,t){return Object.prototype.hasOwnProperty.call(e,t)}},t=window.wp.i18n,o=window.wp.blocks,n=window.wp.blockEditor,r=window.wp.components,c=window.React,l=window.wp.apiFetch,a=e.n(l);function u(e,t){(null==t||t>e.length)&&(t=e.length);for(var o=0,n=new Array(t);o<t;o++)n[o]=e[o];return n}(0,o.registerBlockType)("unlock-protocol/unlock-box",{title:(0,t.__)("Unlock Protocol","unlock-protocol"),category:"common",icon:"lock",description:(0,t.__)("A block to add lock(s) to the content inside of WordPress.","unlock-protocol"),attributes:{lockAddress:{type:"string",default:""},ethereumNetworks:{type:"array",default:[]},ethereumNetwork:{type:"integer",default:-1}},supports:{align:!0},edit:function(e){var l=e.attributes,i=e.setAttributes,s=l.lockAddress,d=l.ethereumNetwork,p=l.ethereumNetworks,f=(0,o.getBlockTypes)().map((function(e){return e.name})).filter((function(e){return"unlock-protocol/unlock-box"!==e}));(0,c.useEffect)((function(){a()({path:"/unlock-protocol/v1/settings"}).then((function(e){var o=e.networks,n=[{label:(0,t.__)("None","unlock-protocol"),value:-1}];Object.entries(o).forEach((function(e){var t,o,r=(o=2,function(e){if(Array.isArray(e))return e}(t=e)||function(e,t){var o=null==e?null:"undefined"!=typeof Symbol&&e[Symbol.iterator]||e["@@iterator"];if(null!=o){var n,r,c=[],_n=!0,l=!1;try{for(o=o.call(e);!(_n=(n=o.next()).done)&&(c.push(n.value),!t||c.length!==t);_n=!0);}catch(e){l=!0,r=e}finally{try{_n||null==o.return||o.return()}finally{if(l)throw r}}return c}}(t,o)||function(e,t){if(e){if("string"==typeof e)return u(e,t);var o=Object.prototype.toString.call(e).slice(8,-1);return"Object"===o&&e.constructor&&(o=e.constructor.name),"Map"===o||"Set"===o?Array.from(e):"Arguments"===o||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(o)?u(e,t):void 0}}(t,o)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()),c=r[0],l=r[1];n.push({label:l.network_name,value:c})})),i({ethereumNetworks:n})})).catch((function(e){}))}),[]);var k=function(e,t){i(function(e,t,o){return t in e?Object.defineProperty(e,t,{value:o,enumerable:!0,configurable:!0,writable:!0}):e[t]=o,e}({},e,t))};return React.createElement(React.Fragment,null,React.createElement("div",(0,n.useBlockProps)(),React.createElement(n.InspectorControls,null,React.createElement(r.PanelBody,{title:(0,t.__)("Settings","unlock-protocol")},React.createElement(r.SelectControl,{label:(0,t.__)("Ethereum Network","unlock-protocol"),value:d,options:p,onChange:function(e){return k("ethereumNetwork",parseInt(e))}}),-1!==d?React.createElement(React.Fragment,null,React.createElement("p",null,React.createElement("strong",null,(0,t.__)("Lock Address","unlock-protocol"))),React.createElement(r.TextControl,{value:s,onChange:function(e){return k("lockAddress",e)}})):"",React.createElement("a",{rel:"noopener noreferrer",target:"_blank",href:unlockProtocol.unlock_docs},(0,t.__)("Unlock's documentation","unlock-protocol")))),React.createElement("div",{className:"unlock-header-icon"}),-1===d||-1!==d&&""!==s?(wp.data.dispatch("core/editor").unlockPostSaving("my-lock"),React.createElement(n.InnerBlocks,{allowedBlocks:f})):(wp.data.dispatch("core/editor").lockPostSaving("my-lock"),React.createElement("div",{className:"no-lock-address"},React.createElement("p",null,(0,t.__)("Please add lock address","unlock-protocol"))))))},save:function(){return React.createElement(n.InnerBlocks.Content,null)}})}();