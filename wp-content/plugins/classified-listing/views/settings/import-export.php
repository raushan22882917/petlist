<?php
/**
 * Export / Import settings page wrapper.
 *
 * Renders the page header, the pill-style tab bar and routes the active tab
 * to its matching partial under views/settings/. The new card-based UI is
 * scoped to the .rtcl-ie-app container so it can't leak into other settings
 * screens.
 */

defined( 'ABSPATH' ) || exit;

use Rtcl\Helpers\Functions;

if ( ! function_exists( 'rtcl_ie_icon' ) ) {
	/**
	 * Output a small inline SVG icon used across the Export / Import UI.
	 *
	 * Keeping the icons inline avoids loading an external icon font / library
	 * just for this screen. Only the icons actually referenced from the views
	 * are listed here.
	 *
	 * @param string $name  Icon identifier.
	 * @param string $class Optional extra CSS class.
	 */
	function rtcl_ie_icon( $name, $class = '' ) {
		$paths = [
			'arrow-down-up'  => '<path d="M3 16l4 4 4-4M7 20V4M21 8l-4-4-4 4M17 4v16"/>',
			'upload'         => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/>',
			'download'       => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>',
			'rss'            => '<path d="M4 11a9 9 0 0 1 9 9M4 4a16 16 0 0 1 16 16"/><circle cx="5" cy="19" r="1"/>',
			'map-pin'        => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
			'history'        => '<path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/>',
			'file-json'      => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M10 13a2 2 0 0 0-2 2v1a2 2 0 0 1-2 2 2 2 0 0 1 2 2v1a2 2 0 0 0 2 2"/><path d="M14 13a2 2 0 0 1 2 2v1a2 2 0 0 0 2 2 2 2 0 0 0-2 2v1a2 2 0 0 1-2 2"/>',
			'table'          => '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M3 15h18M9 3v18M15 3v18"/>',
			'git-merge'      => '<circle cx="18" cy="18" r="3"/><circle cx="6" cy="6" r="3"/><path d="M6 21V9a9 9 0 0 0 9 9"/>',
			'activity'       => '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>',
			'folder-open'    => '<path d="M6 14l1.45-2.9A2 2 0 0 1 9.24 10H20a2 2 0 0 1 1.94 2.5l-1.55 6a2 2 0 0 1-1.94 1.5H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h3.93a2 2 0 0 1 1.66.9l.82 1.2a2 2 0 0 0 1.66.9H18a2 2 0 0 1 2 2v2"/>',
			'search'         => '<circle cx="11" cy="11" r="8"/><path d="M21 21l-4.3-4.3"/>',
			'sliders'        => '<line x1="21" y1="4" x2="14" y2="4"/><line x1="10" y1="4" x2="3" y2="4"/><line x1="21" y1="12" x2="12" y2="12"/><line x1="8" y1="12" x2="3" y2="12"/><line x1="21" y1="20" x2="16" y2="20"/><line x1="12" y1="20" x2="3" y2="20"/><line x1="14" y1="2" x2="14" y2="6"/><line x1="8" y1="10" x2="8" y2="14"/><line x1="16" y1="18" x2="16" y2="22"/>',
			'settings'       => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9 1.65 1.65 0 0 0 4.27 7.18l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>',
			'check'          => '<polyline points="20 6 9 17 4 12"/>',
			'check-circle'   => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
			'sparkles'       => '<path d="M12 3l1.9 4.6L18.5 9.5l-4.6 1.9L12 16l-1.9-4.6L5.5 9.5l4.6-1.9z"/><path d="M19 14l.9 2 2.1.8-2.1.8L19 20l-.9-2.4L16 16.8l2.1-.8z"/>',
			'rotate-ccw'     => '<polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/>',
			'arrow-right'    => '<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>',
			'save'           => '<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>',
			'flask'          => '<path d="M9 2v6.5L3.5 19A2 2 0 0 0 5.4 22h13.2a2 2 0 0 0 1.9-3L15 8.5V2"/><line x1="9" y1="2" x2="15" y2="2"/>',
			'play'           => '<polygon points="5 3 19 12 5 21 5 3"/>',
			'trash'          => '<polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>',
			'eye'            => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
			'filter'         => '<polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>',
			'alert'          => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
			'alert-triangle' => '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
			'x-circle'       => '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>',
			'server'         => '<rect x="2" y="2" width="20" height="8" rx="2" ry="2"/><rect x="2" y="14" width="20" height="8" rx="2" ry="2"/><line x1="6" y1="6" x2="6.01" y2="6"/><line x1="6" y1="18" x2="6.01" y2="18"/>',
			'crosshair'      => '<circle cx="12" cy="12" r="10"/><line x1="22" y1="12" x2="18" y2="12"/><line x1="6" y1="12" x2="2" y2="12"/><line x1="12" y1="6" x2="12" y2="2"/><line x1="12" y1="22" x2="12" y2="18"/>',
			'x'              => '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
			'list'           => '<line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>',
		];

		$class = trim( 'rtcl-ie-svg ' . $class );
		$inner = $paths[ $name ] ?? '';
		echo '<svg class="' . esc_attr( $class ) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $inner . '</svg>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- icon SVG is statically defined above.
	}
}

