<?php

declare(strict_types=1);

namespace App\Library\Converters;

use Illuminate\Http\Request;

final class ResponseIndex
{
    /**
     * Pick the query fields into array format
     *
     * @phpstan-type Field array{field: string, default?: string|null}
     * @param Field ...$fields
     * @return array{page: string, group: string} | array<string, string>
     */
    public static function handleQuery(Request $request, array ...$fields): array
    {
        $output = [
            'page' => $request->query('page', 1),
            'group' => $request->query('group', config('database.paginate.perPage')),
        ];
        if ($fields) {
            foreach ($fields as $field) {
                $fieldName = $field['field'];
                $output[$fieldName] = $request->query($fieldName, $field['default'] ?? NULL);
            }
        }
        return $output;
    }
}
