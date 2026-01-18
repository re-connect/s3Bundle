# S3Bundle

S3Bundle helps you connect you Symfony application to a S3 bucket, or any bucket that implements S3 API. It is a wrapper
around the great `league/flysystem-aws-s3-v3` library

## Installation

```bash
composer require reconnect/s3bundle
```

If you are not using Symfony flex, you will need a few extra steps to enable and configure the bundle

## Usage

You need to update the following environment variables to make this bundle works

```env
BUCKET_HOST=http://localhost:9000/ // For a local bucket for example
BUCKET_NAME=files
BUCKET_KEY=files_access
BUCKET_SECRET=files_secret
```

Then, you can inject the `FlysystemS3Client.php` to do some common operations on buckets, such as :

### Fetching a document presigned URL

```php
use Reconnect\S3Bundle\Service\FlysystemS3Client;
// ...
private FlysystemS3Client $S3Client;

public function __construct(FlysystemS3Client $s3Adapter)
{
    $this->documentService = $S3Client;
}
// ...
// The  $objectKey is the key we used to identify the file in the bucket
$presignedUrl = $S3Client->getPresignedUrl($objectKey);
```

### Posting a document

```php
use Reconnect\S3Bundle\Service\FlysystemS3Client;
// ...
private FlysystemS3Client $S3Client;

public function __construct(FlysystemS3Client $s3Adapter)
{
    $this->documentService = $S3Client;
}
// ...
// Get a file as an instance of File
// This method returns the key of the uploaded file in the bucket
// This $key is a random UuidV4
$key = $S3Client->uploadFile($file);
```

### Generating a thumbnail

You can also generate a thumbnail, it handles images and pdfs

```php
use Reconnect\S3Bundle\Service\FlysystemS3Client;
// ...
private FlysystemS3Client $S3Client;

public function __construct(FlysystemS3Client $s3Adapter)
{
    $this->documentService = $S3Client;
}
// ...
// Get a file as an instance of UploadedFile
// This method returns the key of the uploaded thumbnail file in the bucket
// This $key is a random UuidV4
$thumbnailKey = $S3Client->generateThumbnail($file);
```

## Configuration reference

```yaml
# Default configuration for extension with alias: "reconnect_s3"
reconnect_s3:
    bucketHost: ~ # Required
    bucketName: ~ # Required
    bucketKey: ~ # Required
    bucketSecret: ~ # Required
```

if you use .env file, you can import values from the env file
```yaml
reconnect_s3:
    bucketHost: '%env(BUCKET_HOST)%'
    bucketName: '%env(BUCKET_NAME)%'
    bucketKey: '%env(BUCKET_KEY)%'
    bucketSecret: '%env(BUCKET_SECRET)%'
```

and define them in the .env file

```dotenv
BUCKET_HOST=https://localhost:9000/
BUCKET_NAME=bucket
BUCKET_KEY=bucket_key
BUCKET_SECRET=bucket_secret
```
