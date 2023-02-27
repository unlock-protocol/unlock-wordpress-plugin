const fs = require("fs");
const path = require("path");
const minimist = require("minimist");
const shell = require("shelljs");

function resolve(...paths) {
  return path.resolve(__dirname, ...paths);
}

const DEST = resolve("../dist/unlock-wordpress-plugin");
const packageInfo = JSON.parse(fs.readFileSync("package.json"));
const args = minimist(process.argv.slice(2));

let version = packageInfo.version;

const semverRegex =
  /^((([0-9]+)\.([0-9]+)\.([0-9]+)(?:-([0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?)(?:\+([0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?)$/;

if (args.version && args.version.match(semverRegex)) {
  const currentVersion = version;
  version = args.version;

  console.log("Updating plugin version number");

  shell.exec(
    `sed -i '' 's/"version": "${currentVersion}"/"version": "${version}"/g' package.json`
  );
  shell.exec(
    `sed -i '' 's/* UNLOCK_PLUGIN_VERSION: ${currentVersion}/* UNLOCK_PLUGIN_VERSION: ${version}/g' unlock-protocol.php`
  );
  shell.exec(
    `find includes -iname '*.php' -exec sed -i "" "s/UP_SINCE/${version}/g" {} \\\;`
  );
  shell.exec(`npm install`);
}

const zip = `unlock-wordpress-plugin-${version}.zip`;

shell.rm("-rf", DEST);
shell.rm("-f", resolve("unlock-wordpress-plugin-*.zip"));
shell.mkdir("-p", DEST + "/assets/build");


const include = [
  "assets/build/css",
  "assets/build/images",
  "assets/build/js",
  "inc",
  "languages",
  "templates",
  "unlock-protocol.php",
];

console.log("Copying files...");

include.forEach((item) => {
  shell.cp("-r", resolve("../unlock-wordpress-plugin/", item), resolve(DEST, item));
});

shell.cp("-r", './README.txt', resolve(DEST, 'README.txt'));

// UPDATE DEV EDITION
shell.sed('-i', 'DEV EDITION', '', resolve(DEST, 'unlock-protocol.php'));

console.log("Making zip...");
shell.exec(
  `cd ${resolve("../dist")} && zip ${zip} unlock-wordpress-plugin -rq`
);

shell.rm("-rf", resolve(DEST));
console.log("Done.");
