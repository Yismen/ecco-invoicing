<?php

namespace App\Services;

use ZipArchive;

class ZipService
{
    protected array $files = [];

    protected string $zipFilePath;

    protected string $zipFileName;

    public function createZip(
        array $files,
        string $zipFileName = '',
        string $zipFilePath = '',
    ): self
    {
        $this->files = $files;
        $this->zipFileName = $zipFileName ?: 'downloads_' . now()->format('YmdHis') . '.zip';
        $this->zipFilePath = $zipFilePath ?: storage_path('app/temp/' . $this->zipFileName);

        if(\count($files) === 0 || empty($files)) {
            throw new \Exception('No files added...');
        }

        $zip = new ZipArchive();
        if ($zip->open($this->zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($this->files as $file) {
                $zip->addFile($file, basename($file));
            }
            $zip->close();
        }

        return $this;
    }

    public function download()
    {
        return \response()->download($this->zipFilePath, $this->zipFileName);
    }
}
