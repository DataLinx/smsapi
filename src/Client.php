<?php
namespace DataLinx\SMSAPI;

use DataLinx\SMSAPI\Exception\APIError;

class Client {

	const API_URL = 'https://www.smsapi.si/';

	private $username;

	private $password;

	/**
	 * Sender ID
	 *
	 * @var string
	 */
	private $senderId;

	private $ch;

	public function __construct($username, $password)
	{
		$this->username = $username;
		$this->password = $password;
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
	 * Send SMS message
	 *
	 * @param \DataLinx\SMSAPI\Message $message Message object
	 * @return \DataLinx\SMSAPI\Response Response object
	 * @throws APIError|Exception\ValidationException
	 */
	public function send(Message $message)
	{
		$message->validate();

		$data = array(
			'from' => $message->getFrom(),
			'to' => $message->getTo(),
			'cc' => $message->getCountryCode(),
			'm' => trim($message->getContent()),
		);

		if ($this->getSenderId()) {
			$data['sid'] = 1;
			$data['sname'] = $this->getSenderId();
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
		$rs = $this->_send('preveri-stanje-kreditov');

		if (is_numeric($rs)) {
			return (int)$rs;
		}

		throw new Exception('API returned non-numeric value');
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
}
