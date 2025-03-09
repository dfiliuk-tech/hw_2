<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use App\GOF\Adapter\IntegerStack;
use App\GOF\Adapter\ASCIIStack;
use App\GOF\Adapter\IntegerToASCIIAdapter;
use App\GOF\Adapter\ASCIIToIntegerAdapter;

// Demonstrate IntegerStack
echo "=== IntegerStack ===\n";
$integerStack = new IntegerStack();
$integerStack->push(65); // ASCII for 'A'
$integerStack->push(66); // ASCII for 'B'
$integerStack->push(67); // ASCII for 'C'

echo "Popping IntegerStack: " . $integerStack->pop() . " (ASCII for 'C')\n";
echo "Popping IntegerStack: " . $integerStack->pop() . " (ASCII for 'B')\n";
echo "Popping IntegerStack: " . $integerStack->pop() . " (ASCII for 'A')\n";
echo "Popping empty IntegerStack: " . ($integerStack->pop() === null ? "null" : $integerStack->pop()) . "\n\n";

// Demonstrate ASCIIStack
echo "=== ASCIIStack ===\n";
$asciiStack = new ASCIIStack();
$asciiStack->push('A');
$asciiStack->push('B');
$asciiStack->push('C');

echo "Popping ASCIIStack: " . $asciiStack->pop() . "\n";
echo "Popping ASCIIStack: " . $asciiStack->pop() . "\n";
echo "Popping ASCIIStack: " . $asciiStack->pop() . "\n";
echo "Popping empty ASCIIStack: " . ($asciiStack->pop() === null ? "null" : $asciiStack->pop()) . "\n\n";

// Demonstrate IntegerToASCIIAdapter
echo "=== IntegerToASCIIAdapter ===\n";
$integerStack = new IntegerStack();
$asciiAdapter = new IntegerToASCIIAdapter($integerStack);

$asciiAdapter->push('A');
$asciiAdapter->push('B');
$asciiAdapter->push('C');

echo "IntegerStack contents (after pushing via adapter): " . implode(', ', $integerStack->getStack()) . " (ASCII values)\n";
echo "Popping via adapter: " . $asciiAdapter->pop() . "\n";
echo "Popping via adapter: " . $asciiAdapter->pop() . "\n";
echo "Popping via adapter: " . $asciiAdapter->pop() . "\n";
echo "Popping empty stack via adapter: " . ($asciiAdapter->pop() === null ? "null" : $asciiAdapter->pop()) . "\n\n";

// Demonstrate ASCIIToIntegerAdapter
echo "=== ASCIIToIntegerAdapter ===\n";
$asciiStack = new ASCIIStack();
$integerAdapter = new ASCIIToIntegerAdapter($asciiStack);

$integerAdapter->push(65); // ASCII for 'A'
$integerAdapter->push(66); // ASCII for 'B'
$integerAdapter->push(67); // ASCII for 'C'

echo "ASCIIStack contents (after pushing via adapter): " . implode(', ', $asciiStack->getStack()) . "\n";
echo "Popping via adapter: " . $integerAdapter->pop() . " (ASCII for 'C')\n";
echo "Popping via adapter: " . $integerAdapter->pop() . " (ASCII for 'B')\n";
echo "Popping via adapter: " . $integerAdapter->pop() . " (ASCII for 'A')\n";
echo "Popping empty stack via adapter: " . ($integerAdapter->pop() === null ? "null" : $integerAdapter->pop()) . "\n";
