<?php
/**
 * Plugin Name: WP-Api
 * Plugin URI: http://sourceelec.com
 * Description: rest api plugin.
 * Author: Soheil Ghs
 * Version: 1.0
 * Author URI: http://sourceelec.com
 */

defined('ABSPATH') || exit();

class WPAPI {

  protected static $instance;

  public static function getInstance() {
    if (is_null(self::$instance)) {
      self::$instance = new self();
    }
  }

  private function __construct() {
    register_activation_hook(__FILE__, [$this, 'activate']);
    register_deactivation_hook(__FILE__, [$this, 'deactivate']);
    $this->define_constants();
    $this->do_includes();
    $this->init();
  }

  private function do_includes() {
    require WPAPI_DIR . DIRECTORY_SEPARATOR . 'vendor'
      . DIRECTORY_SEPARATOR . 'autoload.php';
  }

  private function define_constants() {
    define('WPAPI_DIR', plugin_dir_path(__FILE__));
  }

  public function init() {
    add_action('init', [$this, 'register_routes']);
    add_filter('query_vars', [$this, 'register_query_vars']);
    add_action('parse_request', [$this, 'parse_request']);
  }

  public function register_routes() {
    add_rewrite_rule('^api\/([\w0-9]+)\/([\w]+)\/([\w]+)',
      'index.php?api=1&version=$matches[1]&class=$matches[2]&method=$matches[3]',
    'top');
    flush_rewrite_rules();
  }

  public function register_query_vars($vars) {
    $vars[] = 'api';
    $vars[] = 'version';
    $vars[] = 'class';
    $vars[] = 'method';
    return $vars;
  }

  public function parse_request($query) {
    if(isset($query->query_vars['api']) &&
      intval($query->query_vars['api']) == 1) {
      $version = $query->query_vars['version'];
      $class = $query->query_vars['class'];
      $method = $query->query_vars['method'];
      global $wp_queries, $wp_rewrite;
      $full_class_path = "\\App\\" . $version . "\\Controllers\\"
        . ucfirst($class) . "Controller";
      $target_class = new $full_class_path;
      if (method_exists($target_class, $method)) {
        $target_class->{$method}();
      }

      exit;
    }
  }

  public function activate() {
    if (!wp_next_scheduled('wpvip_optimize_db')) {
      wp_schedule_event(time(), 'daily', 'wpvip_optimize_db');
    }

    /*function my_add_weekly($schedules) {
      $schedules['weekly'] = array(
        'interval' => 604800,
        'display' => __('Once Weekly')
      );

      return $schedules;
    }

    add_filter('cron_schedules', 'my_add_weekly');*/
  }

  public function deactivate() {
  }
}

WPAPI::getInstance();