<?php
namespace Content_Mood\Controllers\Admin;
use Content_Mood\Traits\Hook;

defined( 'ABSPATH' ) || exit;

class Menu {
    use Hook;

    public function __construct() {
        $this->action( 'admin_menu', [ $this, 'register_menus' ] );
        // $this->action( 'admin_enqueue_scripts', [ $this, 'enqueue_menu_script' ] );
    }

    public function register_menus() {
        // Main top-level menu (Overview/Landing page - NO hash)
        add_menu_page(
            __( 'Sentiment Analyzer', 'content-mood-analyzer' ),
            __( 'Sentiment Analyzer', 'content-mood-analyzer' ),
            'manage_options',
            'content-mood-analyzer',
            [ $this, 'render_main_page' ],
            'dashicons-chart-line',
            30
        );

        // Overview submenu (same as parent - NO hash)
        add_submenu_page(
            'content-mood-analyzer',
            __( 'Overview', 'content-mood-analyzer' ),
            __( 'Overview', 'content-mood-analyzer' ),
            'manage_options',
            'content-mood-analyzer',
            [ $this, 'render_main_page' ]
        );

        // Dashboard submenu (WITH hash)
        add_submenu_page(
            'content-mood-analyzer',
            __( 'Dashboard', 'content-mood-analyzer' ),
            __( 'Dashboard', 'content-mood-analyzer' ),
            'manage_options',
            'content-mood-analyzer#/dashboard',
            [ $this, 'render_main_page' ]
        );

        // All Sentiments submenu
        add_submenu_page(
            'content-mood-analyzer',
            __( 'All Sentiments', 'content-mood-analyzer' ),
            __( 'All Sentiments', 'content-mood-analyzer' ),
            'manage_options',
            'content-mood-analyzer#/sentiments',
            [ $this, 'render_main_page' ]
        );

        // Settings submenu
        add_submenu_page(
            'content-mood-analyzer',
            __( 'Settings', 'content-mood-analyzer' ),
            __( 'Settings', 'content-mood-analyzer' ),
            'manage_options',
            'content-mood-analyzer#/settings',
            [ $this, 'render_main_page' ]
        );
    }

    public function render_main_page() {
        echo '<div class="wrap"><div id="sentiment-root">Loading...</div></div>';
    }

    /**
     * Enqueue script to handle hash-based navigation in WordPress admin menu
     */
    public function enqueue_menu_script() {
        $screen = get_current_screen();
        
        // Only load on our plugin pages
        if ( ! $screen || strpos( $screen->id, 'content-mood-analyzer' ) === false ) {
            return;
        }

        // Inline script to handle menu clicks
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Handle clicks on menu items with hash routes
            $('#adminmenu a[href*="content-mood-analyzer#"]').on('click', function(e) {
                e.preventDefault();
                
                var href = $(this).attr('href');
                var hashPart = href.split('#')[1];
                
                // Update the URL hash without page reload
                window.location.hash = '#' + hashPart;
                
                // Update active menu item
                $('#adminmenu .wp-submenu li').removeClass('current');
                $(this).parent().addClass('current');
            });

            // Handle clicks on main menu without hash (Overview)
            $('#adminmenu a[href="admin.php?page=content-mood-analyzer"]').on('click', function(e) {
                var currentPage = window.location.href.split('?')[1]?.split('#')[0];
                
                // Only prevent default if we're already on the page
                if (currentPage === 'page=content-mood-analyzer') {
                    e.preventDefault();
                    
                    // Clear the hash and go to overview
                    window.location.hash = '';
                    
                    // Update active menu item
                    $('#adminmenu .wp-submenu li').removeClass('current');
                    $(this).parent().addClass('current');
                }
                // Otherwise, let it navigate normally (page reload)
            });

            // Set active menu item based on current hash on page load
            var currentHash = window.location.hash;
            
            if (currentHash) {
                // If there's a hash, highlight that menu item
                $('#adminmenu a[href*="' + currentHash + '"]').parent().addClass('current');
            } else {
                // If no hash, highlight the Overview menu item
                $('#adminmenu a[href="admin.php?page=content-mood-analyzer"]').parent().addClass('current');
            }
        });
        </script>
        <?php
    }
}