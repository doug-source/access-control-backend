<?php

declare(strict_types=1);

namespace App\Library\Builders;

use Illuminate\Support\Uri;

final class UrlExternal
{
    /**
     * Used by mount frontend external uri links
     */
    public static function build(?string $path = NULL, array $query = []): Uri
    {
        $frontendUrl = config('app.frontend.uri.host');
        $uri = Uri::of($frontendUrl);
        if (!is_null($path)) {
            $uri = $uri->withPath($path);
        }
        if ($query) {
            $uri = $uri->withQuery($query);
        }
        return $uri;
    }
}
