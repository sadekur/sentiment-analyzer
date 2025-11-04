<?php
namespace EasyCommerce\Controllers\Admin;

defined( 'ABSPATH' ) || exit;

use EasyCommerce\Traits\Hook;
use EasyCommerce\Traits\Asset;
use EasyCommerce\Traits\Menu as Menu_Trait;
use EasyCommerce\Helpers\Utility;

class Menu {

	use Hook;
	use Asset;
	use Menu_Trait;

	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {
		add_action('admin_menu', array($this, 'add_admin_menu'));
		add_action('admin_init', array($this, 'register_settings'));
		add_action('admin_post_bulk_update_sentiment', array($this, 'handle_bulk_update'));
	}

	// public function register() {
	// 	$menus = easycommerce_menus();

	// 	$register_submenus = function( $parent_slug, $submenus, $level = 0 ) use ( &$register_submenus ) {
	// 		foreach ( $submenus as $submenu ) {
	// 			$prefix = str_repeat( '↳', $level ) . ' ';

	// 			$this->add_submenu(
	// 				$parent_slug,
	// 				$submenu['page_title'],
	// 				$prefix . $submenu['menu_title'],
	// 				$submenu['capability'] ?? 'manage_options',
	// 				$submenu['slug'],
	// 				$submenu['callback'] ?? '__return_null'
	// 			);

	// 			if ( ! empty( $submenu['submenus'] ) ) {
	// 				$register_submenus( $parent_slug, $submenu['submenus'], $level + 1 );
	// 			}
	// 		}
	// 	};

	// 	foreach ( $menus as $menu ) {
	// 		$this->add_menu(
	// 			$menu['title'],
	// 			$menu['menu_title'],
	// 			$menu['capability'],
	// 			$menu['slug'],
	// 			$menu['callback'],
	// 			$menu['icon'],
	// 			$menu['position']
	// 		);

	// 		if ( ! empty( $menu['submenus'] ) ) {
	// 			$register_submenus( $menu['slug'], $menu['submenus'] );
	// 		}
	// 	}
	// }
}
