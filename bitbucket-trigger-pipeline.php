<?php
/*
Plugin Name: BitBucket Trigger Pipeline
Plugin URI: https://github.com/kubmir/wordpress-bitbucket-trigger-pipeline
Description: Wordpress Plugin triggers a BitBucket Pipeline when user publishes a post (using environment variables BITBUCKET_PROJECT, BITBUCKET_WORKSPACE and BITBUCKET_API_KEY)
Version: 0.2
Author: kubmir (forked from: Kim T)
Author URI: https://github.com/kubmir
License: GPL
Copyright: kubmir (forked from: Kim T)
*/

add_action('publish_page', 'publish_static_hook');
add_action('publish_post', 'publish_static_hook');

function publish_static_hook($id) {

  $bitbucket_workspace = get_option('option_workspace');
  $bitbucket_project = get_option('option_project');
  $bitbucket_branch = get_option('option_branch');
  $bitbucket_api_key = get_option('option_api_key');

  // if variables are set, then trigger static build
  if ($bitbucket_workspace && $bitbucket_project && $bitbucket_branch && $bitbucket_api_key) {
    $data = array(
      'target' => array(
        'ref_type' => 'branch',
        'type' => 'pipeline_ref_target',
        'ref_name' => $bitbucket_branch
      )
    );
    // Based on Atlassian documentation
    // https://developer.atlassian.com/bitbucket/api/2/reference/resource/repositories/%7Bworkspace%7D/%7Brepo_slug%7D/pipelines/
    $response = wp_remote_post('https://api.bitbucket.org/2.0/repositories/'.$bitbucket_workspace.'/'.$bitbucket_project.'/pipelines/', array(
      'body' => json_encode($data),
      'headers' => array(
        'Authorization' => 'Bearer '.$bitbucket_api_key,
        'Content-Type' => 'application/json'
      ),
    ));
    // for debugging
    // echo '<pre>https://api.bitbucket.org/2.0/repositories/'.$bitbucket_workspace.'/'.$bitbucket_project.'/pipelines/</pre>';
    // echo '<pre>'.print_r($data, true).'</pre>';
    // echo '<pre>'.print_r($response, true).'</pre>';
  }
}

add_action('admin_init', 'my_general_section');
function my_general_section() {
  add_settings_section(
    'my_settings_section',
    'Bitbucket Settings',
    'my_section_options_callback',
    'general'
  );

  add_settings_field(
    'option_workspace',
    'BITBUCKET WORKSPACE',
    'my_textbox_callback',
    'general',
    'my_settings_section',
    array(
        'option_workspace'
    )
  );

  add_settings_field(
    'option_project',
    'BITBUCKET PROJECT',
    'my_textbox_callback',
    'general',
    'my_settings_section',
    array(
        'option_project'
    )
  );

  add_settings_field(
    'option_branch',
    'BITBUCKET BRANCH',
    'my_textbox_callback',
    'general',
    'my_settings_section',
    array(
        'option_branch'
    )
  );

	add_settings_field(
    'option_api_key',
    'BITBUCKET API KEY',
    'my_textbox_callback',
    'general',
    'my_settings_section',
    array(
        'option_api_key'
    )
  );

  register_setting('general','option_project', 'esc_attr');
	register_setting('general','option_branch', 'esc_attr');
	register_setting('general','option_workspace', 'esc_attr');
	register_setting('general','option_api_key', 'esc_attr');
}

function my_section_options_callback() {
    echo '<p>Settings for Bitbucket</p>';
}

function my_textbox_callback($args) {
    $option = get_option($args[0]);
    echo '<input type="text" id="'. $args[0] .'" name="'. $args[0] .'" value="' . $option . '" />';
}
