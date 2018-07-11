=== Paid Memberships Pro - BuddyPress Add On ===
Contributors: strangerstudios, great-h-master
Tags: paid memberships pro, pmpro, buddypress
Requires at least: 4
Tested up to: 4.9.7
Stable tag: 1.1

Manage access to your BuddyPress Community using Paid Memberships Pro

== Description ==

= Manage access to your BuddyPress Community using Paid Memberships Pro. =

This BuddyPress Integration for Paid Memberships Pro allows you to restrict access to specific features of your BuddyPress community by membership level, allowing you to build a custom, private, and flexible members-only community.

The plugin requires BuddyPress and Paid Memberships Pro to be installed and activated.

= General features of this Integration include: =
1. Restrict BuddyPress Features by Membership Level
1. Assign or Invite Members to BuddyPress Groups by Membership Level
1. Assign BuddyPress Member Types by Membership Level
1. Show the Member's Level Name on BuddyPress Profile
1. Use the BuddyPress Registration Process in place of Paid Memberships Pro

= Specific areas of BuddyPress that can be restricted by Membership Level include: =
1. Group Creation: Can members of this level create BuddyPress Groups?
1. Single Group Viewing: Can members of this level view individual BuddyPress Groups?
1. Groups Page Viewing: Can members of this level view the BuddyPress Groups page?
1. Joining Groups: Can members of this level join BuddyPress Groups?
1. Public Messaging: Can members of this level send public messages to other members?
1. Private Messaging: Can members of this level send private messages to other members?
1. Send Friend Requests: Can members of this level send friend requests to other members?
1. Include in Member Directory: Should members of this level be included in the BuddyPress member directory?

== Installation ==

= Prerequisites =
1. You must have Paid Memberships Pro and BuddyPress installed and activated on your site.

= Download, Install and Activate! =
1. Download the latest version of the plugin.
1. Unzip the downloaded file to your computer.
1. Upload the /pmpro-buddypress/ directory to the /wp-content/plugins/ directory of your site.
1. Activate the plugin through the 'Plugins' menu in WordPress.

= Page Settings =
This plugin redirects users to a specific page if they try to access restricted BuddyPress features. The user is redirected to the page assigned as the "Access Restricted" page under Memberships > Page Settings.

1. Navigate to Memberships -> Page Settings and choose (or generate) a page for the "Access Restricted" page.
1. Be sure to include the [pmpro_buddypress_restricted] shortcode in the page content.
1. Save Settings

= Membership Level Settings =
1. Navigate to Memberships -> Membership Levels and choose a level to edit.
1. Under the "BuddyPress Restrictions" section, change "Unlock BuddyPress?" to your desired option.
1. You can choose to give members of that level access to all of BuddyPress (and thus lock all users without that level from accessing BuddyPress features) or choose specific features that require that membership level.
1. If you are planning to restrict BuddyPress for more than one level, the plugin will check if the user has ANY level giving them access to those features.

= Managing Members and Groups in BuddyPress =
1. Navigate to Memberships -> Membership Levels.
1. Select a level to edit or create a new level.
1. Under "BuddyPress Group Membership", select the groups you would like to add or invite members to.
1. Users will be automatically added to any group checked in the "Add to these Groups" option.
1. Users will be invited (can manually choose to join) any group checked in the "Invite to these Groups" option.
1. Save Settings.

= Creating and Assigning Member Types by Membership Level =
1. Navigate to Memberships > Membership Levels.
1. Select a level to edit or create a new level.
1. Under "BuddyPress Member Types", select the member types you would like to assign for members of this level.
1. Save Settings.

Note that BuddyPress Member Types must be created through custom code. [This post explains how to add Member Types to BuddyPress](https://www.paidmembershipspro.com/apply-a-buddypress-member-type-on-membership-checkout-or-level-change/).

= Non-member User Settings =
Some of your WordPress users may not have a membership level in Paid Memberships Pro. This plugin allows you to set how BuddyPress should be locked down for these users without a membership level.

1. Navigate to Memberships -> PMPro BuddyPress in the WP Dashboard.
1. Under the "Non-member User Settings" section, change "Unlock BuddyPress?" to your desired option.
1. You can choose to lock access to all of BuddyPress, give non-member users access to all of BuddyPress or choose specific features that they can access.

= Use BuddyPress Registration Process =
This plugin also allows you to use the BuddyPress user registration process (in place of the standard Paid Memberships Pro membership checkout process).

1. Navigate to Memberships -> PMPro BuddyPress in the WP Dashboard.
1. Change the "Registration Page" setting to "Use BuddyPress Registration Page".
1. Save Settings.

= Show the Member's Level Name on BuddyPress Profile =
1. Navigate to Memberships -> PMPro BuddyPress in the WP Dashboard.
1. Select "Yes" under "Show Membership Level on BuddyPress Profile?"
1. Save Settings.

== Screenshots ==

1. **PMPro BuddyPress Settings** - Explore and manage the features of the integration plugin, including Page Settings, Membership Level Settings, and General Settings.
2. **Membership Level Settings** - Define how you want to restrict BuddyPress for each  Membership Level under Memberships > Edit Level.
3. **Group and Member Type Settings** - Add or Invite Members to Groups and Assign Member Types by Membership Level under Memberships > Edit Level.

== Changelog ==

= 1.1 =
* BUG FIX: Fixed fatal errors when PMPro or BuddyPress isn't activated.
* BUG FIX: Fixed bug where membership level changes were creatinig fatal errors if Groups was not active.
* BUG FIX: Fixed bug where the members directory was not being filtered correctly.
* BUG FIX: Fixed bug where logged in users were being redirected to the BuddyPress registration page even though logged in users can't register.
* ENHANCEMENT: Doublechecking pmpro_hasMembershipLevel so plugins like PMPro Approvals can still filter which levels are included when calculating restrictions.

= 1.0 =
* Initial WP.org release.
