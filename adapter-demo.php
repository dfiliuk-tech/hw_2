<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use App\GOF\Adapter\IntegerStack;
use App\GOF\Adapter\ASCIIStack;
use App\GOF\Adapter\IntegerToASCIIAdapter;
use App\GOF\Adapter\ASCIIToIntegerAdapter;

function separator(string $title): void {
    echo PHP_EOL . str_repeat('-', 50) . PHP_EOL;
    echo $title . PHP_EOL;
    echo str_repeat('-', 50) . PHP_EOL;
}

// Demo 1: Integer Stack
separator('INTEGER STACK DEMO');
$intStack = new IntegerStack();

echo "Pushing integers: 65, 66, 67" . PHP_EOL;
$intStack->push(65);
$intStack->push(66);
$intStack->push(67);

echo "Popping from integer stack:" . PHP_EOL;
try {
    echo "Popped: " . $intStack->pop() . " (ASCII for '" . chr(67) . "')" . PHP_EOL;
    echo "Popped: " . $intStack->pop() . " (ASCII for '" . chr(66) . "')" . PHP_EOL;
    echo "Popped: " . $intStack->pop() . " (ASCII for '" . chr(65) . "')" . PHP_EOL;

    // This will throw an exception - stack is now empty
    echo "Trying to pop from empty stack..." . PHP_EOL;
    $intStack->pop();
} catch (\RuntimeException $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}

// Demo 2: ASCII Stack
separator('ASCII STACK DEMO');
$asciiStack = new ASCIIStack();

echo "Pushing characters: 'A', 'B', 'C'" . PHP_EOL;
$asciiStack->push('A');
$asciiStack->push('B');
$asciiStack->push('C');

echo "Popping from ASCII stack:" . PHP_EOL;
echo "Popped: " . $asciiStack->pop() . PHP_EOL;
echo "Popped: " . $asciiStack->pop() . PHP_EOL;
echo "Popped: " . $asciiStack->pop() . PHP_EOL;
echo "Popped from empty stack: " . var_export($asciiStack->pop(), true) . PHP_EOL;

// Demo 3: Integer to ASCII Adapter
separator('INTEGER TO ASCII ADAPTER DEMO');
$intStack = new IntegerStack();
$intToAsciiAdapter = new IntegerToASCIIAdapter($intStack);

echo "Pushing characters through adapter: 'X', 'Y', 'Z'" . PHP_EOL;
$intToAsciiAdapter->push('X');
$intToAsciiAdapter->push('Y');
$intToAsciiAdapter->push('Z');

echo "Popping from adapter (original stack stores ASCII values):" . PHP_EOL;
echo "Popped: " . $intToAsciiAdapter->pop() . PHP_EOL;
echo "Popped: " . $intToAsciiAdapter->pop() . PHP_EOL;
echo "Popped: " . $intToAsciiAdapter->pop() . PHP_EOL;
echo "Popped from empty stack: " . var_export($intToAsciiAdapter->pop(), true) . PHP_EOL;

// Demo 4: ASCII to Integer Adapter
separator('ASCII TO INTEGER ADAPTER DEMO');
$asciiStack = new ASCIIStack();
$asciiToIntAdapter = new ASCIIToIntegerAdapter($asciiStack);

echo "Pushing integers through adapter: 72, 73, 74" . PHP_EOL;
$asciiToIntAdapter->push(72); // 'H'
$asciiToIntAdapter->push(73); // 'I'
$asciiToIntAdapter->push(74); // 'J'

echo "Popping from adapter (original stack stores characters):" . PHP_EOL;
echo "Popped: " . $asciiToIntAdapter->pop() . " (ASCII for '" . chr(74) . "')" . PHP_EOL;
echo "Popped: " . $asciiToIntAdapter->pop() . " (ASCII for '" . chr(73) . "')" . PHP_EOL;
echo "Popped: " . $asciiToIntAdapter->pop() . " (ASCII for '" . chr(72) . "')" . PHP_EOL;

try {
    echo "Trying to pop from empty stack..." . PHP_EOL;
    $asciiToIntAdapter->pop();
} catch (\RuntimeException $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "Demo completed successfully!" . PHP_EOL;