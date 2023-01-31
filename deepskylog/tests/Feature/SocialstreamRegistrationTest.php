<?php

namespace Tests\Feature;

use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JoelButcher\Socialstream\Providers;
use Laravel\Fortify\Features as FortifyFeatures;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User;
use Mockery as m;
use Tests\TestCase;

class SocialstreamRegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @dataProvider socialiteProvidersDataProvider
     */
    public function test_users_can_register_using_socialite_providers(string $socialiteProvider)
    {
        // Check if the SKIP_SOCIALITE_TEST_ON_LOCAL environment variable is set to true
        if (env('SKIP_SOCIALITE_TEST_ON_LOCAL', false)) {
            // If the environment variable is set to true, skip the test and provide a message
            return $this->markTestSkipped('Socialite not tested on local machine.');
        }

        if (! FortifyFeatures::enabled(FortifyFeatures::registration())) {
            return $this->markTestSkipped('Registration support is not enabled.');
        }

        if (! Providers::enabled($socialiteProvider)) {
            return $this->markTestSkipped("Registration support with the $socialiteProvider provider is not enabled.");
        }

        $user = (new User())
            ->map([
                'id' => 'abcdefgh',
                'nickname' => 'Jane',
                'name' => 'Jane Doe',
                'email' => 'janedoe@example.com',
                'avatar' => null,
                'avatar_original' => null,
            ])
            ->setToken('user-token')
            ->setRefreshToken('refresh-token')
            ->setExpiresIn(3600);

        $provider = m::mock('Laravel\\Socialite\\Two\\'.$socialiteProvider.'Provider');
        $provider->shouldReceive('user')->once()->andReturn($user);

        Socialite::shouldReceive('driver')->once()->with($socialiteProvider)->andReturn($provider);

        session()->put('socialstream.previous_url', route('register'));

        $response = $this->get("/oauth/$socialiteProvider/callback");

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function socialiteProvidersDataProvider(): array
    {
        return [
            [Providers::google()],
            [Providers::facebook()],
            [Providers::github()],
            [Providers::twitter()],
        ];
    }
}
