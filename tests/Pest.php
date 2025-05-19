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

use App\Models\Role;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\{
    Facades\Socialite,
    Two\User as ProviderUser
};
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Stringable;

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
function createUserDB(string $email, ?string $password = NULL, bool $emailVerified = TRUE)
{
    $preParams = $emailVerified ? [] : ['email_verified_at' => NULL];

    return User::factory()->createOne([
        'email' => $email,
        'password' => !is_null($password) ? Hash::make($password) : NULL,
        ...$preParams
    ]);
}

/**
 * Create the user socialite mocking instance used by testing environment
 */
function buildSocialite(
    ?string $password = NULL,
    string $email = 'test@test.com',
    bool $createUsedDB = true,
    bool $emailVerified = TRUE,
    ?Exception $exception = NULL
) {
    $user = $createUsedDB ? createUserDB(
        email: $email,
        password: $password,
        emailVerified: $emailVerified
    ) : (object)['email' => $email];

    $socialiteUser = Mockery::mock(ProviderUser::class);

    if ($exception) {
        $socialiteUser->shouldReceive('getEmail')->andThrow($exception);
    } else {
        // ->shouldReceive('getId')->andReturn($googleId = '12345654321345')
        // ->shouldReceive('getName')->andReturn($user->name)
        // ->shouldReceive('getAvatar')->andReturn($avatarUrl = 'https://en.gravatar.com/userimage');
        $socialiteUser->shouldReceive('getEmail')->andReturn($user->email);
    }

    Socialite::shouldReceive('driver->redirect');
    Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);
    return $user;
}

function findUserFromDB(string $email): ?User
{
    return (new UserRepository())->findByEmail(email: $email);
}

/**
 * Execute the login
 */
function login(mixed $scope, ?string $email = NULL, ?string $password = NULL)
{
    $emailUsed = $email ?: fake()->email();
    $passwordUsed = $password ?: fake()->password();
    $user = createUserDB(password: $passwordUsed, email: $emailUsed);
    $responseLogin = $scope->postJson(route('auth.login'), [
        'email' => $user->email,
        'password' => $passwordUsed
    ]);
    return Str::after($responseLogin->baseResponse->original['user']['token'], '|');
}

function createSuperAdminRelationship(User $user)
{
    $role = Role::factory(count: 1)->createOne([
        'name' => 'super-admin'
    ]);
    $user->roles()->attach($role->id);
}

/**
 * Execute failed response assertions
 */
function assertFailedResponse(TestResponse $response, string $errorKey, Stringable $errorMsg, int $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY): TestResponse
{
    return $response->assertStatus($statusCode)
        ->assertExactJson([
            'message' => $errorMsg,
            'errors' => [
                $errorKey => [$errorMsg]
            ]
        ]);
}

/**
 * Generate the large word repeting a letter
 */
function generateWordBySize(int $size, string $letter = 'a')
{
    return  Str::of(str_repeat($letter, $size))->toString();
}

/**
 * Generate the larger email by size
 */
function generateOverflowInvalidEmail(int $maxSize)
{
    $sufix = Str::of('@test.com');
    return $sufix->prepend(
        generateWordBySize($maxSize - $sufix->length() + 1)
    );
}