$active_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'export'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only tab switcher.
$tabs       = [
	'export'  => [ 'label' => __( 'Export', 'classified-listing' ),        'icon' => 'download' ],
	'import'  => [ 'label' => __( 'Import', 'classified-listing' ),        'icon' => 'upload' ],
	'rss'     => [ 'label' => __( 'RSS Feeds', 'classified-listing' ),     'icon' => 'rss' ],
	'google'  => [ 'label' => __( 'Google Places', 'classified-listing' ), 'icon' => 'map-pin' ],
	'history' => [ 'label' => __( 'History', 'classified-listing' ),       'icon' => 'history' ],
];
if ( ! array_key_exists( $active_tab, $tabs ) ) {
	$active_tab = 'export';
}
?>
<div class="rtcl-admin-wrap rtcl-import-export-wrapper rtcl-ie-app">
	<header class="rtcl-ie-head">
		<div class="rtcl-ie-head-icon"><?php rtcl_ie_icon( 'arrow-down-up' ); ?></div>
		<div class="rtcl-ie-head-text">
			<h1><?php esc_html_e( 'Export Import Settings', 'classified-listing' ); ?></h1>
			<p><?php esc_html_e( 'Import listings from external sources and map them to your forms', 'classified-listing' ); ?></p>
		</div>
	</header>

	<nav class="rtcl-ie-tabs" role="tablist">
		<?php foreach ( $tabs as $key => $tab ) : ?>
			<a href="<?php echo esc_url( add_query_arg( [ 'page' => 'rtcl-import-export', 'tab' => $key ], admin_url( 'admin.php' ) ) ); ?>"
				class="rtcl-ie-tab<?php echo $active_tab === $key ? ' is-active' : ''; ?>"
				role="tab"
				aria-selected="<?php echo $active_tab === $key ? 'true' : 'false'; ?>">
				<?php rtcl_ie_icon( $tab['icon'] ); ?>
				<span><?php echo esc_html( $tab['label'] ); ?></span>
			</a>
		<?php endforeach; ?>
	</nav>

	<div class="rtcl-ie-body">
		<?php
		if ( 'import' === $active_tab ) {
			require_once RTCL_PATH . 'views/settings/import.php';
		} elseif ( 'export' === $active_tab ) {
			require_once RTCL_PATH . 'views/settings/export.php';
		} elseif ( 'rss' === $active_tab ) {
			require_once RTCL_PATH . 'views/settings/import-rss.php';
		} elseif ( 'google' === $active_tab ) {
			require_once RTCL_PATH . 'views/settings/import-google.php';
		} elseif ( 'history' === $active_tab ) {
			require_once RTCL_PATH . 'views/settings/import-history.php';
		}
		?>
	</div>
</div>
