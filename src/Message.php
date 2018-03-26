<?php
namespace DataLinx\SMSAPI;

use DataLinx\SMSAPI\Exception\ValidationException;

class Message {

	private $to;

	private $content;

	/**
	 * Country code
	 *
	 * @var string
	 */
	private $countryCode;

	public function __construct($to, $content, $countryCode = '00386')
	{
		$this->to = $to;
		$this->content = $content;
		$this->countryCode = $countryCode;
	}

	public function getTo()
	{
		return $this->to;
	}

	public function getContent()
	{
		return $this->content;
	}

	public function getCountryCode()
	{
		return $this->countryCode;
	}

	public function setTo($to)
	{
		$this->to = $to;

		return $this;
	}

	public function setContent($message)
	{
		$this->content = $message;

		return $this;
	}

	public function setCountryCode($countryCode)
	{
		$this->countryCode = $countryCode;
		
		return $this;
	}

	/**
	 * Run the pre-sending validation procedure
	 *
	 * @throws ValidationException
	 */
	public function validate()
	{
		$props = array('to', 'content', 'countryCode');

		foreach ($props as $p) {
			if (empty($this->$p)){
				throw new ValidationException("Message property '$p' is required", ValidationException::CODE_REQUIRED, $p);
			}
		}

		if ( ! preg_match('/^(\d{3}|\d{5})$/', $this->countryCode)) {
			throw new ValidationException("Country code must be exactly 3 or 5 digits long", ValidationException::CODE_UNEXP_FORMAT, $p);
		}
	}
}
