<?php

declare(strict_types=1);

namespace Tests\PHPLisp;

use PHPLisp\Executor;
use PHPLisp\IntegerNode;
use PHPLisp\SExprParser;
use PHPLisp\StringNode;
use PHPUnit\Framework\TestCase;

final class InterpreterTest extends TestCase
{
	/**
	 * @var Executor
	 */
	private $executor;

	public function setUp()
	{
		$this->executor = new Executor();
	}

	/**
	 * @dataProvider data_scalars
	 */
	public function test_can_execute_scalars($expected, $node)
	{
		$result = $this->executor->execute([$node]);

		$this->assertEquals($expected, $result);
	}

	public function data_scalars()
	{
		return [
			[5, new IntegerNode(5)],
			['ab', new StringNode('ab')]
		];
	}

	public function test_can_define_a_function()
	{
		$roots = array_map(function($code){ return (new SExprParser())->parse($code); }, [
			'(defun name () 5)',
			'(name 5)'
		]);

		$result = $this->executor->execute($roots);

		$this->assertEquals(5, $result);
	}

	public function test_can_define_a_function_that_takes_an_argument()
	{
		$roots = array_map(function($code){ return (new SExprParser())->parse($code); }, [
			'(defun name (x) x)',
			'(name 4)'
		]);

		$result = $this->executor->execute($roots);

		$this->assertEquals(4, $result);
	}

	public function test_can_execute_factorial()
	{
		$roots = array_map(function($code){ return (new SExprParser())->parse($code); }, [
			'(defun factorial (n)
					  (if (= n 0)
					      1
					      (* n (factorial (- n 1))) ) )',
			'(factorial 4)'
		]);

		$result = $this->executor->execute($roots);

		$this->assertEquals(24, $result);
	}
}