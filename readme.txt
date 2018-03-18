=== Paid Memberships Pro - BuddyPress Add On ===
Contributors: strangerstudios
Tags: paid memberships pro, pmpro, buddypress
Requires at least: 3.5
Tested up to: 4.7.1
Stable tag: 1.0

Integrate BuddyPress with Paid Memberships Pro.

== Description ==

Requires bbPress and Paid Memberships Pro installed and activated.

== Installation ==

= Prerequisites =
1. You must have Paid Memberships Pro and BuddyPress installed and activated on your site.

= Download, Install and Activate! =
1. Download the latest version of the plugin.
1. Unzip the downloaded file to your computer.
1. Upload the /pmpro-buddypress/ directory to the /wp-content/plugins/ directory of your site.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Go to Memberships -> Page Settings and choose (or generate) a page for the "Access Restricted" page. This is the page users are redirected to if they try to use a BuddyPress feature they don't have access to.

= Lock Down BuddyPress =
1. Go to Memberships -> Membership Levels and choose a level to edit.
1. Under the "BuddyPress Restrictions" section, change "Unlock BuddyPress?" to one of the "Yes" options.
1. You can choose to give members of that level access to all of BuddyPress (and thus lock all users without that level form accessing BuddyPress features) or choose specific features that would require that membership level.
1. If you set restrictions on several levels, the rules will check if a user has ANY level giving them access to those features.

= Add Members to BuddyPress Groups =
1. Go to Memberships -> Membership Levels and choose a level to edit.
1. Go to the "BuddyPress Group Membership" section.
1. Users will be automatically added to any group checked in the "Add to These Groups" option.
1. Users will be invited (and then can manually choose to join) any group checked in the "Invite to These Groups" option.

= BuddyPress Member Types =
1. TBD

= To Use the BuddyPress Register Page Instead of the PMPro Levels Page =
1. Go to Memberships -> PMPro BuddyPress in the WP Dashboard.
1. Change the "Registration Page" setting to "Use BuddyPress Registration Page".

= Shortcode for Member's Activity =
1. TBD

= Other Settings =
1. Go to Memberships -> PMPro BuddyPress in the WP Dashboard.

== Screenshots ==

== Changelog ==

= 1.0 =
* Initial WP.org release.
