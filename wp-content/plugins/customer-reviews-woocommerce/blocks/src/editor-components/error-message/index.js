/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { escapeHTML } from '@wordpress/escape-html';

const getErrorMessage = ( { message, type } ) => {
	if ( ! message ) {
		return __(
			'An unknown error occurred which prevented the block from being updated.',
			'woo-gutenberg-products-block'
		);
	}

	if ( type === 'general' ) {
		return (
			<span>
				{ __(
					'The following error was returned',
					'woo-gutenberg-products-block'
				) }
				<br />
				<code>{ escapeHTML( message ) }</code>
			</span>
		);
	}

	if ( type === 'api' ) {
		return (
			<span>
				{ __(
					'The following error was returned from the API',
					'woo-gutenberg-products-block'
				) }
				<br />
				<code>{ escapeHTML( message ) }</code>
			</span>
		);
	}

	return message;
};

const ErrorMessage = ( { error } ) => (
	<div className="wc-block-error-message">{ getErrorMessage( error ) }</div>
);

export default ErrorMessage;
