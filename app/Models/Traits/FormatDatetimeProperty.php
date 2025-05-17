<?php

declare(strict_types=1);

namespace App\Models\Traits;

use Illuminate\Support\Facades\App;

trait FormatDatetimeProperty
{
    /**
     * Format datetime properties based on current locale
     *
     * @return string
     */
    protected function getPropertyFormatted(string $prop)
    {
        $locale = App::getLocale();
        if ($locale === 'pt_BR') {
            return $this->$prop->format('d/m/Y');
        }
        return $this->$prop->format('m/d/Y');
    }
}
