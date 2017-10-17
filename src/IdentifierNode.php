<?php

declare(strict_types=1);

namespace PHPLisp;

final class IdentifierNode
{
	/**
	 * @var string
	 */
	private $name;

	public function __construct(string $name)
	{
		$this->name = $name;
	}

	public function __toString()
	{
		return $this->name;
	}
}