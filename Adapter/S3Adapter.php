<?php


namespace Reconnect\S3Bundle\Adapter;

use Aws\Result;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV4;

class S3Adapter
{
    private S3Client $client;
    private MimeTypes $mimeTypes;
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
        $this->mimeTypes = new MimeTypes();
        $this->bucketName = $bucketName;
    }

    public function getPreSignedUrl(string $fileKey = null): ?string
    {
        if (null === $fileKey) {
            return null;
        }
        try {
            $command = $this->client->getCommand('GetObject', [
                'Bucket' => $this->bucketName,
                'Key' => $fileKey,
            ]);

            return (string) $this->client->createPresignedRequest($command, '+10 minutes')->getUri();
        } catch (S3Exception $e) {
            return null;
        }
    }

    /**
     * @param File $file
     * @return UuidV4
     * @throws \Exception
     */
    public function putFile(File $file): UuidV4
    {
        try {
            $key = Uuid::v4();
            $stream = fopen($file->getPathname(), 'r');
            $this->client->putObject([
                'Bucket' => 'axel',
                'Key' => $key,
                'Body' => $stream,
                'ContentType' => $file->getMimeType(),
                'ACL' => 'public-read',
            ]);
            fclose($stream);

            return $key;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param string      $key
     * @param string      $tempUri
     * @param string|null $bucketName
     * @return Result
     * @throws \Exception
     */
    public function download(string $key, string $tempUri, string $bucketName = null): Result
    {
        try {
            $bucketName = $bucketName ?? $this->bucketName;

            return $this->client->getObject([
                'Bucket' => $bucketName,
                'Key' => $key,
                'SaveAs' => $tempUri,
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}