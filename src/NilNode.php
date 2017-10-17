<?php

declare(strict_types=1);

namespace PHPLisp;

final class NilNode implements ListNode
{
	public function __toString()
	{
		return '<NIL>';
	}
}