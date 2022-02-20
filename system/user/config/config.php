<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['site_license_key'] = '';
$config['autosave_interval_seconds'] = '10';
$config['enable_dock'] = 'y';
$config['enable_frontedit'] = 'y';
$config['automatic_frontedit_links'] = 'y';
$config['share_analytics'] = 'n';
$config['require_cookie_consent'] = 'y';
$config['enable_devlog_alerts'] = 'n';
$config['index_page'] = '';
$config['ip2nation'] = 'y';
$config['ip2nation_db_date'] = '1335677198';
$config['sc_paypal_account'] = '';
$config['sc_encrypt_buttons'] = 'n';
$config['sc_certificate_id'] = '';
$config['sc_public_certificate'] = '';
$config['sc_private_key'] = '';
$config['sc_paypal_certificate'] = '';
$config['sc_temp_path'] = '/tmp';
$config['legacy_member_templates'] = 'y';
$config['forum_is_installed'] = 'y';
$config['save_tmpl_files'] = 'y';
$config['disable_csrf_protection'] = "y";
$config['allow_member_registration'] = "n";
$config['login_reminder'] = 'n';
// ExpressionEngine Config Items
// Find more configs and overrides at
// https://docs.expressionengine.com/latest/general/system-configuration-overrides.html

$config['app_version'] = '6.2.5';
$config['encryption_key'] = '';
$config['session_crypt_key'] = '';
$config['database'] = array(
	'expressionengine' => array(
		'hostname' => '',
		'database' => '',
		'username' => '',
		'password' => '',
		'dbprefix' => 'exp_',
		'char_set' => 'utf8mb4',
		'dbcollat' => 'utf8mb4_unicode_ci',
		'port'     => ''
	),
);
$config['show_ee_news'] = 'y';

// EOF