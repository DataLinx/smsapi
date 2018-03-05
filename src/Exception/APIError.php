<?php
namespace DataLinx\SMSAPI\Exception;

/**
 * API error exception
 */
class APIError extends \Exception {

	/**
	 * Create instance
	 *
	 * @param int $code Error code
	 */
	public function __construct($code)
	{
		parent::__construct($this->_prepMessage($code), $code);
	}

	/**
	 * Prepare message
	 *
	 * @param int $code Error code
	 * @return string
	 */
	private function _prepMessage($code)
	{
		switch ($code) {
			case 1:
				return 'API username/password invalid';
			case 2:
				return 'Message content too long or empty';
			case 3:
				return 'Sender number invalid';
			case 4:
				return 'Recipient number invalid';
			case 5:
				return 'Insufficient credit';
			case 6:
				return 'Server error';
			case 7:
				return 'Sender number not registered.';
			case 8:
				return 'User reference invalid';
			case 9:
				return 'Country code invalid';
			case 10:
				return 'Sender ID not confirmed';
			case 11:
				return 'Country code not supported';
			case 12:
				return 'Sender number not confirmed';
			case 13:
				return 'Recipient number or country does not support MMS';
			case 14:
				return 'MMS MIME type not supported';
			case 15:
				return 'MMS file URL unreachable';
			case 16:
				return 'MMS too large (max 500kB)';
		}

		return 'Unknown error (code: '. $code .')';
	}
}
