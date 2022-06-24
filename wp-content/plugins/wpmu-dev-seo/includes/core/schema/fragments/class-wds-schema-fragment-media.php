<?php

class Smartcrawl_Schema_Fragment_Media extends Smartcrawl_Schema_Fragment {
	/**
	 * @var Smartcrawl_Post
	 */
	private $post;
	/**
	 * @var Smartcrawl_Schema_Utils
	 */
	private $utils;
	/**
	 * @var array
	 */
	private $schema = array();
	/**
	 * @var Smartcrawl_Controller_Media_Schema_Data
	 */
	private $media_schema_data_controller;

	public function __construct( $post ) {
		$this->post = $post;
		$this->utils = Smartcrawl_Schema_Utils::get();
		$this->media_schema_data_controller = Smartcrawl_Controller_Media_Schema_Data::get();
	}

	protected function get_raw() {
		$wp_post = $this->post->get_wp_post();
		if ( $this->is_audio_enabled() || $this->is_video_enabled() ) {
			$this->media_schema_data_controller->maybe_refresh_wp_embeds_cache( $wp_post );
			$cache = $this->media_schema_data_controller->get_cache( $wp_post->ID );

			if ( ! empty( $cache ) ) {
				$this->add_oembed_schema( $cache );
			}
		}

		$this->add_attachment_schema( $wp_post );

		return $this->schema;
	}

	private function add_oembed_schema( $cache ) {
		$enable_audio = $this->is_audio_enabled();
		$audio_data = smartcrawl_get_array_value( $cache, 'audio' );
		if ( $enable_audio && ! empty( $audio_data ) && is_array( $audio_data ) ) {
			foreach ( $audio_data as $audio_datum ) {
				$this->add_schema(
					$this->get_audio_schema( $audio_datum )
				);
			}
		}

		$enable_youtube = (bool) $this->utils->get_schema_option( 'schema_enable_yt_api' );
		$youtube_data = smartcrawl_get_array_value( $cache, 'youtube' );
		$youtube_data = empty( $youtube_data ) ? array() : $youtube_data;

		$enable_video = $this->is_video_enabled();
		$video_data = smartcrawl_get_array_value( $cache, 'video' );
		if ( $enable_video && ! empty( $video_data ) && is_array( $video_data ) ) {
			foreach ( $video_data as $video_id => $video_datum ) {
				if ( $enable_youtube && array_key_exists( $video_id, $youtube_data ) ) {
					$this->add_schema(
						$this->get_youtube_schema( $youtube_data[ $video_id ], $video_datum )
					);
				} else {
					$this->add_schema(
						$this->get_video_schema( $video_datum )
					);
				}
			}
		}
	}

	private function get_audio_schema( $data ) {
		return $this->media_data_to_schema( array(
			'title'         => "name",
			'url'           => "url",
			'description'   => "description",
			'thumbnail_url' => "thumbnailUrl",
		), $data, Smartcrawl_Schema_Type_Constants::TYPE_AUDIO_OBJECT );
	}

	private function get_video_schema( $data ) {
		$schema = $this->media_data_to_schema( array(
			'title'            => "name",
			'url'              => "url",
			'description'      => "description",
			'upload_date'      => "uploadDate",
			'thumbnail_url'    => array( "thumbnail", "url" ),
			'thumbnail_width'  => array( "thumbnail", "width" ),
			'thumbnail_height' => array( "thumbnail", "height" ),
		), $data, Smartcrawl_Schema_Type_Constants::TYPE_VIDEO_OBJECT );

		$duration = $this->get_duration( $data );
		if ( $duration ) {
			$schema['duration'] = $duration;
		}

		$schema = $this->add_embed_url_property( $schema, $data );

		return $schema;
	}

	private function get_youtube_schema( $data, $embed_data ) {
		$schema = $this->media_data_to_schema( array(
			'title'                => "name",
			'url'                  => "url",
			'description'          => "description",
			'publishedAt'          => "uploadDate",
			'duration'             => "duration",
			'defaultAudioLanguage' => "inLanguage",
			'definition'           => "videoQuality",
		), $data, Smartcrawl_Schema_Type_Constants::TYPE_VIDEO_OBJECT );

		$schema = $this->add_youtube_thumbnail_data( $data, $schema );

		$tags = smartcrawl_get_array_value( $data, 'tags' );
		if ( $tags && is_array( $tags ) ) {
			$schema["keywords"] = join( ',', $tags );
		}

		$schema = $this->add_embed_url_property( $schema, $embed_data );

		return $schema;
	}

	private function add_embed_url_property( $schema, $embed_data ) {
		if ( isset( $embed_data['html'] ) ) {
			$src_attribute = Smartcrawl_Html::find_attributes( 'iframe', 'src', $embed_data['html'] );
			if ( ! empty( $src_attribute ) ) {
				$schema['embedUrl'] = array_shift( $src_attribute );
			}
		}

		return $schema;
	}

	private function seconds_to_duration( $seconds ) {
		$mins = (int) gmdate( "i", $seconds );
		$secs = (int) gmdate( "s", $seconds );

		return "PT{$mins}M{$secs}S";
	}

	private function get_duration( $data ) {
		$seconds = smartcrawl_get_array_value( $data, 'duration' );
		if ( ! $seconds ) {
			return '';
		}

		return $this->seconds_to_duration( $seconds );
	}

