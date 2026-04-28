<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class FileService extends Service
{
    /**
     * Handles image uploading and saving to a specified directory in public path.
     * 
     * @param Request $request
     * @param string $fieldName
     * @param string $directory
     * @return string|null
     */
    public static function imageUploader(Request $request, string $fieldName, string $directory): ?string
    {
        if ($request->hasFile($fieldName)) {
            $file = $request->file($fieldName);
            $filename = time() . '_' . $file->getClientOriginalName();
            
            // Ensure directory exists
            if (!File::isDirectory(public_path($directory))) {
                File::makeDirectory(public_path($directory), 0755, true, true);
            }

            $file->move(public_path($directory), $filename);
            return $filename;
        }
        return null;
    }

    /**
     * Generates a full URL for a file in a given directory.
     * 
     * @param string $directory
     * @param string $filename
     * @return string
     */
    public static function getFileUrl(string $directory, string $filename): string
    {
        return asset($directory . $filename);
    }

    /**
     * Deletes a file from S3 storage if it exists.
     * 
     * @param string $path
     * @return void
     */
    public static function deleteFromS3(string $path): void
    {
        if ($path && Storage::disk('s3')->exists($path)) {
            Storage::disk('s3')->delete($path);
        }
    }
}
