<?php

declare(strict_types=1);

namespace PHPLisp;

final class Executor
{
	private $functions = [];

	public function execute(array $roots)
	{
		foreach($roots as $node) {
			if ($node instanceof IntegerNode) {
				return $node->value();
			}

			if ($node instanceof StringNode) {
				return $node->value();
			}

			if($node instanceof FullListNode) {
				if($node->car() instanceof IdentifierNode) {
					$ret = $this->executeFunction($node->car()->name(), $node->cdr());
					if($ret !== null) {
						return $ret;
					}
				}
			}
		}
	}

	private function executeFunction($name, ListNode $arguments)
	{
		if($name === 'defun') {
			$this->functions[$arguments->car()->name()] = [
				$this->listToArray($arguments->cdr()->car()),   // args
				$arguments->cdr()->cdr(),   // body
			];

			return null;
		} elseif($name === 'if') {
			$args = $this->listToArray($arguments);

			if($this->execute([$args[0]])) {
				return $this->execute([$args[1]]);
			}

			return $this->execute([$args[2]]);
		} elseif($name === '=') {
			$args = $this->listToArray($arguments);

			return $this->execute([$args[0]]) == $this->execute([$args[1]]);
		} elseif($name === '*') {
			$args = $this->listToArray($arguments);

			return $this->execute([$args[0]]) * $this->execute([$args[1]]);
		} elseif($name === '-') {
			$args = $this->listToArray($arguments);

			return $this->execute([$args[0]]) - $this->execute([$args[1]]);
		} else {
			$func = $this->functions[$name];

			$executedArgs = array_map(function($arg) {
				$ex = $this->execute([$arg]);

				if(is_int($ex)) {
					return new IntegerNode($ex);
				} else {
					return new StringNode($ex);
				}
			}, $this->listToArray($arguments));

			$bodyWithArgs = $this->fillBodyArguments($func[0], $func[1], $executedArgs);

			return $this->execute([$bodyWithArgs->car()]);
		}
	}

	private function fillBodyArguments(array $argumentIdentifiers, $body, $arguments)
	{
		$identifierNames = array_map(function(IdentifierNode $x) { return $x->name(); }, $argumentIdentifiers);

		if($body instanceof NilNode) {
			return $body;
		}

		if($body instanceof FullListNode) {
			$car = $body->car();
			$cdr = $body->cdr();

			if($car instanceof IdentifierNode) {
				if(($argNum = array_search($car->name(), $identifierNames, false)) !== false) {
					$car = $arguments[$argNum];
				}
			} elseif($car instanceof FullListNode) {
				$car = $this->fillBodyArguments($argumentIdentifiers, $car, $arguments);
			}

			return new FullListNode($car, $this->fillBodyArguments($argumentIdentifiers, $cdr, $arguments));
		}

		return $body;
	}

	private function listToArray(ListNode $node)
	{
		if(!$node instanceof FullListNode) {
			return [];
		}

		return array_merge([$node->car()], $this->listToArray($node->cdr()));
	}
}