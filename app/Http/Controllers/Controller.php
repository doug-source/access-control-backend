<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

abstract class Controller
{
    /**
     * Handle the file submit logic
     *
     * @return string|null The new file's path
     */
    protected static function handleFile(Request $request, Model $model, string $name, string $folderName)
    {
        if ($request->hasFile($name) && $request->file($name)->isValid()) {
            $pathPhotoRecent = storage_path() . '/app/' . $model->$name;
            if (File::exists($pathPhotoRecent)) {
                File::delete($pathPhotoRecent);
            }
            $pathPhotoNew = $request->$name->store($folderName);
            return $pathPhotoNew;
        }
        return NULL;
    }
}
