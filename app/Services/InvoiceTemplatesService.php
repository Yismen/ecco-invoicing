<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class InvoiceTemplatesService
{
    public $path;

    public function __construct(string|null $path = null)
    {
        if ($path) {
            $this->path = $path;
        }

        $this->path = $this->path ?: resource_path('views/vendor/invoices/templates');
    }

    public static function make(string|null $path = null): array
    {
        $static = new static($path);

        return $static->getFiles();
    }

    public function getFiles(): array
    {
        $files = [];

        foreach( Storage::build([
            'driver' => 'local',
            'root' => $this->path,
        ])->allFiles('') as $file) {
            $file = str($file)->before('.blade.php')->toString();
            $files[$file] = $file;
        }

        return $files;
    }
}
