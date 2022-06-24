<?php

abstract class Smartcrawl_Entity {
	const NOINDEX_KEY_FORMAT = 'meta_robots-noindex-%s';
	const NOFOLLOW_KEY_FORMAT = 'meta_robots-nofollow-%s';
	const SUBSEQUENT_PAGES_KEY_FORMAT = 'meta_robots-%s-subsequent_pages';

	/**
	 * @var string
	 */
	private $meta_title;
	/**
	 * @var string
	 */
	private $meta_description;
	/**
	 * @var string
	 */
	private $robots;
	/**
	 * @var string
	 */
	private $canonical_url;
	/**
	 * @var array
	 */
	private $schema;
	/**
	 * @var array
	 */
	private $opengraph_tags;
	/**
	 * @var bool
	 */
	private $opengraph_enabled;
	/**
	 * @var string
	 */
	private $opengraph_title;
	/**
	 * @var string
	 */
	private $opengraph_description;
	/**
	 * @var array
	 */
	private $opengraph_images;
	/**
	 * @var bool
	 */
	private $twitter_enabled;
	/**
	 * @var string
	 */
	private $twitter_title;
	/**
	 * @var array
	 */
	private $twitter_images;
	/**
	 * @var string
	 */
	private $twitter_description;

	public function get_meta_title() {
		if ( is_null( $this->meta_title ) ) {
			$this->meta_title = $this->load_meta_title();
		}
		return $this->filter_meta_title( $this->meta_title );
	}

	abstract protected function load_meta_title();

	public function get_meta_description() {
		if ( is_null( $this->meta_description ) ) {
			$this->meta_description = $this->load_meta_description();
		}
		return $this->filter_meta_desc( $this->meta_description );
	}

	abstract protected function load_meta_description();

	public function get_robots() {
		if ( is_null( $this->robots ) ) {
			$this->robots = $this->load_robots();
		}
		return $this->robots;
	}

	abstract protected function load_robots();

	public function get_canonical_url() {
		if ( is_null( $this->canonical_url ) ) {
			$this->canonical_url = $this->load_canonical_url();
		}
		return $this->canonical_url;
	}

	abstract protected function load_canonical_url();

	public function get_schema() {
		if ( is_null( $this->schema ) ) {
			$this->schema = $this->load_schema();
		}
		return $this->schema;
	}

	abstract protected function load_schema();

	public function get_opengraph_tags() {
		if ( is_null( $this->opengraph_tags ) ) {
			$this->opengraph_tags = $this->load_opengraph_tags();
		}
		return $this->opengraph_tags;
	}

	protected function load_opengraph_tags() {
		$tags = array(
			'og:type'        => 'object',
			'og:url'         => $this->get_canonical_url(),
			'og:title'       => $this->get_opengraph_title(),
			'og:description' => $this->get_opengraph_description(),
		);

		return $this->add_opengraph_image_tags( $tags );
	}

	/**
	 * @param array $tags
	 *
	 * @return array
	 */
	private function add_opengraph_image_tags( $tags ) {
		$included_urls = array();
		$images = $this->get_opengraph_images();
		$images = ! empty( $images ) && is_array( $images )
			? $images
			: array();

		foreach ( $images as $image ) {
			$url = smartcrawl_get_array_value( $image, 0 );
			$width = smartcrawl_get_array_value( $image, 1 );
			$height = smartcrawl_get_array_value( $image, 2 );

			if ( ! $width || ! $height ) {
				$attachment = smartcrawl_get_attachment_by_url( trim( $url ) );
				if ( $attachment ) {
					$width = $attachment['width'];
					$height = $attachment['height'];
				}
			}

			if ( array_search( $url, $included_urls ) !== false ) {
				continue;
			}

			$image_tags = array();
			if ( $url ) {
				$image_tags['og:image'] = $url;
			}
			if ( $width ) {
				$image_tags['og:image:width'] = $width;
			}
			if ( $height ) {
				$image_tags['og:image:height'] = $height;
			}

			if ( $image_tags ) {
				$tags[] = $image_tags;
			}
		}
		return $tags;
	}

	public function is_opengraph_enabled() {
		if ( is_null( $this->opengraph_enabled ) ) {
			$this->opengraph_enabled = $this->load_opengraph_enabled();
		}
		return $this->opengraph_enabled;
	}

	abstract protected function load_opengraph_enabled();

	public function get_opengraph_title() {
		if ( is_null( $this->opengraph_title ) ) {
			$this->opengraph_title = $this->load_opengraph_title();
		}
		return $this->filter_opengraph_title( $this->opengraph_title );
	}

	abstract protected function load_opengraph_title();

