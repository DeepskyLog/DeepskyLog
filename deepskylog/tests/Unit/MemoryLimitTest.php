<?php

use App\Support\MemoryLimit;

/*
 * A queue worker runs job after job in one process: a job that needs more
 * memory raises the limit, but must never lower it for the jobs after it.
 */

afterEach(fn () => ini_set('memory_limit', $this->previousLimit));

beforeEach(fn () => $this->previousLimit = ini_get('memory_limit'));

it('raises a lower memory limit', function () {
    ini_set('memory_limit', '256M');

    MemoryLimit::atLeast('512M');

    expect(ini_get('memory_limit'))->toBe('512M');
});

it('never lowers the memory limit', function (string $limit) {
    ini_set('memory_limit', $limit);

    MemoryLimit::atLeast('1024M');

    expect(ini_get('memory_limit'))->toBe($limit);
})->with(['-1', '2G', '1024M']);
