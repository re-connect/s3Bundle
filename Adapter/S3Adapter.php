<?php

namespace Reconnect\S3Bundle\Adapter;

use Aws\Result;
use Aws\S3\S3Client;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV4;

class S3Adapter
{
    private S3Client $client;
    private string $bucketName;

    public function __construct(string $bucketHost, string $bucketName, string $bucketKey, string $bucketSecret)
    {
        $this->client = new S3Client([
            'endpoint' => $bucketHost,
            'credentials' => [
                'key' => $bucketKey,
                'secret' => $bucketSecret,
            ],
            'use_path_style_endpoint' => true,
            'region' => 'eu-west-1',
            'version' => 'latest',
        ]);
        $this->bucketName = $bucketName;
    }

    public function getPreSignedUrl(string $fileKey = null): ?string
    {
        if (null === $fileKey) {
            return null;
        }
        $command = $this->client->getCommand('GetObject', [
            'Bucket' => $this->bucketName,
            'Key' => $fileKey,
        ]);

        return (string) $this->client->createPresignedRequest($command, '+10 minutes')->getUri();
    }

    /**
     * @throws \Exception
     */
    public function putFile(File $file, ?string $fileKey = null): UuidV4
    {
        $key = null === $fileKey ? Uuid::v4() : $fileKey;
        $stream = fopen($file->getPathname(), 'r');
        $this->client->putObject([
            'Bucket' => $this->bucketName,
            'Key' => $key,
            'Body' => $stream,
            'ContentType' => $file->getMimeType(),
            'ACL' => 'public-read',
        ]);
        fclose($stream);

        return $key;
    }

    /**
     * @throws \Exception
     */
    public function download(string $key, string $tempUri, ?string $bucketName = null): Result
    {
        $bucketName = $bucketName ?? $this->bucketName;

        return $this->client->getObject([
            'Bucket' => $bucketName,
            'Key' => $key,
            'SaveAs' => $tempUri,
        ]);
    }

    /**
     * @throws \Exception
     */
    public function copyFileFromOtherBucket(string $otherBucketName, string $otherBucketKey): UuidV4
    {
        $key = Uuid::v4();
        $this->client->copyObject([
            'Bucket' => $this->bucketName,
            'Key' => $key,
            'CopySource' => $otherBucketName.'/'.$otherBucketKey,
        ]);

        return $key;
    }

    public function getDownloadablePresignedUrl(string $key, string $contentType, string $fileName): string
    {
        $disposition = HeaderUtils::makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $fileName,
        );

        $command = $this->client->getCommand('GetObject', [
            'Bucket' => $this->bucketName,
            'Key' => $key,
            'ResponseContentType' => $contentType,
            'ResponseContentDisposition' => $disposition,
        ]);

        return (string) $this->client->createPresignedRequest($command, '+10 minutes')->getUri();
    }
}
