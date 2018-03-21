<?php
namespace DataLinx\SMSAPI\Exception;

/**
 * Exception thrown on request pre-sending validation problems
 */
class ValidationException extends \Exception {

	const CODE_REQUIRED = 1;
	const CODE_UNEXP_FORMAT = 2;

	/**
	 * Associated input name
	 *
	 * @var string
	 */
	private $inputName;

	/**
	 * Create instance
	 *
	 * @param string $message Exception message (English)
	 * @param int $code Exception type
	 * @param string $inputName Associated input name
	 */
	public function __construct($message, $code, $inputName = NULL)
	{
		parent::__construct($message, $code);

		$this->inputName = $inputName;
	}

	/**
	 * Get associated input name
	 *
	 * @return string Input name
	 */
	public function getInputName()
	{
		return $this->inputName;
	}
}
