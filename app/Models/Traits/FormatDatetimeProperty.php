<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Library\Builders\DatetimeFormat as DatetimeFormatBuilder;

trait FormatDatetimeProperty
{
    /**
     * Format datetime properties based on current locale
     *
     * @return string
     */
    protected function getPropertyFormatted($prop)
    {
        return DatetimeFormatBuilder::formatToDate($this->$prop);
    }
}
