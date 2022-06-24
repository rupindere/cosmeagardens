<?php

/**
 * Class Smartcrawl_Controller_Lighthouse
 *
 * TODO: add more checks to lighthouse
 */
class Smartcrawl_Controller_Lighthouse extends Smartcrawl_Base_Controller {
	const ERROR_RESULT_NOT_FOUND = 30;
	private static $_instance;

	public static function get() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	protected function init() {
		add_action( 'wp_ajax_wds-lighthouse-run', array( $this, 'run_lighthouse' ) );
		add_action( 'wp_ajax_wds-lighthouse-start-test', array( $this, 'start_lighthouse_test' ) );
		add_action( 'wds_plugin_update', array( $this, 'apply_checkup_schedule_to_lighthouse' ) );
		add_action( 'wds_admin_notices', array( $this, 'checkup_removal_notice' ) );
	}

	public function checkup_removal_notice() {
		$key = 'wds_checkup_removed_218';
		$dismissed_messages = get_user_meta( get_current_user_id(), 'wds_dismissed_messages', true );
		$is_message_dismissed = smartcrawl_get_array_value( $dismissed_messages, $key ) === true;
		$is_version_218 = version_compare( SMARTCRAWL_VERSION, '2.18.0', '=' );
		if (
			$is_message_dismissed ||
			! $is_version_218 ||
			! current_user_can( 'manage_options' )
		) {
			return;
		}

		$health_admin_url = Smartcrawl_Settings_Admin::admin_url( Smartcrawl_Settings::TAB_HEALTH );
		?>
		<div class="notice-info notice is-dismissible wds-native-dismissible-notice"
		     data-message-key="<?php echo esc_attr( $key ); ?>">
			<p style="margin-bottom: 15px;">
				<?php printf(
					esc_html__( "Heads up, %s! SmartCrawl’s SEO Checkup functionality has been removed in favor of SEO Audits powered by Google Lighthouse. We’ve automatically migrated your SEO Checkup settings to Lighthouse SEO Audit.", 'wds' ),
					Smartcrawl_Model_User::current()->get_first_name()
				); ?>
			</p>
			<a href="<?php echo esc_attr( $health_admin_url ); ?>"
			   class="button button-primary">
				<?php esc_html_e( 'Check Out SEO Audits', 'wds' ); ?>
			</a>
			<a href="#" class="wds-native-dismiss"><?php esc_html_e( 'Dismiss', 'wds' ); ?></a>
			<p></p>
		</div>
		<?php
	}

	public function start_lighthouse_test() {
		$request_data = $this->get_request_data();
		if ( empty( $request_data ) ) {
			wp_send_json_error();
		}
		/**
		 * @var Smartcrawl_Lighthouse_Service $lighthouse
		 */
		$lighthouse = Smartcrawl_Service::get( Smartcrawl_Service::SERVICE_LIGHTHOUSE );
		$lighthouse->clear_last_report();
		$lighthouse->stop();
		$lighthouse->start();

		wp_send_json_success();
	}

	public function run_lighthouse() {
		$request_data = $this->get_request_data();
		if ( empty( $request_data ) ) {
			wp_send_json_error();
		}

		/**
		 * @var Smartcrawl_Lighthouse_Service $lighthouse
		 */
		$lighthouse = Smartcrawl_Service::get( Smartcrawl_Service::SERVICE_LIGHTHOUSE );
		$start_time = $lighthouse->get_start_time();
		if ( ! $start_time ) {
			$lighthouse->start();
			wp_send_json_success( array( 'finished' => false ) );
		}

		$now = current_time( 'timestamp' );
		if ( $now < $start_time + 15 ) {
			// Not enough time has passed, buy more time
			wp_send_json_success( array( 'finished' => false ) );
		}

		if ( $now >= $start_time + 90 ) {
			// Too much time has passed, something might be wrong, force user to start over
			$lighthouse->stop();
			$lighthouse->clear_last_report();
			$lighthouse->set_error(
				'timeout',
				esc_html__( 'We were not able to get results for your site', 'wds' )
			);
			wp_send_json_success( array( 'finished' => true ) );
		}

		$lighthouse->refresh_report();
		$last_report = $lighthouse->get_last_report();
		if (
			$last_report->get_error_code() === self::ERROR_RESULT_NOT_FOUND
			|| ! $last_report->is_fresh()
		) {
			// Let's wait a little longer for the results to become available
			wp_send_json_success( array( 'finished' => false ) );
		}

		$lighthouse->stop();
		wp_send_json_success( array( 'finished' => true ) );
	}

