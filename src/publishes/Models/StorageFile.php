<?php

namespace App\Models;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use StdioTemplate\FileHelper\FFMpegStdio;

class StorageFile extends FFMpegStdio
{
    protected $storage;

    public function __construct($driver = 'public')
    {
        $this->storage = Storage::disk($driver);
    }

    /**
     * Upload file
     * @param storage
     * @param dir
     * @param file
     * @param fileName
     */
    public function uploadFile($dir, $file, $fileName = null)
    {
        try {
            if (!isset($fileName))
                $fileName = $file->getClientOriginalName() . time() . random_int(1, 1000);

            if (self::checkExists($this->storage, $dir . $fileName))
                throw new \Exception('File exists!', Response::HTTP_INTERNAL_SERVER_ERROR);

            return $this->storage->putFileAs(
                $dir,
                $file,
                $fileName
            );
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Check file exists
     * @param storage
     * @param filePath
     */
    public function checkExists($filePath)
    {
        return $this->storage->exists($filePath);
    }

    /**
     * Delete file
     * @param storage
     * @param filePath
     */
    public function deleteFile($filePath)
    {
        try {
            if (self::checkExists($filePath))
                throw new \Exception('File not exists!', Response::HTTP_INTERNAL_SERVER_ERROR);

            return $this->storage->delete($filePath);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Download file
     * @param storage
     * @param filePath
     */
    public function downloadFile($filePath)
    {
        try {
            if (self::checkExists($filePath))
                throw new \Exception('File not exists!', Response::HTTP_INTERNAL_SERVER_ERROR);

            return $this->storage->download($filePath);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
