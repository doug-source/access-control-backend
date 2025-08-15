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

use App\Library\Converters\Phone as PhoneConverter;
use App\Models\Ability;
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
function createUserDB(string $email, ?string $password = NULL, ?string $name = NULL, bool $emailVerified = TRUE)
{
    $preParams = $emailVerified ? [] : ['email_verified_at' => NULL];

    return User::factory()->createOne([
        'name' => $name ?? fake()->name(),
        'email' => $email,
        'phone' => PhoneConverter::clear(fake()->phoneNumber()),
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
    ?string $name = NULL,
    bool $createUsedDB = true,
    bool $emailVerified = TRUE,
    ?Exception $exception = NULL
) {
    $user = $createUsedDB ? createUserDB(
        email: $email,
        password: $password,
        name: $name,
        emailVerified: $emailVerified
    ) : (object)['email' => $email, 'name' => $name];

    $socialiteUser = Mockery::mock(ProviderUser::class);

    if ($exception) {
        $socialiteUser->shouldReceive('getEmail')->andThrow($exception);
    } else {
        $socialiteUser->shouldReceive('getId')->andReturn('12345654321345');
        $socialiteUser->shouldReceive('getName')->andReturn($user->name);
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://gravatar.com/avatar/fd631317f9f9ed1acf2547344c75d429?s=50&d=robohash&r=x');
        $socialiteUser->shouldReceive('getEmail')->andReturn($user->email);
    }

    Socialite::shouldReceive('driver->redirect');
    Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);
    return $user;
}

/**
 * Execute the authentication
 *
 * @return array{user: App\Models\User, response: Illuminate\Testing\TestResponse, token: string}
 */
function authenticate(mixed $scope, ?string $email = NULL, ?string $password = NULL, bool $create = TRUE)
{
    $emailUsed = $email ?: fake()->email();
    $passwordUsed = $password ?: fake()->password();
    $user = $create ? createUserDB(
        password: $passwordUsed,
        email: $emailUsed
    ) : (new UserRepository())->findByEmail($emailUsed);

    $responseLogin = $scope->postJson(route('auth.login'), [
        'email' => $user->email,
        'password' => $passwordUsed
    ]);

    return [
        'user' => $user,
        'response' => $responseLogin,
        'token' => $responseLogin->json()['user']['token']
    ];
}

function createSuperAdminRelationship(User $user)
{
    $abilities = Ability::factory(count: 3)->create();
    $role = Role::factory(count: 1)->createOne([
        'name' => 'super-admin'
    ]);
    $role->abilities()->attach($abilities->pluck('id')->all());
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
