<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Utility\Upload;
use Tests\TestCase;

class UploadTest extends TestCase
{
    private string $uploadDirectory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->uploadDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'videgrenier-tests-' . uniqid();

        if (!mkdir($this->uploadDirectory) && !is_dir($this->uploadDirectory)) {
            throw new \RuntimeException('Unable to create temporary upload directory.');
        }
    }

    protected function tearDown(): void
    {
        foreach (glob($this->uploadDirectory . DIRECTORY_SEPARATOR . '*') ?: [] as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        if (is_dir($this->uploadDirectory)) {
            rmdir($this->uploadDirectory);
        }

        parent::tearDown();
    }

    public function testUploadAcceptsAJpegImage(): void
    {
        $temporaryFile = $this->createTemporaryFile('jpeg-content');

        $pictureName = Upload::uploadFile(
            [
                'name' => 'photo.jpeg',
                'tmp_name' => $temporaryFile,
                'size' => filesize($temporaryFile)
            ],
            'article-1',
            function (string $source, string $target): bool {
                return copy($source, $target);
            },
            $this->uploadDirectory
        );

        $this->assertSame('article-1.jpeg', $pictureName);
        $this->assertFileExists($this->uploadDirectory . DIRECTORY_SEPARATOR . $pictureName);
    }

    public function testUploadAcceptsAPngImage(): void
    {
        $temporaryFile = $this->createTemporaryFile('png-content');

        $pictureName = Upload::uploadFile(
            [
                'name' => 'photo.png',
                'tmp_name' => $temporaryFile,
                'size' => filesize($temporaryFile)
            ],
            'article-2',
            function (string $source, string $target): bool {
                return copy($source, $target);
            },
            $this->uploadDirectory
        );

        $this->assertSame('article-2.png', $pictureName);
        $this->assertFileExists($this->uploadDirectory . DIRECTORY_SEPARATOR . $pictureName);
    }

    public function testUploadRejectsAnUnsupportedExtension(): void
    {
        $temporaryFile = $this->createTemporaryFile('pdf-content');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This file extension is not allowed. Please upload a JPEG or PNG file');

        Upload::uploadFile(
            [
                'name' => 'document.pdf',
                'tmp_name' => $temporaryFile,
                'size' => filesize($temporaryFile)
            ],
            'article-3',
            function (string $source, string $target): bool {
                return copy($source, $target);
            },
            $this->uploadDirectory
        );
    }

    private function createTemporaryFile(string $content): string
    {
        $temporaryFile = tempnam(sys_get_temp_dir(), 'upload-test-');

        if ($temporaryFile === false) {
            throw new \RuntimeException('Unable to create temporary file.');
        }

        file_put_contents($temporaryFile, $content);

        return $temporaryFile;
    }
}
