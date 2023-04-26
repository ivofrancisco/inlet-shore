<?php

namespace WP_Defender\Component\IP;

use WP_Defender\Behavior\WPMUDEV;
use WP_Defender\Component;
use WP_Defender\Traits\IP;
use WP_Defender\Model\Setting\Blacklist_Lockout;

class Global_IP extends Component {

	use IP;

	/**
	 * @var array
	 */
	private $allow_list;

	/**
	 * @var array
	 */
	private $block_list;

	/**
	 * @var array Fetches data from HUB API service method.
	 */
	private $global_list = [];

	/**
	 * @var WPMUDEV
	 */
	private $wpmudev;

	/**
	 * @var bool Check is global IP setting enabled.
	 */
	private $is_global_ip_enabled;

	public function __construct() {
		/**
		 * @var WPMUDEV
		 */
		$this->wpmudev = wd_di()->get( WPMUDEV::class );

		$this->set_global_list();
	}

	/**
	 * Return global ip allow list from HUB.
	 */
	public function allow_list(): array {
		$this->allow_list = $this->global_list['allow_list'] ?? [];

		return $this->allow_list;
	}

	/**
	 * Return global ip block list from HUB.
	 */
	public function block_list(): array {
		$this->block_list = $this->global_list['block_list'] ?? [];

		return $this->block_list;
	}

	/**
	 * Verify is given ip exists in global IP allow list.
	 *
	 * @param string $ip ip address.
	 *
	 * @return bool if exists return true else false.
	 */
	public function is_ip_allowed( string $ip ): bool {
		return $this->is_ip_in_format( $ip, $this->allow_list() );
	}

	/**
	 * Verify is given ip exists in global IP block list.
	 *
	 *  @param string $ip ip address.
	 *
	 * @return bool if exists return true else false.
	 */
	public function is_ip_blocked( string $ip ): bool {
		return $this->is_ip_in_format( $ip, $this->block_list() );
	}

	/**
	 * Verify is global ip settings enabled.
	 *
	 * @return bool True for enabled or false for disabled.
	 */
	public function is_global_ip_enabled(): bool {
		/**
		 * @var Blacklist_Lockout
		 */
		$blacklist_lockout = wd_di()->get( Blacklist_Lockout::class );

		$this->is_global_ip_enabled = $blacklist_lockout->global_ip_list;

		return $this->is_global_ip_enabled;
	}

	private function set_global_list(): void {
		if ( ! $this->is_global_ip_enabled() ) {
			return;
		}

		$global_ip_list = $this->wpmudev->get_global_ip_list();

		$is_valid = ! is_wp_error( $global_ip_list ) &&
			is_array( $global_ip_list ) &&
			isset( $global_ip_list['allow_list'], $global_ip_list['block_list'] );

		if ( $is_valid ) {
			$this->global_list = $global_ip_list;
		}
	}

}
