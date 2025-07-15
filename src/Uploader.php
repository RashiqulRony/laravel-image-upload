<?php

namespace Rashiqulrony\LaravelImageUpload;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class Uploader
{
    protected static $basePath = '';
    protected static $originalPath = '';
    protected static $file = '';
    protected static $name = '';
    protected static $thumbPath = '';
    protected static $thumb = false;
    protected static $storageFolder = 'storage/';
    protected static $imageResize = [];
    protected static $thumbResize = [300, 300];
    protected static $_imageManager = null;

    protected static function init()
    {
        if (is_null(self::$_imageManager)) {
            self::$_imageManager = new ImageManager(new Driver());
        }
    }

    /**
     * Internal method to handle the upload process.
     *
     * @return array Uploaded file details.
     */
    private static function upload()
    {
        self::init();
        $file = self::$file;

        $fileName = self::$name
            ? Str::slug(self::$name, '-') . '.' . $file->getClientOriginalExtension()
            : time() . '-' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), '-') . '.' . $file->getClientOriginalExtension();

        $data = [
            'name' => $fileName,
            'originalName' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'ext' => $file->getClientOriginalExtension(),
            'url' => url(self::$storageFolder . self::$originalPath . $fileName),
            'thumbUrl' => self::$thumb ? url(self::$storageFolder . self::$originalPath . 'thumb/' . $fileName) : null,
        ];

        if (!empty(self::$imageResize)) {
            $image = self::$_imageManager->read($file)->resize(self::$imageResize[0], self::$imageResize[1]);
            Storage::put(self::$originalPath . '/' . $fileName, (string) $image->encode());
        } else {
            Storage::putFileAs(self::$originalPath, $file, $fileName);
        }

        if (self::$thumb) {
            if (!empty(self::$thumbResize)) {
                self::$_imageManager->read(self::$storageFolder . self::$originalPath . $fileName)
                    ->resize(self::$thumbResize[0], self::$thumbResize[1])
                    ->save(self::$storageFolder . self::$thumbPath . '/' . $fileName);
            } else {
                self::$_imageManager->read(self::$storageFolder . self::$originalPath . $fileName)
                    ->resize(config('imageupload.image_thumb_width'), config('imageupload.image_thumb_height'))
                    ->save(self::$storageFolder . self::$thumbPath . '/' . $fileName);
            }
        }

        return $data;
    }

    /**
     * Upload an image with optional resizing and thumbnail creation.
     *
     * @param mixed $requestFile Uploaded file from the request.
     * @param string $path Destination folder path.
     * @param bool $thumb Generate thumbnail or not.
     * @param string|null $name Optional custom filename.
     * @param array $imageResize Resize dimensions [width, height].
     * @param array $thumbResize Thumbnail dimensions [width, height].
     * @return array Uploaded image information.
     */
    public static function imageUpload($requestFile, $path, $thumb = false, $name = null, $imageResize = [], $thumbResize = [300, 300])
    {
        $realPath = self::$basePath . $path . '/';
        if (!Storage::exists($realPath)) {
            Storage::makeDirectory($realPath);
        }

        if (!Storage::exists($realPath . 'thumb') && $thumb) {
            Storage::makeDirectory($realPath . 'thumb');
        }

        self::$file = $requestFile;
        self::$originalPath = $realPath;
        self::$thumbPath = $realPath . 'thumb';
        self::$thumb = $thumb;
        self::$name = $name;
        self::$imageResize = $imageResize;
        self::$thumbResize = $thumbResize;

        return self::upload();
    }

    /**
     * Upload a video file.
     *
     * @param mixed $requestFile Video file from request.
     * @param string $path Destination path.
     * @param string|null $name Optional custom filename.
     * @return array Uploaded video file info.
     */
    public static function videoUpload($requestFile, $path, $name = null)
    {
        $realPath = self::$basePath . $path . '/';
        if (!Storage::exists($realPath)) {
            Storage::makeDirectory($realPath);
        }

        self::$file = $requestFile;
        self::$originalPath = $realPath;
        self::$name = $name;

        return self::upload();
    }

    /**
     * Upload any type of file.
     *
     * @param mixed $requestFile File from request.
     * @param string $path Destination folder path.
     * @param string|null $name Optional custom filename.
     * @return array Uploaded file info.
     */
    public static function fileUpload($requestFile, $path, $name = null)
    {
        $realPath = self::$basePath . $path . '/';
        if (!Storage::exists($realPath)) {
            Storage::makeDirectory($realPath);
        }

        self::$file = $requestFile;
        self::$originalPath = $realPath;
        self::$name = $name;

        return self::upload();
    }

    /**
     * Upload a base64-encoded image.
     *
     * @param string $base64 Base64 encoded image string.
     * @param string $path Destination folder path.
     * @param string|null $name Optional custom filename.
     * @return array Uploaded image info.
     */
    public static function imageUploadBase64($base64, $path, $name = null)
    {
        $realPath = self::$basePath . $path . '/';
        if (!Storage::exists($realPath)) {
            Storage::makeDirectory($realPath);
        }

        $extension = explode('/', explode(':', substr($base64, 0, strpos($base64, ';')))[1])[1];
        $replace = substr($base64, 0, strpos($base64, ',') + 1);
        $image = str_replace([$replace, ' '], ['', '+'], $base64);

        $imageName = $name ? $name . '_' . Str::random(10) . '.' . $extension : Str::random(10) . '.' . $extension;

        Storage::disk('public')->put($realPath . '/' . $imageName, base64_decode($image));

        return [
            'name' => $imageName,
            'url' => url(self::$storageFolder . $realPath . $imageName),
        ];
    }

    /**
     * Upload raw content to a file.
     *
     * @param string $content Raw file content.
     * @param string $path Destination folder path.
     * @param string $name File name.
     * @return array Uploaded content info.
     */
    public static function contentUpload($content, $path, $name)
    {
        $realPath = self::$basePath . $path . '/';
        if (!Storage::exists($realPath)) {
            Storage::makeDirectory($realPath);
        }

        Storage::put($realPath . $name, $content);

        return [
            'name' => $name,
            'url' => url(self::$storageFolder . $realPath . $name),
        ];
    }

    /**
     * Create a thumbnail for an existing image.
     *
     * @param string $path Image folder path.
     * @param string $file Image filename.
     * @param string|false $thumbPath Optional custom thumbnail folder.
     * @param int $thumbWidth Thumbnail width.
     * @param int $thumbHeight Thumbnail height.
     * @return bool Success status.
     */
    public static function thumb($path, $file, $thumbPath = false, $thumbWidth = 0, $thumbHeight = 0)
    {
        self::init();

        $realPath = self::$basePath . $path;
        $thumbPath = $thumbPath ?: $realPath . '/thumb';

        $thumbWidth = $thumbWidth > 0 ? $thumbWidth : config('imageupload.image_thumb_width');
        $thumbHeight = $thumbHeight > 0 ? $thumbHeight : config('imageupload.image_thumb_height');

        if (!Storage::exists($thumbPath)) {
            Storage::makeDirectory($thumbPath);
        }

        $image = self::$_imageManager->read(self::$storageFolder . $realPath . '/' . $file)
            ->resize($thumbWidth, $thumbHeight)
            ->save(self::$storageFolder . $thumbPath . '/' . $file);

        return isset($image->filename);
    }

    /**
     * Delete a file and optionally its thumbnail.
     *
     * @param string $file File name.
     * @param string $path File path.
     * @param bool $thumb Whether to delete the thumbnail.
     * @return bool Success status.
     */
    public static function mediaDelete($file, $path, $thumb = false)
    {
        $realPath = self::$basePath . $path . '/';

        if (Storage::exists($realPath . $file)) {
            Storage::delete($realPath . $file);
            if ($thumb) {
                Storage::delete($realPath . 'thumb/' . $file);
            }
            return true;
        }

        return false;
    }

    /**
     * Delete an entire folder.
     *
     * @param string $path Folder path.
     * @return bool Success status.
     */
    public static function removeDir($path)
    {
        $realPath = self::$basePath . $path . '/';
        if (Storage::exists($realPath)) {
            Storage::deleteDirectory($realPath);
        }
        return true;
    }
}
