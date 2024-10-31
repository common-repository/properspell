=== Plugin Name ===
Contributors: c4mden
Tags: spell check, spelling, spellcheck, search, sugestion
Requires at least: ?
Tested up to: 3.0.4
Stable tag: trunk
License: GPLv2

Provides 'Did you mean' spell check suggestions for your WordPress site.

== Description ==

This plugin allows you to offer 'Did you mean' spelling suggestions for queries on your site. By inserting a single line of code into your theme file, your site can have the same powerful spell checking employed by the major search engines.

ProperSpell's unparalleled spell checking service is based on the actual usage of words and phrases, as opposed to a dictionary, so it can suggest common spellings for proper nouns and popular terms. It can also determine the context of a search term in order to provide intelligent search alternatives.

In order to use this service, you must first obtain API keys from [www.properspell.com](http://www.properspell.com/).

== Installation ==

1. Upload the `properspell` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Sign up for your ProperSpell API keys at [www.properspell.com](http://www.properspell.com/).
1. Enter your ProperSpell API keys under Settings -> ProperSpell
1. Place the following code immediately before the loop begins in your search.php theme file `<?php if (function_exists('get_properspell_suggestion')) { echo get_properspell_suggestion(); } ?>`

== Frequently Asked Questions ==

= How much does the ProperSpell service cost? =

Usage of the ProperSpell API costs US $1 for every 1,000 queries, with a minimum charge of US $5 per month.

= What languages does the ProperSpell API offer suggestions for? =

The API currently only supports queries in the English language.  Please refer to the [ProperSpell documentation](http://www.properspell.com/documentation/) for further information about the API.