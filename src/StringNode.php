<?php

declare(strict_types=1);

namespace PHPLisp;

final class StringNode
{
	/**
	 * @var string
	 */
	private $value;

	public function __construct(string $value)
	{
		$this->value = $value;
	}

	public function value(): string
	{
		return $this->value;
	}
}