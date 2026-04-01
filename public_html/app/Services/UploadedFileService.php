<?php

namespace App\Services;

use App\Constants\FileDirectory;
use App\Models\UploadedFile;
use BadMethodCallException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile as File;
use Illuminate\Support\Facades\Storage;

class UploadedFileService extends Service
{
    /**
     * Name of the base directory where uploads will be stored.
     */
    private const  UPLOADS_DIRECTORY_NAME = 'uploads';

    private const  AVATAR_DIRECTORY_NAME = 'avatar';

    /**
     * Saves a file to the specified directory.
     *
     * @param  File  $file  The uploaded file instance.
     * @param  null|FileDirectory|string  $directory  The subdirectory within the uploads directory where the file will be saved.
     *                                                Can be an instance of FileDirectory, a string, or null for default.
     * @return string The path of the stored file relative to the storage disk.
     */
    private function saveFile(File $file, null|FileDirectory|string $directory = null): string
    {
        $path = self::UPLOADS_DIRECTORY_NAME;

        if ($directory) {
            if (is_object($directory)) {
                $directory = $directory->value;
            }
            $directory = trim($directory, '/');
            $path .= "/$directory";
        }

        $filePath = $file->store($path, 'public');

        return $filePath;
    }

    /**
     * Handles file upload from an HTTP request and stores its metadata in the database.
     *
     * @param  Request  $request  The HTTP request instance containing the uploaded file.
     * @param  string  $fieldName  The name of the file input field in the request.
     * @param  null|FileDirectory|string  $directory  The subdirectory within the uploads directory to store the file.
     *                                                Can be an instance of FileDirectory, a string, or null for default.
     * @return UploadedFile The newly created UploadedFile model instance with metadata about the uploaded file.
     *
     * @throws BadMethodCallException If the specified file field does not exist in the given request.
     */
    public function uploadFromRequest(Request $request, string $fieldName, null|FileDirectory|string $directory = null): UploadedFile
    {
        $file = $request->file($fieldName);

        if (! $file) {
            throw new BadMethodCallException('Specified file does not exist in the given request.');
        }

        $filePath = $this->saveFile($file, $directory);

        $uploadedFile = UploadedFile::create([
            'disk' => config('filesystems.default'),
            'path' => $filePath,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'user_id' => $request->user()?->id,
        ]);

        return $uploadedFile;
    }

    /**
     * Handle multiple file uploads from a request and save them to the specified directory.
     *
     * This method processes multiple files from the given request field, validates their presence and type,
     * saves each file to the designated directory, and creates a corresponding record in the `UploadedFile` model.
     *
     * @param  \Illuminate\Http\Request  $request  The incoming HTTP request containing the files.
     * @param  string  $fieldName  The name of the request field containing the files.
     * @param  null|FileDirectory|string  $directory  The directory where files should be saved.
     *                                                - If null, a default directory should be determined by the `saveFile` method.
     * @return UploadedFile[] An array of `UploadedFile` model instances, each representing a successfully uploaded file.
     *
     * @throws \BadMethodCallException If the specified field does not contain any files.
     */
    public function uploadMultipleFromRequest(Request $request, string $fieldName, null|FileDirectory|string $directory = null): array
    {
        $files = $request->file($fieldName);

        if (! is_array($files)) {
            $files = [$files];
        }

        $uploadedFiles = [];

        $defaultDisk = config('filesystems.default');

        foreach ($files as $file) {

            if (! $file || ! ($file instanceof File)) {
                continue;
            }

            $filePath = $this->saveFile($file, $directory);

            $uploadedFile = UploadedFile::create([
                'disk' => $defaultDisk,
                'path' => $filePath,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'user_id' => $request->user()?->id,
            ]);

            $uploadedFiles[] = $uploadedFile;

        }

        return $uploadedFiles;
    }

    /**
     * Saves a base64-encoded avatar image to storage and returns the file path.
     *
     * This method decodes a base64-encoded image, assigns it a unique name,
     * and stores it in the specified directory within the public storage disk.
     * It supports only base64 strings that represent images.
     *
     * @param  string  $base64  The base64-encoded string of the image. It may include the data URI prefix (e.g., "data:image/png;base64,").
     * @return string The storage path of the saved avatar (relative to the public disk).
     *
     * @throws InvalidArgumentException If the provided base64 string is invalid or cannot be decoded.
     */
    public function saveAvatarFromBase64(string $base64): string
    {
        if (str_starts_with($base64, 'data:image/')) {
            $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
        }
        $directory = self::AVATAR_DIRECTORY_NAME;
        $decodedData = base64_decode($base64);
        $uniqueName = md5(uniqid(true)).'.png';
        $path = $directory.'/'.$uniqueName;
        Storage::put($path, $decodedData);

        return $path;
    }
}
