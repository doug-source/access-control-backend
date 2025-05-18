<?php

declare(strict_types=1);

namespace App\Library\Builders;

use Carbon\Carbon;
use Illuminate\Support\Facades\App;

final class DatetimeFormat
{
    public static function formatToDate(Carbon $datetime): string
    {
        $locale = App::getLocale();
        if ($locale === 'pt_BR') {
            return $datetime->format('d/m/Y');
        }
        return $datetime->format('m/d/Y');
    }
}