	public function get_opengraph_description() {
		if ( is_null( $this->opengraph_description ) ) {
			$this->opengraph_description = $this->load_opengraph_description();
		}
		return $this->filter_opengraph_description( $this->opengraph_description );
	}

	abstract protected function load_opengraph_description();

	public function get_opengraph_images() {
		if ( is_null( $this->opengraph_images ) ) {
			$this->opengraph_images = $this->load_opengraph_images();
		}
		return $this->filter_opengraph_images( $this->opengraph_images );
	}

	abstract protected function load_opengraph_images();

	private function filter_opengraph_title( $title ) {
		return apply_filters( 'wds_custom_og_title', $title );
	}

	private function filter_opengraph_description( $description ) {
		return apply_filters( 'wds_custom_og_description', $description );
	}

	private function filter_opengraph_images( $images ) {
		return apply_filters( 'wds_custom_og_image', $images );
	}

	public function is_twitter_enabled() {
		if ( is_null( $this->twitter_enabled ) ) {
			$this->twitter_enabled = $this->load_twitter_enabled();
		}
		return $this->twitter_enabled;
	}

	abstract protected function load_twitter_enabled();

	public function get_twitter_title() {
		if ( is_null( $this->twitter_title ) ) {
			$this->twitter_title = $this->load_twitter_title();
		}
		return $this->filter_twitter_title( $this->twitter_title );
	}

	abstract protected function load_twitter_title();

	public function get_twitter_description() {
		if ( is_null( $this->twitter_description ) ) {
			$this->twitter_description = $this->load_twitter_description();
		}
		return $this->filter_twitter_description( $this->twitter_description );
	}

	abstract protected function load_twitter_description();

	public function get_twitter_images() {
		if ( is_null( $this->twitter_images ) ) {
			$this->twitter_images = $this->load_twitter_images();
		}
		return $this->filter_twitter_images( $this->twitter_images );
	}

	abstract protected function load_twitter_images();

	private function filter_twitter_title( $title ) {
		return apply_filters( 'wds_custom_twitter_title', $title );
	}

	private function filter_twitter_description( $description ) {
		return apply_filters( 'wds_custom_twitter_description', $description );
	}

	private function filter_twitter_images( $images ) {
		return apply_filters( 'wds_custom_twitter_image', $images );
	}

	abstract public function get_macros( $subject = '' );

	protected function load_meta_title_from_options( $location ) {
		return $this->get_onpage_option( 'title-' . $location );
	}

	protected function load_meta_desc_from_options( $location ) {
		return $this->get_onpage_option( 'metadesc-' . $location );
	}

	protected function is_opengraph_enabled_for_location( $location ) {
		return $this->get_onpage_option( 'og-active-' . $location );
	}

	protected function load_opengraph_title_from_options( $location ) {
		return $this->get_onpage_option( 'og-title-' . $location );
	}

	protected function load_opengraph_description_from_options( $location ) {
		return $this->get_onpage_option( 'og-description-' . $location );
	}

	protected function load_opengraph_images_from_options( $location ) {
		return $this->get_onpage_option( 'og-images-' . $location );
	}

	protected function is_twitter_enabled_for_location( $location ) {
		return $this->get_onpage_option( 'twitter-active-' . $location );
	}

	protected function load_twitter_title_from_options( $location ) {
		return $this->get_onpage_option( 'twitter-title-' . $location );
	}

	protected function load_twitter_description_from_options( $location ) {
		return $this->get_onpage_option( 'twitter-description-' . $location );
	}

	protected function load_twitter_images_from_options( $location ) {
		return $this->get_onpage_option( 'twitter-images-' . $location );
	}

	protected function get_onpage_option( $key ) {
		$options = $this->get_onpage_options();

		return smartcrawl_get_array_value( $options, $key );
	}

	protected function sanitize_string( $string ) {
		if ( ! is_string( $string ) ) {
			return '';
		}

		$sanitization_functions = array( 'trim', 'wp_strip_all_tags', 'smartcrawl_normalize_whitespace' );
		foreach ( $sanitization_functions as $function ) {
			$string = call_user_func( $function, $string );
			if ( empty( $string ) ) {
				return '';
			}
		}
		return $string;
	}

	public function is_noindex() {
		return strpos( $this->get_robots(), 'noindex' ) !== false;
	}

	public function is_nofollow() {
		return strpos( $this->get_robots(), 'nofollow' ) !== false;
	}

	protected function get_noindex_setting( $place ) {
		$options = $this->get_onpage_options();

		return (boolean) smartcrawl_get_array_value(
			$options,
			sprintf( self::NOINDEX_KEY_FORMAT, $place )
		);
	}

	protected function get_nofollow_setting( $place ) {
		$options = $this->get_onpage_options();

		return (boolean) smartcrawl_get_array_value(
			$options,
			sprintf( self::NOFOLLOW_KEY_FORMAT, $place )
		);
	}

