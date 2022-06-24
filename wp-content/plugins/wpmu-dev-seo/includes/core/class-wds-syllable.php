<?php

use Smartcrawl_Vendor\Vanderlee\Syllable\Syllable;

class Smartcrawl_Syllable {
	private $language_code;
	/**
	 * @var Syllable
	 */
	private $helper;
	/**
	 * @var float[]
	 */
	private $syllable_offsets = array(
		'es' => 0.857616824,
	);

	public function __construct( $language_code ) {
		$language_code = $language_code === 'en'
			? 'en-us'
			: $language_code;

		$this->language_code = $language_code;

		if ( $this->is_language_supported() ) {
			$this->helper = new Syllable( $language_code );
			$this->helper->setCache();
		}
	}

	public function count_syllables( $string ) {
		if ( empty( $string ) || ! $this->helper ) {
			return 0;
		}

		$syllable_count = $this->helper->countSyllablesText( $string );
		if ( $syllable_count < 0 ) {
			return 0;
		}

		return $this->adjust_syllable_count( $syllable_count );
	}

	/**
	 * The syllable counting library we are using is not perfect, so we have to make slight adjustments to the syllable count.
	 *
	 * For example for 'es', the syllable count returned by an established library (https://github.com/shivam5992/textstat/)
	 * is around 85% of the count returned by our library. So we multiply our value with approximately 0.85 before feeding it
	 * to readability formulas.
	 *
	 * This is not ideal, but it's faster than developing a better syllable counting library from scratch ;)
	 *
	 * @see https://github.com/shivam5992/textstat/
	 *
	 * @param $unadjusted_count
	 *
	 * @return int
	 */
	private function adjust_syllable_count( $unadjusted_count ) {
		$offset = smartcrawl_get_array_value(
			$this->syllable_offsets,
			$this->language_code
		);
		$offset = empty( $offset ) ? 1 : $offset;
		$adjusted = $unadjusted_count * $offset;

		return intval( $adjusted );
	}

	public function is_language_supported() {
		$vendor = SMARTCRAWL_VENDOR_PREFIXED_DIR;
		$lang = $this->language_code;
		$lang_file_path = "$vendor/syllable/languages/hyph-$lang.tex";

		return file_exists( $lang_file_path );
	}
}