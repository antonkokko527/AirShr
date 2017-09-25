<?php

namespace Core\Audio\Exception;

class InvalidMatchException extends \RuntimeException {
	
	const INVALID = 1;

	public function __construct(\Exception $previous = NULL)
	{
		parent::__construct("An irreconcilable match time exception.", self::INVALID, $previous);
	}

}
