<?php

use App\Modules\Fee\Services\FeeCalculatorService;

beforeEach(function () {
    $this->calculator = new FeeCalculatorService;
});

it('normalizes monthly fees correctly', function () {
    expect($this->calculator->normalizeToMonthly(1000, 'monthly'))->toBe(1000.0);
});

it('normalizes quarterly fees correctly', function () {
    expect($this->calculator->normalizeToMonthly(3000, 'quarterly'))->toBe(1000.0);
});

it('normalizes half_yearly fees correctly', function () {
    expect($this->calculator->normalizeToMonthly(6000, 'half_yearly'))->toBe(1000.0);
});

it('normalizes yearly fees correctly', function () {
    expect($this->calculator->normalizeToMonthly(12000, 'yearly'))->toBe(1000.0);
});

it('returns 0 for one_time fees in monthly normalization', function () {
    expect($this->calculator->normalizeToMonthly(5000, 'one_time'))->toBe(0.0);
});

it('returns null for per_unit fees in monthly normalization', function () {
    expect($this->calculator->normalizeToMonthly(100, 'per_unit'))->toBeNull();
});

it('returns null for unknown frequency', function () {
    expect($this->calculator->normalizeToMonthly(1000, 'unknown'))->toBeNull();
});

it('handles rounding correctly', function () {
    $result = $this->calculator->normalizeToMonthly(100, 'monthly');
    expect($result)->toBe(100.0);
});

it('calculates percentage change correctly', function () {
    expect($this->calculator->calculatePercentageChange(1000, 1200))->toBe(20.0);
});

it('returns null for percentage change when previous is zero', function () {
    expect($this->calculator->calculatePercentageChange(0, 100))->toBeNull();
});

it('returns null for percentage change when previous is null', function () {
    expect($this->calculator->calculatePercentageChange(null, 100))->toBeNull();
});

it('handles negative percentage change', function () {
    expect($this->calculator->calculatePercentageChange(1000, 800))->toBe(-20.0);
});


