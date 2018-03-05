<?php
namespace DataLinx\SMSAPI;

class Message {

	private $from;

	private $to;

	private $content;

	/**
	 * Country code
	 *
	 * @var string
	 */
	private $countryCode;

	public function __construct($from, $to, $content, $countryCode = '00386')
	{
		$this->from = $from;
		$this->to = $to;
		$this->content = $content;
		$this->countryCode = $countryCode;
	}

	public function getFrom()
	{
		return $this->from;
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

	public function setFrom($from)
	{
		$this->from = $from;

		return $this;
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

	public function validate()
	{

	}
}