	private function media_data_to_schema( $mapping, $data, $type ) {
		$schema = array(
			"@type" => $type,
		);
		foreach ( $mapping as $source_key => $target_key ) {
			$source_value = smartcrawl_get_array_value( $data, $source_key );
			if ( $source_value ) {
				smartcrawl_put_array_value( $source_value, $schema, $target_key );
			}
		}
		if ( ! empty( $schema['author'] ) ) {
			$schema['author'] = array( "@type" => Smartcrawl_Schema_Type_Constants::TYPE_PERSON ) + $schema['author'];
		}
		if ( ! empty( $schema['publisher'] ) ) {
			$schema['publisher'] = array( "@type" => Smartcrawl_Schema_Type_Constants::TYPE_ORGANIZATION ) + $schema['publisher'];
		}
		if ( ! empty( $schema['thumbnail'] ) ) {
			$schema['thumbnail'] = array( "@type" => Smartcrawl_Schema_Type_Constants::TYPE_IMAGE ) + $schema['thumbnail'];
			$schema['thumbnailUrl'] = $schema['thumbnail']['url'];
		}

		return $schema;
	}

	private function add_youtube_thumbnail_data( $data, array $schema ) {
		$thumbnails = smartcrawl_get_array_value( $data, 'thumbnails' );
		$thumbnail_url_default = '';
		foreach ( $thumbnails as $thumbnail_size => $thumbnail ) {
			$thumbnail_url = smartcrawl_get_array_value( $thumbnail, 'url' );
			$schema["thumbnail"][] = array(
				"@type"  => Smartcrawl_Schema_Type_Constants::TYPE_IMAGE,
				"url"    => $thumbnail_url,
				"width"  => smartcrawl_get_array_value( $thumbnail, 'width' ),
				"height" => smartcrawl_get_array_value( $thumbnail, 'height' ),
			);

			if ( $thumbnail_size === 'default' ) {
				$thumbnail_url_default = $thumbnail_url;
			}
		}
		if ( $thumbnail_url_default ) {
			$schema['thumbnailUrl'] = $thumbnail_url_default;
		}
		return $schema;
	}

	private function add_attachment_schema( $post ) {
		$src_attributes = Smartcrawl_Html::find_attributes( 'video, audio', 'src', $post->post_content );
		foreach ( $src_attributes as $html_element => $src_url ) {
			$attachment_id = attachment_url_to_postid( $src_url );
			if ( ! $attachment_id ) {
				continue;
			}

			$attachment = get_post( $attachment_id );

			if ( $this->is_mime_type_video( $attachment ) ) {
				$this->add_schema(
					$this->get_video_attachment_schema( $attachment, $html_element )
				);
			}

			if ( $this->is_mime_type_audio( $attachment ) ) {
				$this->add_schema(
					$this->get_audio_attachment_schema( $attachment )
				);
			}
		}
	}

	private function get_video_attachment_schema( $attachment, $video_element_html ) {
		$attachment_url = wp_get_attachment_url( $attachment->ID );
		$attachment_schema = $this->get_attachment_schema( Smartcrawl_Schema_Type_Constants::TYPE_VIDEO_OBJECT, $attachment, $attachment_url );

		// Video poster image
		$poster_image_url = $this->get_video_poster_attribute( $video_element_html );
		if ( $poster_image_url ) {
			$attachment_schema['thumbnailUrl'] = $poster_image_url;
		}

		return $attachment_schema;
	}

	private function get_video_poster_attribute( $video_element_html ) {
		$poster_values = Smartcrawl_Html::find_attributes( 'video', 'poster', $video_element_html );
		if ( count( $poster_values ) > 0 ) {
			return array_shift( $poster_values );
		}

		return '';
	}

	private function get_audio_attachment_schema( $attachment ) {
		return $this->get_attachment_schema(
			Smartcrawl_Schema_Type_Constants::TYPE_AUDIO_OBJECT,
			$attachment,
			wp_get_attachment_url( $attachment->ID )
		);
	}

	/**
	 * @param $attachment WP_Post
	 *
	 * @return bool
	 */
	private function is_mime_type_video( $attachment ) {
		return strpos( $attachment->post_mime_type, 'video/' ) !== false;
	}

	/**
	 * @param $attachment WP_Post
	 *
	 * @return bool
	 */
	private function is_mime_type_audio( $attachment ) {
		return strpos( $attachment->post_mime_type, 'audio/' ) !== false;
	}

	/**
	 * @param $type
	 * @param $attachment WP_Post
	 * @param $attachment_url
	 *
	 * @return array
	 */
	private function get_attachment_schema( $type, $attachment, $attachment_url ) {
		$description = $attachment->post_excerpt
			? $attachment->post_excerpt
			: $attachment->post_content;

		return array(
			'@type'       => $type,
			'name'        => $attachment->post_title,
			'description' => wp_strip_all_tags( $description ),
			'uploadDate'  => $attachment->post_date,
			'contentUrl'  => $attachment_url,
		);
	}

	private function add_schema( $schema ) {
		$this->schema[] = $schema;
	}

	/**
	 * @return bool
	 */
	private function is_audio_enabled() {
		return (bool) $this->utils->get_schema_option( 'schema_enable_audio' );
	}

	/**
	 * @return bool
	 */
	private function is_video_enabled() {
		return (bool) $this->utils->get_schema_option( 'schema_enable_video' );
	}
}