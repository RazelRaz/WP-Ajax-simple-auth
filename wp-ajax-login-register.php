<?php
/*
 * Plugin Name:       WP Ajax Login Register
 * Description:       This is a basic Plugin for WP Ajax Login Register
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Razel Ahmed
 * Author URI:        https://razelahmed.com
 */

 if ( ! defined('ABSPATH') ) {
  exit;
 }

 class simple_auth {
    public function __construct() {
      add_shortcode( 'simple-auth', [$this, 'render_shortcode'] );
      add_action( 'wp_enqueue_scripts', [$this, 'enqueue_scripts'] );
      // WP Ajax
      add_action( 'wp_ajax_simple-auth-profile-form', [$this, 'update_profile' ] );
      // login
      add_action( 'wp_ajax_simple-auth-login-form', [$this, 'handle_login' ] );
    }

    public function enqueue_scripts() {
      // Define the base URL for the plugin
      $plugin_url = plugin_dir_url(__FILE__);
      // Custom css
      wp_enqueue_style( 'simple-auth-style', $plugin_url . 'assets/css/auth.css', array(), '1.0.0', 'all' );
      wp_enqueue_script('simple-auth-js', $plugin_url . 'assets/js/auth.js', array('jquery', 'wp-util'), '1.0.0');
      wp_localize_script( 'simple-auth-js', 'simpleAuthAjax', [
         'ajax_url' => admin_url( 'admin-ajax.php' ),
         'nonce' => wp_create_nonce( 'simple-auth-nonce' ),
      ]);
    }

    public function render_shortcode() {

      if ( is_user_logged_in() ) {
         return $this->render_profile_page();
      } else {
         return $this->render_auth_page();
      }

    }

    public function update_profile() {
      // print_r( $_POST );
      check_ajax_referer('simple-auth-profile');

      // Manual Verification
      // if ( ! isset($_POST['_wpnonce']) ) {
      //    return wp_send_json_error(['message' => 'Nonce Not Available']);
      // }

      // if( wp_verify_nonce( $_POST['_wp_nonce'], 'simple-auth-profile' ) ){
      //    return wp_send_json_error(['message' => 'Nonce Verification Failed']);
      // }

      $display_name = sanitize_text_field( $_POST['display_name'] );
      $email =  sanitize_email( $_POST['email'] );
      $user_data = [
         'ID' => get_current_user_id(),
         'display_name' => $display_name,
         'user_email' => $email,
      ];

      $user_id = wp_update_user($user_data);

      if ( is_wp_error( $user_id ) ) {
         wp_send_json_error([
            'message' => $user_id->get_error_message(),
         ]);
      }

      wp_send_json_success( [
         'message' => 'Profile updated',
      ] );

      print_r( $_POST );

      exit;
    }

   //  login
   public function handle_login() {
      check_ajax_referer('simple-auth-login');

      $username = sanitize_text_field( $_POST['username'] );
      $password = sanitize_text_field( $_POST['password'] );

      $user = wp_signon( [
         'user_login' => $username,
         'user_password' => $password,
         'remember' => true,
      ] );

      if ( is_wp_error($user) ) {
         wp_send_json_error([
            'message' => $user->get_error_message(),
         ]);
      }

      wp_send_json_success( [
         'message' => 'Login Success',
      ] );

   }

   //  profile
    public function render_profile_page() {
      // return 'Profile Page';
      $user = wp_get_current_user();
      // echo '<pre>';
      // print_r($user);
      ob_start(); ?>
         <div id="simple-auth-profile">
            <h2>Update Profile</h2>

            <div id="profile-update-message"></div>

            <form method="post" id="profile-form">
               <input type="text" name="display_name" value="<?php echo esc_attr( $user->display_name ); ?>">
               <input type="email" name="email" id="" value="<?php echo esc_attr( $user->user_email ); ?>">

               <input type="hidden" name="action" value="simple-auth-profile-form">

               <?php  wp_nonce_field('simple-auth-profile'); ?>

               <button type="submit">Update Profile</button>
            </form>
         </div>
      <?php
      return ob_get_clean();

    }

   //  auth
    public function render_auth_page() {

      $user = wp_get_current_user();

      ob_start(); ?>
         <div id="simple-auth-profile">
            <h2>Login</h2>

            <div id="login-message"></div>
            <form method="post" id="simple-auth-login-form">
               <input type="text" name="username" value="" placeholder="User Name">
               <input type="password" name="password" id="" value="" placeholder="Password">

               <input type="hidden" name="action" value="simple-auth-login-form">

               <?php  wp_nonce_field('simple-auth-login'); ?>

               <button type="submit">Login</button>
            </form>
         </div>
      <?php
      return ob_get_clean();
    }

 }

 new simple_auth();