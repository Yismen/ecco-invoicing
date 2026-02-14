<?php

namespace App\Services;

use Exception;
use ZipArchive;

class ZipService
{
    protected array $files = [];

    protected string $zipFilePath;

    protected string $zipFileName;

    protected string $zipFullFilename;

    protected bool $removeFilesAfterCompletion = false;

    public function createZip(
        array $files,
        string $zipFileName = '',
        string $zipFilePath = '',
        bool $removeFilesAfterCompletion = false
    ): self {
        $this->files = $files;
        $this->zipFileName = $zipFileName ?: 'downloads_'.now()->format('YmdHis').'.zip';
        $this->zipFilePath = $zipFilePath ?: storage_path('app/public/temp/');
        $this->removeFilesAfterCompletion = $removeFilesAfterCompletion;
        $this->zipFullFilename = $this->zipFilePath.$this->zipFileName;

        if (is_dir($this->zipFilePath) === false) {
            mkdir($this->zipFilePath, 0777, true);
        }

        if (\count($files) === 0 || empty($files)) {
            throw new Exception('No files added...');
        }

        $zip = new ZipArchive;
        if ($zip->open($this->zipFullFilename, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($this->files as $filePath) {
                $zip->addFile($filePath, \basename($filePath));
            }
            $zip->close();
        }

        return $this;
    }

    public function shouldRemoveFilesAfterCompletion(bool $condition = true): self
    {
        $this->removeFilesAfterCompletion = $condition;

        return $this;
    }

    public function download()
    {
        if ($this->removeFilesAfterCompletion === true) {

            foreach ($this->files as $filePath) {
                if (\file_exists($filePath)) {
                    \unlink($filePath);
                }
            }
        }

        return \response()
            ->download($this->zipFullFilename, $this->zipFileName)
            ->deleteFileAfterSend(true);
    }
}