	private function get_request_data() {
		return isset( $_POST['_wds_nonce'] ) && wp_verify_nonce( $_POST['_wds_nonce'], 'wds-lighthouse-nonce' )
			? $_POST
			: array();
	}

	/**
	 * TODO: remove when enough time has passed
	 */
	public function apply_checkup_schedule_to_lighthouse() {
		$version_with_checkup = '2.17.1';
		$last_version = Smartcrawl_Loader::get_last_version();
		$last_version_had_checkup = version_compare( $last_version, $version_with_checkup, '<=' );
		$option_id = 'wds_checkup_removed';
		$schedule_already_applied = get_option( $option_id, false );
		$checkup_options = get_option( 'wds_checkup_options' );

		if (
			$last_version_had_checkup &&
			! $schedule_already_applied &&
			! empty( $checkup_options )
		) {
			$test_mode_was_checkup = smartcrawl_get_array_value( get_option( 'wds_health_options' ), 'health-test-mode' ) === 'seo-checkup';
			if ( $test_mode_was_checkup ) {
				$cron_enabled = (bool) smartcrawl_get_array_value( $checkup_options, 'checkup-cron-enable' );
				if ( $cron_enabled ) {
					// Checkup cron was enabled in the last version so from now on we want the lighthouse report to be sent to all the checkup recipients

					$reporting_frequency = smartcrawl_get_array_value( $checkup_options, 'checkup-frequency' );
					$reporting_dow = smartcrawl_get_array_value( $checkup_options, 'checkup-dow' );
					$reporting_tod = smartcrawl_get_array_value( $checkup_options, 'checkup-tod' );
					$recipients = $this->get_checkup_recipients();

					$lighthouse_options = wp_parse_args( array(
						Smartcrawl_Lighthouse_Options::CRON_ENABLE                 => true,
						Smartcrawl_Lighthouse_Options::REPORTING_FREQUENCY         => $reporting_frequency,
						Smartcrawl_Lighthouse_Options::REPORTING_DOW               => $reporting_dow,
						Smartcrawl_Lighthouse_Options::REPORTING_TOD               => $reporting_tod,
						Smartcrawl_Lighthouse_Options::RECIPIENTS                  => $recipients,
						Smartcrawl_Lighthouse_Options::REPORTING_CONDITION_ENABLED => false,
						Smartcrawl_Lighthouse_Options::REPORTING_DEVICE            => 'both',
					), Smartcrawl_Lighthouse_Options::get_options() );
				} else {
					// No emails were being sent in the last version so we don't want lighthouse emails to start up suddenly, disable lighthouse

					$lighthouse_options = wp_parse_args( array(
						Smartcrawl_Lighthouse_Options::CRON_ENABLE => false,
					), Smartcrawl_Lighthouse_Options::get_options() );
				}
				update_option( Smartcrawl_Lighthouse_Options::OPTION_ID, $lighthouse_options );
			}

			update_option( $option_id, true );
		}
	}

	public function get_checkup_recipients() {
		$email_recipients = array();
		$options = Smartcrawl_Settings::get_specific_options( 'wds_checkup_options' );
		$new_recipients = empty( $options['checkup-email-recipients'] )
			? array()
			: $options['checkup-email-recipients'];
		$old_recipients = empty( $options['email-recipients'] )
			? array()
			: $options['email-recipients'];

		foreach ( $old_recipients as $user_id ) {
			if ( ! is_numeric( $user_id ) ) {
				continue;
			}
			$old_recipient = $this->get_email_recipient( $user_id );
			if ( $this->recipient_exists( $old_recipient, $new_recipients ) ) {
				continue;
			}

			$email_recipients[] = $old_recipient;
		}

		return array_merge(
			$email_recipients,
			$new_recipients
		);
	}

	private function get_email_recipient( $user_id = false ) {
		if ( $user_id ) {
			$user = Smartcrawl_Model_User::get( $user_id );
		} else {
			$user = Smartcrawl_Model_User::owner();
		}

		return array(
			'name'  => $user->get_display_name(),
			'email' => $user->get_email(),
		);
	}

	private function recipient_exists( $recipient, $recipient_array ) {
		$emails = array_column( $recipient_array, 'email' );
		$needle = (string) smartcrawl_get_array_value( $recipient, 'email' );

		return in_array( $needle, $emails, true );
	}
}