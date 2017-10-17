<?php

declare(strict_types=1);

namespace Tests\PHPLisp;

use PHPLisp\NilNode;
use PHPLisp\FullListNode;
use PHPLisp\IdentifierNode;
use PHPLisp\IntegerNode;
use PHPLisp\SExprParser;
use PHPLisp\StringNode;
use PHPUnit\Framework\TestCase;

final class SExprParserTest extends TestCase
{
	/**
	 * @var SExprParser
	 */
	private $parser;

	public function setUp()
	{
		$this->parser = new SExprParser();
	}

	/**
	 * @dataProvider data_valid_integers
	 *
	 * @param string $value
	 */
	public function test_can_parse_integers(string $value)
	{
		$result = $this->parser->parse($value);

		$this->assertEquals(new IntegerNode((int)$value), $result);
	}

	public function data_valid_integers()
	{
		return [
			['5'],
			[(string)PHP_INT_MAX],
		];
	}

	/**
	 * @param string $expected
	 * @param string $value
	 *
	 * @dataProvider data_valid_strings
	 */
	public function test_can_parse_strings(string $expected, string $value)
	{
		$result = $this->parser->parse($value);

		$this->assertEquals(new StringNode($expected), $result);
	}

	public function data_valid_strings()
	{
		return [
			['a', '"a"'],
			['a"a', '"a\"a"'],
		];
	}

	/**
	 * @param string $expected
	 * @param string $input
	 *
	 * @dataProvider data_valid_lists
	 */
	public function test_can_parse_a_list($expected, string $input)
	{
		$result = $this->parser->parse($input);

		$this->assertEquals($expected, $result);
	}

	public function data_valid_lists()
	{
		return [
			[new NilNode(), '()'],
			[new FullListNode(new IntegerNode(5), new NilNode()), '(5)'],
			[new FullListNode(new IntegerNode(5), new FullListNode(new IntegerNode(4), new NilNode())), '(5 4)'],
			[new FullListNode(new StringNode('A'), new NilNode()), '("A")'],
			[new FullListNode(new IdentifierNode('a'), new NilNode()), '(a)'],
			[new FullListNode(new IdentifierNode('a'), new FullListNode(new FullListNode(new IdentifierNode('a'), new NilNode()), new NilNode())), '(a (a))'],
			[
				$this->arrayToLispList([
					new IdentifierNode('defun'),
					new IdentifierNode('factorial'),
					$this->arrayToLispList([
						new IdentifierNode('n')
					])
				]),
				'(defun factorial (n))'
			]
		];
	}

	/**
	 * @param $expected
	 * @param $input
	 *
	 * @dataProvider data_valid_identifiers
	 */
	public function test_can_parse_an_identifier($expected, $input)
	{
		$result = $this->parser->parse($input);

		$this->assertEquals($expected, $result);
	}

	public function data_valid_identifiers()
	{
		return [
			[new IdentifierNode('test'), 'test'],
			[new IdentifierNode('test-a'), 'test-a'],
			[new IdentifierNode('test-1'), 'test-1'],
			[new IdentifierNode('+'), '+'],
			[new IdentifierNode('-'), '-'],
			[new IdentifierNode('*'), '*'],
			[new IdentifierNode('/'), '/'],
			[new IdentifierNode('='), '='],

		];
	}

	private function arrayToLispList(array $array)
	{
		$lispList = new NilNode();

		foreach (array_reverse($array) as $item) {
			$lispList = new FullListNode($item, $lispList);
		}

		return $lispList;
	}

	public function test_can_handle_line_breaks_in_lists()
	{
		$result = $this->parser->parse("(a\nb)");

		$this->assertEquals(
			new FullListNode(new IdentifierNode('a'), new FullListNode(new IdentifierNode('b'), new NilNode())),
			$result
		);
	}

	public function test_can_parse_factorial_function()
	{
		$function = '(defun factorial (n)
					  (if (= n 0)
					      1
					      (* n (factorial (- n 1))) ) )
					';

		$argumentsList = $this->arrayToLispList(
			[
				new IdentifierNode('n'),
			]
		);

		$bodyList = $this->arrayToLispList(
			[
				new IdentifierNode('*'),
				new IdentifierNode('n'),
				$this->arrayToLispList([
					new IdentifierNode('factorial'),
					$this->arrayToLispList([
						new IdentifierNode('-'),
						new IdentifierNode('n'),
						new IntegerNode(1)
					])
				])
			]
		);

		$ifList = $this->arrayToLispList(
			[
				new IdentifierNode('if'),
				$this->arrayToLispList(
					[
						new IdentifierNode('='),
						new IdentifierNode('n'),
						new IntegerNode(0),
					]
				),
				new IntegerNode(1),
				$bodyList,
			]
		);

		$result = $this->arrayToLispList(
			[
				new IdentifierNode('defun'),
				new IdentifierNode('factorial'),
				$argumentsList,
				$ifList,
			]
		);

		$this->assertEquals($result, $this->parser->parse($function));
	}
}
