<?php

declare(strict_types=1);

namespace PHPLisp;

final class SExprParser
{
	public function parse(string $value)
	{
		$currentLists = [];
		$currentListLevel = 0;

		for($position = 0, $positionMax = strlen($value); $position < $positionMax; $position++) {
			$currentCharacter = $value[$position];

			if($currentCharacter === '"') {
				$stringValue = '';
				$position++;

				do {
					if($value[$position] === '\\' && $value[$position+1] === '"') {
						$position++;
					}

					$stringValue .= $value[$position];

					$position++;
				} while($value[$position] !== '"');

				if($currentListLevel > 0) {
					$currentLists[$currentListLevel][] = new StringNode($stringValue);
				} else {
					return new StringNode($stringValue);
				}
			}

			if($currentCharacter === '(') {
				$currentListLevel++;
				$currentLists[$currentListLevel] = [];
			}

			if($currentListLevel > 0 && $currentCharacter === ')') {
				$currentListLevel--;

				if($currentListLevel === 0) {
					return $this->toLispList($currentLists[$currentListLevel+1]);
				} else {
					$currentLists[$currentListLevel][] = $this->toLispList($currentLists[$currentListLevel+1]);
				}
			}

			if(ctype_digit($currentCharacter)) {
				$intValue = '';
				do {
					$intValue .= $value[$position];
					$position++;
				} while($position < strlen($value) && ctype_digit($value[$position]));

				if($currentListLevel > 0) {
					$position--;

					$currentLists[$currentListLevel][] = new IntegerNode((int)$intValue);
				} else {
					return new IntegerNode((int)$intValue);
				}
			}

			if($this->isValidFirstIdentifierCharacter($currentCharacter)) {
				$identifierName = '';

				do {
					$identifierName .= $value[$position];
					$position++;
				} while($position < strlen($value) && (ctype_alnum($value[$position]) || $value[$position] === '-'));

				$position--;

				$node = new IdentifierNode($identifierName);

				if($currentListLevel > 0) {
					$currentLists[$currentListLevel][] = $node;
				} else {
					return $node;
				}
			}
		}
	}

	private function isValidFirstIdentifierCharacter($x): bool
	{
		return ctype_alpha($x) || in_array($x, ['+', '-', '*', '/', '='], true);
	}

	private function toLispList(array $list): ListNode
	{
		$result = new NilNode();

		foreach(array_reverse($list) as $item) {
			$result = new FullListNode($item, $result);
		}

		return $result;
	}
}