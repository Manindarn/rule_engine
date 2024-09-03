<?php
namespace App\Message;

class ScanFilesMessage
{
    private int $uploadId;

    public function __construct(int $uploadId)
    {
        $this->uploadId = $uploadId;
    }

    public function getUploadId(): int
    {
        return $this->uploadId;
    }
}
