<?php

namespace App\Library\Builders;

use Illuminate\Http\Response as HttpResponse;
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
        ], HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Build the successful json response output
     *
     * @param mixed|null $data
     */
    public static function successJSON($data = NULL, int $status = HttpResponse::HTTP_OK, array $headers = [], bool $complete = false)
    {
        if ($complete) {
            return response()->json(
                data: [
                    'message' => 'OK',
                    'status' => $status,
                    'data' => $data
                ],
                status: $status,
                headers: $headers
            );
        }
        return response()->json(data: $data, status: $status, headers: $headers);
    }
}
