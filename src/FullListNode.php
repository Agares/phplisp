<?php

declare(strict_types=1);

namespace PHPLisp;

final class FullListNode implements ListNode
{
	/**
	 * @var
	 */
	private $car;

	/**
	 * @var ListNode
	 */
	private $cdr;

	public function __construct($car, ListNode $cdr)
	{
		$this->car = $car;
		$this->cdr = $cdr;
	}

	public function __toString()
	{
		return '(' . $this->car . ' ' . ($this->cdr instanceof FullListNode ? $this->cdr->car . ' ' . $this->cdr->cdr : $this->cdr) . ')';
	}

	public function car() { return $this->car; }
	public function cdr() { return $this->cdr; }
}