	public function apply_macros( $subject ) {
		if ( strpos( $subject, '%%' ) === false ) {
			return $subject;
		}

		$macros = array_merge(
			$this->get_general_macros(),
			$this->get_macros( $subject )
		);

		$macros = apply_filters(
			'wds-known_macros',
			array_combine(
				apply_filters( 'wds-known_macros-keys', array_keys( $macros ) ),
				apply_filters( 'wds-known_macros-values', array_values( $macros ) )
			)
		);

		foreach ( $macros as $macro => $get_replacement ) {
			if ( strpos( $subject, $macro ) === false ) {
				continue;
			}

			$subject = str_replace(
				$macro,
				$this->resolve_macro( $macro, $get_replacement ),
				$subject
			);
		}

		return preg_replace( '/%%[a-zA-Z_]*%%/', '', $subject );
	}

	public function get_resolved_macros() {
		$resolved = array();
		foreach ( $this->get_macros() as $macro => $get_replacement ) {
			$resolved[ $macro ] = $this->resolve_macro( $macro, $get_replacement );
		}
		return $resolved;
	}

	private function resolve_macro( $macro, $get_replacement ) {
		if ( is_callable( $get_replacement ) ) {
			$replacement = call_user_func( $get_replacement );
		} elseif ( is_scalar( $get_replacement ) ) {
			$replacement = $get_replacement;
		} else {
			$replacement = '';
		}

		$replacement = apply_filters( 'wds-macro-variable_replacement', $replacement, $macro );

		return $this->process_macro_replacement_value( $replacement );
	}

	private function get_general_macros() {
		global $wp_query;
		$paged = intval( $wp_query->get( 'paged' ) );
		$max_num_pages = isset( $wp_query->max_num_pages ) ? $wp_query->max_num_pages : 1;
		$page_x_of_y = esc_html__( 'Page %1$s of %2$s', 'wds' );
		$options = $this->get_onpage_options();
		$preset_sep = ! empty( $options['preset-separator'] )
			? $options['preset-separator']
			: 'pipe';
		$separator = ! empty( $options['separator'] )
			? $options['separator']
			: smartcrawl_get_separators( $preset_sep );
		$pagenum = $paged;
		if ( 0 === $pagenum ) {
			$pagenum = $max_num_pages > 1 ? 1 : '';
		}

		return array(
			'%%sitename%%'         => get_bloginfo( 'name' ),
			'%%sitedesc%%'         => get_bloginfo( 'description' ),
			'%%page%%'             => 0 !== $paged ? sprintf( $page_x_of_y, $paged, $max_num_pages ) : '',
			'%%spell_page%%'       => 0 !== $paged ? sprintf( $page_x_of_y, smartcrawl_spell_number( $paged ), smartcrawl_spell_number( $max_num_pages ) ) : '',
			'%%pagetotal%%'        => $max_num_pages > 1 ? $max_num_pages : '',
			'%%spell_pagetotal%%'  => $max_num_pages > 1 ? smartcrawl_spell_number( $max_num_pages ) : '',
			'%%pagenumber%%'       => empty( $pagenum ) ? '' : $pagenum,
			'%%spell_pagenumber%%' => empty( $pagenum ) ? '' : smartcrawl_spell_number( $pagenum ),
			'%%currenttime%%'      => date_i18n( get_option( 'time_format' ) ),
			'%%currentdate%%'      => date_i18n( get_option( 'date_format' ) ),
			'%%currentmonth%%'     => date_i18n( 'F' ),
			'%%currentyear%%'      => date_i18n( 'Y' ),
			'%%sep%%'              => $separator,
		);
	}

	private function process_macro_replacement_value( $replacement ) {
		if ( $replacement === '<' ) {
			return $replacement;
		}

		if ( ! is_scalar( $replacement ) ) {
			return '';
		}

		return wp_strip_all_tags( $replacement );
	}

	private function get_onpage_options() {
		$onpage_options = Smartcrawl_Settings::get_component_options( Smartcrawl_Settings::COMP_ONPAGE );
		return empty( $onpage_options )
			? array()
			: $onpage_options;
	}

	protected function find_dynamic_replacements( $subject, $get_terms, $get_meta ) {
		$term_desc_replacements = $this->find_term_field_replacements( $subject, $get_terms, 'ct_desc_', 'description' );
		$subject = str_replace( array_keys( $term_desc_replacements ), '', $subject );

		$term_name_replacements = $this->find_term_field_replacements( $subject, $get_terms, 'ct_', 'name' );
		$subject = str_replace( array_keys( $term_name_replacements ), '', $subject );

		$meta_replacements = $this->find_meta_replacements( $subject, $get_meta );

		return array_merge( $term_desc_replacements, $term_name_replacements, $meta_replacements );
	}

