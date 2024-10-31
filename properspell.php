<?php

/*
    Plugin Name: ProperSpell
    Plugin URI: http://www.properspell.com
    Description: Provides 'Did you mean' spell check suggestions for queries on your WordPress site
    Version: 1.0
    Author: Camden Daily
    Author URI: http://www.properspell.com
    License: GPLv2
*/

/*  Copyright 2010 - 2011 Jaunter LLC (email: camden at jaunter dot com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if (!is_object($properspell)) {

  // instantiate our class exactly once
  $properspell = new properspell();

  // add our hooks
  add_action('admin_init', array(&$properspell, 'admin_init'));
  add_action('admin_menu', array(&$properspell, 'admin_menu'));
  register_activation_hook(__FILE__, array('properspell', 'add_defaults'));
}

/*
 *  wrapper function for getting a suggestion
 */

function get_properspell_suggestion() {

  global $properspell;
  return $properspell->get_spelling_suggestion();
}

// class definition

class properspell {

  // the location we will query for spelling suggestions
  var $endpoint = "http://properspell.com/api/rest/";

 /*
  *  sets default plugin settings upon activation (if none are already defined)
  */

  function add_defaults() {

    $options = get_option('properspell_settings');
    if(!is_array($options)) {

      $options = array("pre_html" => "<p><span style=\"color:#cc0000\">".__("Did you mean:")."</span>", "post_html" => "</p><br/>");
      update_option('properspell_settings', $options); }
  }

 /*
  *  register plugin settings
  */

  function admin_init() {

    register_setting('properspell_settings', 'properspell_settings', array(&$this, 'settings_validate'));

    add_settings_section('properspell_api', __('API Settings'), array(&$this, 'setting_text_api'), __FILE__);
    add_settings_field('appid', __('Application ID'), array(&$this, 'setting_input_appid'), __FILE__, 'properspell_api');
    add_settings_field('secret', __('Secret Key'), array(&$this, 'setting_input_secret'), __FILE__, 'properspell_api');

    add_settings_section('properspell_display', __('Display Settings'), array(&$this, 'setting_text_display'), __FILE__);
    add_settings_field('pre_html', __('Pre Link HTML'), array(&$this, 'setting_input_pre_html'), __FILE__, 'properspell_display');
    add_settings_field('post_html', __('Post Link HTML'), array(&$this, 'setting_input_post_html'), __FILE__, 'properspell_display');
  }

 /*
  *  creates an admin menu for configuring this plugin
  */

  function admin_menu() {

      add_options_page(__('ProperSpell'), __('ProperSpell'), 'manage_options', 'properspell', array(&$this, 'settings_page'));
  }

  /*
   *  returns HTML for displaying a spelling suggestion if one is found and if searching for it would return results
   */

  function get_spelling_suggestion() {

    // get the current search term and see if we have a spelling suggestion
    $query = html_entity_decode(get_search_query());
    $result = $this->spell_check($query);

    if (!$result) {

      // no suggestion found
      return false; }

    else {

      // there is a spelling suggestion, so let's do a new query with the suggestion to see if any results are returned
      global $wp_query;
      $vars = $wp_query->query;
      $vars['s'] = $result['suggestion'];
      $query = new WP_Query($vars);

      if ($query->found_posts > 0) {

        // only display a suggestion if we found results that match it

        // grab array of query arguments
        $args = array();
        $query = explode("&", $_SERVER['QUERY_STRING']);
        foreach($query as $q) {
          list($key, $value) = explode("=", $q);
          $args[$key] = $value; }

        // construct our suggestion HTML
        $options = get_option('properspell_settings');
        $args['s'] = $result['suggestion'];
        $url = get_option('home') . "?" .  http_build_query($args);
        $html  = "<div id='properspellSuggestion'>";
        $html .= $options['pre_html'];
        $html .= " <a href='{$url}'>" . strip_tags($result['html'], "<span>") . "</a>";
        $html .= $options['post_html'];
        $html .= "</div>";

        return $html; }

      else {

        // searching this site for the suggestion returned no results
        return false; } }
  }

 /*
  *  creates the settings page
  */

  function settings_page() {

  ?>
    <div class="wrap">
      <div class="icon32" id="icon-options-general"><br></div>
      <h2><?php _e('ProperSpell') ?></h2>
      <form action="options.php" method="post">
        <?php settings_fields('properspell_settings'); ?>
        <?php do_settings_sections(__FILE__); ?>
        <p><input name="submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" /></p>
      </form>
    </div>
  <?php

  }

 /*
  *  functions used to generate the settings page form
  */

  function setting_input_appid() {

    $options = get_option('properspell_settings');
    echo "<input id='appid' name='properspell_settings[appid]' size='30' type='text' value='".esc_attr($options['appid'])."' />";
  }

  function setting_input_secret() {

    $options = get_option('properspell_settings');
    echo "<input id='secret' name='properspell_settings[secret]' size='30' type='text' value='".esc_attr($options['secret'])."' />";
  }

  function setting_input_pre_html() {

    $options = get_option('properspell_settings');
    echo "<input id='pre_html' name='properspell_settings[pre_html]' size='60' type='text' value='".esc_attr($options['pre_html'])."' />";
  }

  function setting_input_post_html() {

    $options = get_option('properspell_settings');
    echo "<input id='post_html' name='properspell_settings[post_html]' size='60' type='text' value='".esc_attr($options['post_html'])."' />";
  }

  function setting_text_api() {

    echo __("API keys can be optained from ") . "<a href='http://www.properspell.com/' target='_blank'>www.properspell.com</a>";
  }

  function setting_text_display() {

    _e("This is the HTML code displayed immediately before and after the suggestion link.");
  }

 /*
  *  validate any plugin settings before saving them to the database
  */

  function settings_validate($input) {

    $validated['appid'] = trim($input['appid']);
    $validated['secret'] = trim($input['secret']);
    $validated['pre_html'] = trim($input['pre_html']);
    $validated['post_html'] = trim($input['post_html']);

    return $validated;
  }

 /*
  *  retrieve a spell check suggestion for the given query
  */

  function spell_check($query) {

    if (strlen($query) == 0) {

      // fail if no query passed
      return false; }

    // grab our options
    $options = get_option('properspell_settings');
    $appid = $options['appid'];
    $secret = $options['secret'];

    if (strlen($appid) == 0 OR strlen($secret) == 0) {

      // fail if API keys are not set
      return false; }

    // calculate the signature for this request
    $signature = md5($appid . $query . $secret);

    // urlencode our parameters
    $appid = urlencode($appid);
    $query = urlencode($query);
    $signature = urlencode($signature);

    // send our request
    $url = "{$this->endpoint}?q={$query}&id={$appid}&sig={$signature}&out=xml&v=2";
    $request = new WP_Http();
    $result = $request->request($url, array('timeout' => 1));  // timeout after 1 second

    $xml = @ simplexml_load_string($result['body']);

    if (!$xml) {

      // error parsing xml response
      return false; }

    if ($xml->attributes()->responsecode == "200" AND strlen((string) $xml->suggestion) != 0) {

      // valid request with a non empty suggestion
      return array('suggestion' => (string) $xml->suggestion, 'html' => (string) $xml->html); }

    else {

      // more error handling could go here
      //   see http://www.properspell.com/documentation/ for possible response codes

      return false; }

  }

}