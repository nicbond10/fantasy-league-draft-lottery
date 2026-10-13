<?php

use App\Support\Percentage;

test('percentages convert to integer basis points exactly', function (string $percentage, int $basisPoints) {
    expect(Percentage::toBasisPoints($percentage))->toBe($basisPoints);
})->with([
    ['100.00', 10_000],
    ['25.00', 2500],
    ['0.01', 1],
    ['12.3', 1230],
    ['0', 0],
]);

test('basis points display as percentages with two decimal places', function () {
    expect(Percentage::fromBasisPoints(2500))->toBe('25.00')
        ->and(Percentage::fromBasisPoints(1))->toBe('0.01')
        ->and(Percentage::fromBasisPoints(12_500))->toBe('125.00');
});

test('percentages with invalid precision are rejected', function (string $percentage) {
    expect(fn () => Percentage::toBasisPoints($percentage))->toThrow(InvalidArgumentException::class);
})->with(['12.345', '100.01', '-1.00', 'not-a-percentage']);
