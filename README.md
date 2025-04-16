## Media Uploader


`mediauploader` is a simple file-handling package for `Laravel` that provides various options for managing files, including image uploads, file uploads, WebP uploads, base64 image uploads, image uploads from URLs, and Google image content uploads. The package currently supports `public`, `local`, and `s3` storage (note: only publicly accessible `S3` buckets are supported).

It also includes file and image preview functionality.

## Install

Via Composer

```bash
composer require rashiqulrony/laravel-image-upload
```

#### Publish config file

You should publish the config file with:

```
php artisan vendor:publish --provider="Rashiqulrony\LaravelImageUpload\Providers\AppServiceProvider" --tag=config
```

In `config/imageupload.php` config file you should set `mediauploader` global path.

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Base Directory
    |--------------------------------------------------------------------------
    |
    | base_dir stores all other directory inside storage folder of your laravel application by default
    | if you specify any name. all storage will be done inside that directory or name that you specified
    |
    */

    'base_dir' => null,

    /*
    |--------------------------------------------------------------------------
    | Thumb Directory
    |--------------------------------------------------------------------------
    |
    | thumb_dir creates another folder inside the directory as a "thumb" by default
    | you can change the name thumb to any other name you like.
    */

    'thumb_dir' => 'thumb',

    /*
    |--------------------------------------------------------------------------
    | Timestamp Prefix
    |--------------------------------------------------------------------------
    |
    | If timestamp_prefix is true then create a file with a timestamp to ignore the same name image replacement. Example: image-1658562981.png.
    | If timestamp_prefix is false then the script checks file exists or not if the file exists then add the time() prefix for the new file otherwise leave it as the file
    | name.
    */

    'timestamp_prefix' => false,

    /*
    |--------------------------------------------------------------------------
    | Thumb Image Height Width
    |--------------------------------------------------------------------------
    |
    | specify the thumb image ratio of height and weight by default it takes 300px X 300px
    */

    'image_thumb_height' => 300,
    'image_thumb_width' => 300,

    /*
    |--------------------------------------------------------------------------
    | Fake Image Url
    |--------------------------------------------------------------------------
    |
    | fake_image_url , if you specify a fake image path or url here. the entire package will use
    | this image when there is no image found. or you can specify the fake image in the
    | function parameter as well.
    | Example: 'fake_image_url' => 'images/fake.png' or 'fake_image_url' => 'https://example.com/images/fake.png,
    */

    'fake_image_url' => null,

    /*
    |--------------------------------------------------------------------------
    | Folder permission
    |--------------------------------------------------------------------------
    |
    | path_permission , if you create a folder in your project then you can define your folder permission.
    | Example: null, 0755, 0777
    */

    'path_permission' => 0777,
];
```

## Configuration
Before using this, you must complete the following steps (if you want to use the default filesystem disk, which is public):

Change your env filesystem drive to this if you want to use public storage directory.
```
FILESYSTEM_DISK=public
```
Please make sure to link your storage before using this package
```bash
php artisan storage:link
```

### Image Upload

Use this class
```
use Rashiqulrony\LaravelImageUpload\Uploader;
```

**Using Controller for Image Upload**
```
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

return Uploader::imageUpload($request->image, $path, 1, $name, [300, 300], [200, 200]);
```
Response
```
{
    "name": "1744802578-60164bb368db6.jpg",
    "originalName": "60164bb368db6.jpg",
    "size": 24418,
    "ext": "jpg",
    "url": "http://127.0.0.1:8000/storage/upload/1744802578-60164bb368db6.jpg",
    "thumbUrl": "http://127.0.0.1:8000/storage/upload/thumb/1744802578-60164bb368db6.jpg"
}
```

**Using Controller for video Upload**
```
/**
* Upload a video file.
*
* @param mixed $requestFile Video file from request.
* @param string $path Destination path.
* @param string|null $name Optional custom filename.
* @return array Uploaded video file info.
*/
return Uploader::videoUpload($request->video, $path, $name);
```
Response
```
{
    "name": "123.mp4",
    "originalName": "Web 1st slide Without Text new 04-25.mp4",
    "size": 709821,
    "ext": "mp4",
    "url": "http://127.0.0.1:8000/storage/upload/123.mp4"
}
```

**Using Controller for File Upload**
```
/**
* Upload any type of file.
*
* @param mixed $requestFile File from request.
* @param string $path Destination folder path.
* @param string|null $name Optional custom filename.
* @return array Uploaded file info.
*/
return Uploader::fileUpload($request->video, $path, $name);
```
Response
```
{
    "name": "123.pdf",
    "originalName": "Registration_Form.pdf",
    "size": 1082270,
    "ext": "pdf",
    "url": "http://127.0.0.1:8000/storage/upload/123.pdf"
}
```

**Using Controller for Upload Base64 Image Upload**
```
/**
* Upload a base64-encoded image.
*
* @param string $base64 Base64 encoded image string.
* @param string $path Destination folder path.
* @param string|null $name Optional custom filename.
* @return array Uploaded image info.
*/

return Uploader::imageUploadBase64($request->base64data, $path, $name);
```
Response
```
{
    "name": "image.png",
    "url": "http://127.0.0.1:8000/storage/upload/image.png"
}
```

**Using Controller for Delete Media or Any File**
```
/**
* Delete a file and optionally its thumbnail.
*
* @param string $file File name.
* @param string $path File path.
* @param bool $thumb Whether to delete the thumbnail.
* @return bool Success status.
*/
return Uploader::mediaDelete($file, $path, 1);
```
Response
```
return true or false;
```

**Using Controller for Remove a Directory or Folder**
```
/**
* Delete an entire folder.
*
* @param string $path Folder path.
* @return bool Success status.
*/
return Uploader::removeDir($path);
```
Response
```
return true or false;
```
