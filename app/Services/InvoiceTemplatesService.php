<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class InvoiceTemplatesService
{
    public $path;

    public function __construct(?string $path = null)
    {
        if ($path) {
            $this->path = $path;
        }

        $this->path = $this->path ?: resource_path('views/vendor/invoices/templates');
    }

    public static function make(?string $path = null): array
    {
        $static = new static($path);

        return $static->getFiles();
    }

    public function getFiles(): array
    {
        return Cache::remember('invoice_template_services', now()->addMinutes(15), function () {
            $files = [];

            foreach (Storage::build([
                'driver' => 'local',
                'root' => $this->path,
            ])->allFiles('') as $file) {
                $file = str($file)->before('.blade.php')->toString();
                $files[$file] = $file;
            }

            return $files;
        });
    }
}
