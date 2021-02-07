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

Then, you can inject the `DocumentService.php` to do some common operations on buckets, such as :

### Fetching a document presigned URL

```php
use Reconnect\S3Bundle\Service\DocumentService;
// ...
private DocumentService $documentService;

public function __construct(DocumentService $s3Adapter)
{
    $this->documentService = $documentService;
}
// ...
// The  $objectKey is the key we used to identify the file in the bucket
$presignedUrl = $documentService->getPresignedUrl($objectKey);
```

### Posting a document

```php
use Reconnect\S3Bundle\Service\DocumentService;
// ...
private DocumentService $documentService;

public function __construct(DocumentService $s3Adapter)
{
    $this->documentService = $documentService;
}
// ...
// Get a file as an instance of UploadedFile
// This method returns the key of the uploaded file in the bucket
// This $key is a random UuidV4
$key = $documentService->uploadFile($file);
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
