<?php

namespace Rtcl\Controllers;

use Rtcl\Helpers\Functions;

class BlockTemplateController {

	public static function init() {
		add_filter( 'get_block_templates', [ __CLASS__, 'register_block_templates' ], 10, 3 );
	}

	public static function register_block_templates( $templates, $query, $template_type ) {
		if ( 'wp_template' !== $template_type ) {
			return $templates;
		}

		if ( empty( $query['slug__in'] ) || ! is_array( $query['slug__in'] ) ) {
			return $templates;
		}

		$map = [
			'archive-rtcl_listing'   => __( 'Listing Archive', 'classified-listing' ),
			'taxonomy-rtcl_location' => __( 'Listing Location', 'classified-listing' ),
			'taxonomy-rtcl_category' => __( 'Listing Category', 'classified-listing' ),
			'single-rtcl_listing'    => __( 'Listing Single', 'classified-listing' ),
			'author'                 => __( 'Listing Author', 'classified-listing' ),
		];

		$requested = $query['slug__in'];

		foreach ( $map as $slug => $title ) {
			if ( ! in_array( $slug, $requested, true ) ) {
				continue;
			}

			$rtcl_template = 'author' === $slug ? 'author-rtcl_listing' : $slug;

			ob_start();

			Functions::get_template( $rtcl_template );

			$content = ob_get_clean();

			$templates[] = (object) [
				'id'             => 'classified-listing//' . $slug,
				'slug'           => $slug,
				'title'          => $title,
				'description'    => $title,
				'type'           => 'wp_template',
				'source'         => 'plugin',
				'origin'         => 'plugin',
				'theme'          => null,
				'content'        => $content,
				'has_theme_file' => false,
				'is_custom'      => true,
			];
		}

		return $templates;
	}
}