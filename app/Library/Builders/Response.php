<?php

namespace App\Library\Builders;

use \Illuminate\Support\Stringable;

final class Response
{
    /**
     * Build the invalid json response output
     */
    public static function invalidJSON(Stringable|string $msg)
    {
        return response()->json([
            'errors' => ['status' => [$msg]]
        ], 403);
    }

    /**
     * Build the successful json response output
     *
     * @param mixed|null $data
     */
    public static function successJSON($data = NULL, bool $complete = false)
    {
        if ($complete) {
            return response()->json(
                data: [
                    'message' => 'OK',
                    'status' => 200,
                    'data' => $data
                ],
            );
        }
        return response()->json(data: $data);
    }
}
