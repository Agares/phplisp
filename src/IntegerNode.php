<?php

declare(strict_types=1);

namespace PHPLisp;

final class IntegerNode
{
	/**
	 * @var int
	 */
	private $value;

	public function __construct(int $value)
	{
		$this->value = $value;
	}

	public function __toString()
	{
		return (string)$this->value;
	}
}