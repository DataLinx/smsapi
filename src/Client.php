<?php
namespace DataLinx\SMSAPI;

use DataLinx\SMSAPI\Exception\APIError;
use DataLinx\SMSAPI\Exception\ValidationException;
use Exception;

class Client {

	const API_URL = 'https://www.smsapi.si/';

	private $username;

	private $password;

	/**
	 * Sender number
	 *
	 * @var string
	 */
	private $senderNumber;

	/**
	 * Sender ID
	 *
	 * @var string
	 */
	private $senderId;

    /**
     * Enable unicode SMS content
     *
     * @var bool
     */
    private $unicode;

	private $ch;

	/**
	 * Create client instance
	 *
	 * @param string $username API username
	 * @param string $password API password
	 * @param string $senderNumber Sender (from) number - required for sending messages
	 */
	public function __construct($username, $password, $senderNumber)
	{
		$this->username = $username;
		$this->password = $password;
		$this->senderNumber = $senderNumber;
	}

	public function __destruct()
	{
		if ($this->ch) {
			curl_close($this->ch);
		}
	}

	public function getUsername()
	{
		return $this->username;
	}

	public function getPassword()
	{
		return $this->password;
	}

	public function setUsername($value)
	{
		$this->username = $value;

		return $this;
	}

	public function setPassword($value)
	{
		$this->password = $value;

		return $this;
	}

	/**
	 * Get sender (from) number
	 *
	 * @return string
	 */
	public function getSenderNumber()
	{
		return $this->senderNumber;
	}

	/**
	 * Set sender (from) number
	 *
	 * @param string $senderNumber Valid mobile number
	 * @return $this
	 */
	public function setSenderNumber($senderNumber)
	{
		$this->senderNumber = $senderNumber;
		
		return $this;
	}

	/**
	 * Get sender ID
	 *
	 * @return string
	 */
	public function getSenderId()
	{
		return $this->senderId;
	}

	/**
	 * Set (optional) Sender ID to use for sending.<br/>
	 * Sender ID must be set up and confirmed on smsapi.si.
	 *
	 * @param string $senderId Sender ID
	 * @return $this
	 */
	public function setSenderId($senderId)
	{
		$this->senderId = $senderId;

		return $this;
	}

    /**
     * Is unicode content enabled?
     *
     * @return bool
     */
    public function isUnicode()
    {
        return $this->unicode;
    }

    /**
     * Enable/disable unicode content
     *
     * @param bool $unicode
     * @return $this
     */
    public function setUnicode($unicode)
    {
        $this->unicode = $unicode;

        return $this;
    }

	/**
	 * Send SMS message
	 *
	 * @param \DataLinx\SMSAPI\Message $message Message object
	 * @return \DataLinx\SMSAPI\Response Response object
	 * @throws APIError|ValidationException
	 */
	public function send(Message $message)
	{
		$this->_validate('senderNumber');

		$message->validate();

		$data = array(
			'from' => $this->cleanNumber($this->getSenderNumber()),
			'to' => $this->cleanNumber($message->getTo()),
			'cc' => $message->getCountryCode(),
			'm' => trim($message->getContent()),
		);

		if ($this->getSenderId()) {
			$data['sid'] = 1;
			$data['sname'] = $this->getSenderId();
		}

        if ($this->isUnicode()) {
            $data['unicode'] = 1;
        }

		$rs = trim($this->_send('poslji-sms', $data));

		if (substr($rs, 0, 2) === '-1') {
			$data = explode('##', $rs);

			throw new APIError((int)$data[1]);
		}

		return new Response($rs);
	}

	public function price()
	{
		$this->_validate();

		$price = trim($this->_send('dobi-ceno'), '# ');

		if (strpos($price, '##') !== FALSE) {
			return array_map('floatval', explode('##', $price));
		} elseif (is_numeric($price)) {
			return (float)$price;
		}
		
		throw new Exception('API returned non-numeric value');
	}

	public function creditStatus()
	{
		$this->_validate();

		$rs = $this->_send('preveri-stanje-kreditov');

		if (is_numeric($rs)) {
			return (int)$rs;
		}

		throw new Exception('API returned non-numeric value');
	}

	/**
	 * Clean the mobile number of any invalid characters.<br/>
	 * Removes any characters that are not digits. This will be automatically done for outgoing messages, you do not need to do it manually.
	 *
	 * @param string $number Number to clean
	 * @return string
	 */
	public function cleanNumber($number)
	{
		return preg_replace('/\D/', '', $number);
	}

	private function _send($endpoint, $data = array())
	{
		$data['un'] = $this->username;
		$data['ps'] = $this->password;

		if ( ! isset($this->ch)) {
			// Initialize the cURL handle
			$this->ch = curl_init();

			curl_setopt_array($this->ch, array(
				CURLOPT_AUTOREFERER => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POST => true,
				CURLOPT_FAILONERROR => true,
			));
		}

		curl_setopt_array($this->ch, array(
			CURLOPT_URL => self::API_URL . $endpoint,
			CURLOPT_POSTFIELDS => $data,
		));

		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);

		$response = curl_exec($this->ch);

		if ( ! $response) {
			throw new Exception('cURL request failed! cURL error: '. curl_error($this->ch) .', HTTP status: '. curl_getinfo($this->ch, CURLINFO_HTTP_CODE), curl_errno($this->ch));
		}

		return $response;
	}

	/**
	 * Run the pre-sending request validation procedure
	 *
	 * @throws ValidationException
	 */
	private function _validate()
	{
		$props = array('username', 'password');

		$args = func_get_args();

		if (is_array($args)) {
			$props += $args;
		}

		foreach ($props as $p) {
			if (empty($this->$p)){
				throw new ValidationException("Client property '$p' is required", ValidationException::CODE_REQUIRED, $p);
			}
		}
	}
}
