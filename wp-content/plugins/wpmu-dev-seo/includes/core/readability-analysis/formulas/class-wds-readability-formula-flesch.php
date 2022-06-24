<?php

class Smartcrawl_Readability_Formula_Flesch extends Smartcrawl_Readability_Formula {
	private $languages = array(
		'cs' => array(
			'base' => 206.835,
			'asl'  => 1.388,
			'asw'  => 65.090,
		),
		'de' => array(
			'base' => 180,
			'asl'  => 1,
			'asw'  => 58.5,
		),
		'en' => array(
			'base' => 206.835,
			'asl'  => 1.015,
			'asw'  => 84.6,
		),
		'fr' => array(
			'base' => 207,
			'asl'  => 1.015,
			'asw'  => 73.6,
		),
		'nl' => array(
			'base' => 206.84,
			'asl'  => 0.93,
			'asw'  => 77,
		),
		'it' => array(
			'base' => 217,
			'asl'  => 1.3,
			'asw'  => 60,
		),
		'ru' => array(
			'base' => 206.835,
			'asl'  => 1.3,
			'asw'  => 60.1,
		),
		'es' => array(
			'base' => 206.84,
			'asl'  => 1.02,
			'asw'  => 60,
		),
	);

	/**
	 * @var Smartcrawl_String
	 */
	private $string;
	/**
	 * @var string
	 */
	private $language_code;

	public function __construct( Smartcrawl_String $string, $language_code ) {
		$this->string = $string;
		$this->language_code = $language_code;
	}

	private function get_language() {
		return smartcrawl_get_array_value(
			$this->languages,
			$this->language_code
		);
	}

	public function is_language_supported() {
		return ! empty( $this->get_language() );
	}

	public function get_score() {
		$language = $this->get_language();

		if ( empty( $language ) ) {
			return false;
		}

		return $this->calculate_score(
			$language['base'],
			$language['asl'],
			$language['asw']
		);
	}

	protected function calculate_score( $base, $sentence_length_weight, $syllable_weight ) {
		$sentence_count = $this->string->get_sentence_count();
		$word_count = $this->string->get_word_count();
		$syllable_count = $this->string->get_syllable_count();

		if ( $sentence_count > $word_count || $word_count > $syllable_count ) {
			return false;
		}

		if ( ! $sentence_count || ! $word_count ) {
			return false;
		}

		$average_sentence_length = $word_count / $sentence_count;
		$average_syllables_per_word = $syllable_count / $word_count;
		$score = $base
		         - ( $sentence_length_weight * $average_sentence_length )
		         - ( $syllable_weight * $average_syllables_per_word );

		return intval( round( $score ) );
	}
}