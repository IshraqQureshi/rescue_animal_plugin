<?php 

class EnqueueAssets {
  public function __construct()
  {
      // Hook to enqueue frontend scripts
      add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_scripts']);

      // Hook to enqueue admin scripts
      add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
  }

  /**
   * Enqueue CSS and JS for the frontend
   */
  public function enqueue_frontend_scripts()
  {
      // Enqueue a custom CSS file for the frontend
      wp_enqueue_style(
          'rescue-animals-frontend-css',
          plugin_dir_url(__DIR__) . 'assets/frontend/css/style.css',
          [],
      );

      // Enqueue a custom JS file for the frontend
      wp_enqueue_script(
          'rescue-animals-frontend-js',
          plugin_dir_url(__DIR__) . 'assets/frontend/js/script.js',
          ['jquery'], // Dependencies (e.g., jQuery)
      );
  }

  /**
   * Enqueue CSS and JS for the admin area
   */
  public function enqueue_admin_scripts($hook_suffix)
  {
      // Enqueue a custom CSS file for the admin
      wp_enqueue_style(
          'rescue-animals-admin-css',
          plugin_dir_url(__DIR__) . 'assets/admin/css/admin.css',
          [],
      );

      // Enqueue a custom JS file for the admin area
      wp_enqueue_script(
          'my-custom-admin-js',
          plugin_dir_url(__DIR__) . 'assets/admin/js/admin.js',
          [],
      );
  }
}