<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

use App\Models\Enterprise;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\{
    Facades\Socialite,
    Two\User as ProviderUser
};

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

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

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

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


/**
 * Create the database user instance used by testing environment
 */
function createUserDB(?string $password, string $email)
{
    $enterprise = Enterprise::factory(count: 1)->create()->first();
    return User::factory(count: 1)->create([
        'enterprises_id' => $enterprise->id,
        'email' => $email,
        'password' => $password ? Hash::make($password) : NULL
    ])->first();
}

/**
 * Create the user socialite mocking instance used by testing environment
 */
function buildSocialite(?string $password = NULL, string $email = 'test@test.com', bool $createUsedDB = true, ?Exception $exception = NULL)
{
    $user = $createUsedDB ? createUserDB($password, $email) : (object)['email' => $email];

    $socialiteUser = Mockery::mock(ProviderUser::class);

    if ($exception) {
        $socialiteUser->shouldReceive('getEmail')->andThrow($exception);
    } else {
        // ->shouldReceive('getId')->andReturn($googleId = '12345654321345')
        // ->shouldReceive('getName')->andReturn($user->name)
        // ->shouldReceive('getAvatar')->andReturn($avatarUrl = 'https://en.gravatar.com/userimage');
        $socialiteUser->shouldReceive('getEmail')->andReturn($user->email);
    }

    Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);
    return $user;
}
