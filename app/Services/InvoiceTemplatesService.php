<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class InvoiceTemplatesService
{
    public $path;

    public string $disk;

    public function __construct(?string $path = null, string $disk = 'local')
    {
        if ($path) {
            $this->path = $path;
        }

        $this->path = $this->path ?: resource_path('views/vendor/invoices/templates');
        $this->disk = $disk;
    }

    public static function make(?string $path = null, string $disk = 'local'): array
    {
        $static = new static($path, $disk);

        return $static->getFiles();
    }

    public function getFiles(): array
    {
        return Cache::rememberForever('invoice_template_services', function () {
            $returnFiles = [];
            $files = Storage::build([
                'driver' => $this->disk,
                'root' => $this->path,
            ])->allFiles('');

            foreach ($files as $file) {
                $file = str($file)->before('.blade.php')->toString();
                $returnFiles[$file] = str($file)->headline()->toString();
            }

            return $returnFiles;
        });
    }
}