	private function find_term_field_replacements( $subject, $get_terms, $prefix, $term_field ) {
		$pattern = "/(%%{$prefix}[a-z_\-]+%%)/";
		$matches = array();
		$replacements = array();
		$match_result = preg_match_all( $pattern, $subject, $matches, PREG_PATTERN_ORDER );
		if ( ! empty( $match_result ) ) {
			$placeholders = array_shift( $matches );
			foreach ( array_unique( $placeholders ) as $placeholder ) {
				$taxonomy_name = str_replace( array( "%%$prefix", '%%' ), '', $placeholder );

				$replacements[ $placeholder ] = function () use ( $get_terms, $term_field, $taxonomy_name ) {
					$taxonomy = get_taxonomy( $taxonomy_name );
					if ( empty( $taxonomy ) ) {
						return '';
					}

					$terms = call_user_func( $get_terms, $taxonomy_name );
					if ( empty( $terms ) ) {
						return '';
					}
					$term = array_shift( $terms );
					return wp_strip_all_tags( get_term_field( $term_field, $term, $taxonomy_name ) );
				};
			}
		}

		return $replacements;
	}

	private function find_meta_replacements( $subject, $get_meta ) {
		$prefix = 'cf_';
		$pattern = "/(%%{$prefix}[a-z_\-]+%%)/";
		$matches = array();
		$replacements = array();
		$match_result = preg_match_all( $pattern, $subject, $matches, PREG_PATTERN_ORDER );
		if ( ! empty( $match_result ) ) {
			$placeholders = array_shift( $matches );
			foreach ( array_unique( $placeholders ) as $placeholder ) {
				$meta_key = str_replace( array( "%%$prefix", '%%' ), '', $placeholder );

				$replacements[ $placeholder ] = function () use ( $get_meta, $meta_key ) {
					$meta_value = call_user_func( $get_meta, $meta_key );
					if ( empty( $meta_value ) || ! is_scalar( $meta_value ) ) {
						return '';
					}
					return wp_strip_all_tags( $meta_value );
				};

			}
		}

		return $replacements;
	}

	protected function load_string_value( $option_key_part, $load_from_meta, $load_from_options, $load_fallback ) {
		if ( ! $option_key_part ) {
			return '';
		}

		// Check if a meta value is available for this item
		$from_meta = call_user_func( $load_from_meta );
		if ( $from_meta ) {
			$from_meta = $this->sanitize_string( $from_meta );
			if ( $from_meta ) {
				return $this->apply_macros( $from_meta );
			}
		}

		return $this->load_option_string_value( $option_key_part, $load_from_options, $load_fallback );
	}

	protected function load_option_string_value( $option_key_part, $load_from_options, $load_fallback ) {
		if ( ! $option_key_part ) {
			return '';
		}

		// Check if an option is available
		$from_options = call_user_func( $load_from_options, $option_key_part );
		if ( $from_options ) {
			$from_options = $this->sanitize_string( $from_options );
			if ( $from_options ) {
				return $this->apply_macros( $from_options );
			}
		}

		// Use fallback value
		$fallback = call_user_func( $load_fallback );
		if ( $fallback ) {
			$fallback = $this->sanitize_string( $fallback );
			if ( $fallback ) {
				return $this->apply_macros( $fallback );
			}
		}

		// ¯\_(ツ)_/¯
		return '';
	}

	protected function filter_meta_title( $title ) {
		return apply_filters( 'wds_title', $title );
	}

	protected function filter_meta_desc( $description ) {
		return apply_filters( 'wds_metadesc', $description );
	}

	protected function show_robots_on_subsequent_pages_only( $location ) {
		return (boolean) smartcrawl_get_array_value(
			$this->get_onpage_options(),
			sprintf( self::SUBSEQUENT_PAGES_KEY_FORMAT, $location )
		);
	}

	protected function image_ids_to_urls( $image_ids ) {
		$image_ids = is_array( $image_ids ) || ! empty( $image_ids )
			? $image_ids
			: array();

		$images = array();
		foreach ( $image_ids as $image_id ) {
			$images = $this->image_id_to_url( $images, $image_id );
		}

		return $images;
	}

	protected function image_id_to_url( $images, $image_id ) {
		if ( empty( $images ) || ! is_array( $images ) ) {
			$images = array();
		}

		if ( is_numeric( $image_id ) ) {
			$attachment = wp_get_attachment_image_src( $image_id, 'full' );
			$attachment_url = smartcrawl_get_array_value( $attachment, 0 );
			if ( $attachment_url ) {
				$images[ $attachment_url ] = $attachment;
			}
		} else {
			$images[ $image_id ] = array( $image_id );
		}

		return $images;
	}
}