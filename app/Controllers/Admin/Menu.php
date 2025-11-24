<?php
namespace Sentiment\Controllers\Admin;
use Sentiment\Traits\Hook;

defined( 'ABSPATH' ) || exit;

class Menu {
    use Hook;

    public function __construct() {
        $this->action( 'admin_menu', [ $this, 'register_menus' ] );
    }

    public function register_menus() {
        // Main top-level menu
        add_menu_page(
            __( 'Sentiment Analyzer', 'sentiment-analyzer' ),
            __( 'Sentiment Analyzer', 'sentiment-analyzer' ),
            'manage_options',
            'sentiment-analyzer',                    // parent slug
            [ $this, 'render_main_page' ],
            'dashicons-chart-line',
            30
        );

        // Submenus (visible in WP admin sidebar)
        add_submenu_page(
            'sentiment-analyzer',
            __( 'Dashboard', 'sentiment-analyzer' ),
            __( 'Dashboard', 'sentiment-analyzer' ),
            'manage_options',
            'sentiment-analyzer',
            [ $this, 'render_main_page' ]
        );

        add_submenu_page(
            'sentiment-analyzer',
            __( 'Sentiments', 'sentiment-analyzer' ),
            __( 'Sentiments', 'sentiment-analyzer' ),
            'manage_options',
            'sentiment-analyzer#/sentiments',
            [ $this, 'render_main_page' ]
        );

        // add_submenu_page(
        //     'sentiment-analyzer',
        //     __( 'Settings', 'sentiment-analyzer' ),
        //     __( 'Settings', 'sentiment-analyzer' ),
        //     'manage_options',
        //     'sentiment-analyzer#/settings',
        //     [ $this, 'render_main_page' ]
        // );

        // You can add more like #/reports, #/help, etc.
    }

    /**
     * Render the React container (same for all submenu pages)
     */
    public function render_main_page() {
        echo '<div class="wrap"><div id="sentiment-root"></div></div>';
    }
}