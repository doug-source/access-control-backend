<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Library\Builders\Response as ResponseBuilder;
use Illuminate\Support\Str;

class SocialiteController extends Controller
{
    public function releaseToken(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();

        return ResponseBuilder::successJSON([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'enterprise' => [
                    'name' => $user->enterprise->name
                ],
                'token' => Str::replaceMatches(
                    pattern: '|^\d+\||',
                    replace: '',
                    subject: $user->createToken('Sanctum+Socialite')->plainTextToken
                )
            ]
        ]);
    }
}
