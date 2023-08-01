=== Unlock Protocol ===
Contributors: julien51
Tags: paywall, ethereum, monetization, unlock, membership, subscription, member
Requires at least: 5.1
Tested up to: 5.9
Requires PHP: 7.0
Stable tag: 4.0.2
License: GPLv3
License URI: https://github.com/unlock-protocol/unlock-wordpress-plugin/blob/master/LICENSE

This plugin lets authors add locks to their posts and pages so that only paying visitors can view their content.

== Description ==

[Unlock](https://unlock-protocol.com/) is a protocol for memberships that lets any creator monetize their content permissionless.

In order to become members, visitors may need to be using a web3-enabled wallet such as [MetaMask](https://metamask.io/) or a web browser with an embedded wallet, such as [Opera](https://www.opera.com/crypto). They also need a balance of crypto-currency to purchase the memberships.

The plugin enables "full-post" locking, or just block-level level locking, using the Gutemberg Editor.

The plugin uses Unlock's default checkout UI, which means it lets users who do not have wallet create an Unlock account or even pay using credit card, if your lock supports this.

== Getting Started ==

If you'd like to add a lock to your site, start by [deploying your first lock]https://app.unlock-protocol.com/locks/create). We _strongly_ recommend starting with a test network such as Goerli.

While the lock is deploying, you should [download this plugin](https://wordpress.org/plugins/unlock-protocol/) from the WordPress site. Then, from the administration of your WordPress.org website, in the `Plugins` section, click on the `Add New button and then on the `Upload Plugin` button to upload the plugin.

Note: To allow new users to log in using their Ethereum wallet with a single click, make sure that __Settings > General > Anyone can register__ (for single sites) or __Network Settings > Allow new registrations > User accounts__ may be registered (for multisites) is enabled. Your existing users can link their wallets to their WordPress accounts regardless of this setting.

== Writing locked stories ==

You can either choose to lock the whole post, using the post-level settings, or to lock specific Blocks in the UI with the Gutenberg Editor, for posts and pages.

To add the Unlock Protocol block inside the page/post please follow the below steps:

1. Click on Post -> Add New
2. To add a block you just need to click on “+” in the editor.
3. Enter Unlock Protocol in the search.
4. Unlock Protocol block will be shown. Click on the block, the block gets added.
5. You will be able to see block settings on the right side. From block settings, add at least one lock, select the right network and, enter its address.

Once your page or post includes the content you need, you can preview its content like any other WordPress post or page. We recommend the use of a web3 wallet which supports multiple accounts so that you can easily toggle between accounts which are already members and accounts which are not members yet!

== Contributing ==

This plugin is, like all of the Unlock code, [open source](https://github.com/unlock-protocol/unlock-wordpress-plugin). You're encouraged to fork it and make any changes you'd like! If you believe these changes would be beneficial to others, we encourage you to also open a Pull Request so that we could add these to the main branch released on the wordpress.org website!

= Local Development =

To setup local development environment for the plugin using we recommend using [Localwp](https://localwp.com/). To get started, simply clone the repo from [GitHub](https://github.com/unlock-protocol/unlock-wordpress-plugin).


The repo includes a `unlock-wordpress-plugin` which has the required `.php` files and in which asset files (javascript, CSS and images) are added at build time.

You can package the whole plugin by using `yarn run release` and install it in your local WordPress instance. Alternatively, you can add a symbolic link in the local WordPress's `wp-content/plugins` folder that points to the `unlock-wordpress-plugin`.

= Publishing the plugin =

In order to update the plugin, check out the current subversion state with

    svn co  https://plugins.svn.wordpress.org/unlock-protocol

Then, from the GitHub repo clone, inside the `assets` folder, write

    yarn run release

This will generate a zip file in dist directory, unzip that file and copy the content to trunk directory in svn repo and commit the changes into svn with

    svn ci -m "commit message"

Then, tag the new version (replace X and Y!) to release it:

    svn cp trunk tags/X.Y
    svn ci -m "tagging version X.Y"

Additionally, please make sure you tag the version on Github:

    git tag -a X.Y -m "tagging version X.Y"
    git push origin X.Y

And finally, create the release on Github'si UI


== Frequently Asked Questions ==

To be completed with questions!

== Changelog ==

= 4.0.2 =

* handling postMeta undefined

= 4.0.1 =

* Version bump to reflect changes in README

= 4.0.0 =

* Re-architected plugin
* Adding support for fully locked posts

= 3.2.3 =

* Updated RPC endpoints

= 3.2.2 =

* Fixing auth endpoint

= 3.2.1 =

* Changed default RPC endpoint for gnosis chain
* Support for PHP7.0
* Checkout URL customization
* Endpoint customization

= 3.2.0 =

* Switching RPC check to use getHasValidKey so that it can grant access based on the hasValidKeyHook

= 3.1.0 =
* Adding support for blocks with multiple locks

= 3.0.0 =
* Brand new plugin with updated UI
* User can login to the WordPress site by using Unlock Protocol account.
* Pre-filled networks
* Setting to add/delete networks.
* Setting to customize the login and checkout button.
* User is linked with the Unlock Protocol site.
* Now the login and purchase is validated from the Unlock Protocol site and content is displaying after the user has the correct access on that specific content.
* Added new Hooks.

= 2.1 =
* Uses the latest Unlock paywall script https://unlock-protocol.com/blog/introducing-latest-paywall/

= 2.0 =
* each post/page can have a different configuration
* block setting is now visible inside of the Editor

= 1.6 =
* Button is not only visible when content is locked

= 1.5 =
* Cleanup

= 1.1 =
* Supporting nested blocks.

= 1.0 =
* Initial version

== Upgrade Notice ==

This new version is not compatible with the old version of the plugin, please backup your content before upgrade.
