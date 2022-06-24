<?php

abstract class Smartcrawl_Readability_Formula {
	abstract public function __construct( Smartcrawl_String $string, $language_code );

	/**
	 * @return int
	 */
	abstract public function get_score();
}