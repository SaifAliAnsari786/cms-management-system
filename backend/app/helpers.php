<?php

use Illuminate\Http\UploadedFile;

if (! function_exists('upload_image')) {

    /**
     * Upload an image to the public storage.
     *
     * @param UploadedFile|null $file
     * @param string $folder
     * @return string|null
     */
    function upload_image(?UploadedFile $file, string $folder = 'uploads'): ?string
    {
        if (!$file) {
            return null;
        }

        return $file->store($folder, 'public');
    }
}