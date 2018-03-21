<?php
namespace DataLinx\SMSAPI;

class Response {

	private $id;

	private $price;

	private $from;

	private $to;

	public function __construct($str)
	{
		// Success format:
		// ID		Price Sender	 Recipient
		// 3706989##0.02##070566062##070566062

		if (empty($str) OR strpos($str, '##') === FALSE) {
			throw new Exception('Unexpected content in response: '. substr($str, 0, 200));
		}

		$data = explode('##', $str);

		$this->id = $data[0];
		$this->price = (float)$data[1];
		$this->from = $data[2];
		$this->to = $data[3];
	}
}
