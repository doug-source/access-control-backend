<?php

declare(strict_types=1);

namespace App\Library\Builders;

use Illuminate\Support\Uri;
use Illuminate\Support\Str;

final class EmailVerifyMail
{
    public static function buildEmailButtonUrl(string $origin, string $pathTarget): string
    {
        $uri = Uri::of($origin);
        [$id, $hash] = self::extractRouteParams($uri);
        return UrlExternal::build(
            path: Str::of($pathTarget)->append("/$id/$hash")->toString(),
            query: $uri->query()->array()
        )->value();
    }

    /**
     * Extract and separate each url path segment inside of an array
     */
    private static function extractRouteParams(Uri $uri)
    {
        return Str::of(
            self::pickRouteParams($uri->path())
        )->split('|/|')->toArray();
    }

    /**
     * Pick the final url path segments
     */
    private static function pickRouteParams(string $path)
    {
        $main = Str::of($path);
        $beginning = $main->replaceMatches('|\d+/.+$|', '')->toString();
        return $main->after($beginning)->toString();
    }
}
