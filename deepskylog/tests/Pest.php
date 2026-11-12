<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses(
    Tests\TestCase::class,
    Illuminate\Foundation\Testing\RefreshDatabase::class,
)->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

// expect()->extend('toBeOne', function () {
//    return $this->toBe(1);
// });

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/*
 * The advanced searches (AdvancedObjectSearch, AdvancedObservationSearch) go
 * to their results with JavaScript, after giving the form page the criteria in
 * its URL: assert that both URLs are in that script.
 */
Livewire\Features\SupportTesting\Testable::macro('assertSearchGoesTo', function (string $resultsUrl, string $formUrl) {
    $flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES;
    $script = collect($this->effects['xjs'] ?? [])->pluck('expression')->implode("\n");

    PHPUnit\Framework\Assert::assertStringContainsString('history.replaceState(history.state, \'\', '.json_encode($formUrl, $flags).')', $script);
    PHPUnit\Framework\Assert::assertStringContainsString('window.location.assign('.json_encode($resultsUrl, $flags).')', $script);

    return $this;
});

Livewire\Features\SupportTesting\Testable::macro('assertSearchGoesToContaining', function (string $part) {
    $script = collect($this->effects['xjs'] ?? [])->pluck('expression')->implode("\n");

    PHPUnit\Framework\Assert::assertMatchesRegularExpression('/window\\.location\\.assign\\("[^"]*'.preg_quote($part, '/').'/', $script);

    return $this;
});
