<?php
namespace Sentiment\Controllers\Admin;
use Sentiment\Traits\Hook;

defined( 'ABSPATH' ) || exit;

class Menu {
	use Hook;

	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {
		$this->action( 'admin_menu', array( $this, 'add_admin_menu' ) );
	}
	  
    /**
     * Add admin menu as standalone menu (not under Settings)
     */
    public function add_admin_menu() {
        add_menu_page(
            __( 'Sentiment Analyzer', 'sentiment-analyzer' ),
            __( 'Sentiment Analyzer', 'sentiment-analyzer' ),
            'manage_options',                                
            'sentiment-analyzer',                                 
            array( $this, 'settings_page' ),                   
            'dashicons-chart-line',                            
            30     
        );
    }

	/**
     * Settings page HTML
     */
     public function settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die(__( 'You do not have sufficient permissions to access this page.', 'sentiment-analyzer' ) );
        }

        ?>
        
		<div id="root-menu"></div>
        <?php
    }
}