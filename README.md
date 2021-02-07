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
# Default configuration for extension with alias: "reconnect_s3_bundle"
reconnect_s3_bundle:
    bucketHost: ~ # Required
    bucketName: ~ # Required
    bucketKey: ~ # Required
    bucketSecret: ~ # Required
```
