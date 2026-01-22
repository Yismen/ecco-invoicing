<?php

use Illuminate\Support\Facades\Cache;
use App\Services\InvoiceTemplatesService;

describe('InvoiceTemplatesService', function () {
    beforeEach(function () {
        $this->temp_path = public_path('storage/temp/invoice_templates/');

        $this->files = [
            'template-one.blade.php',
            'template-two.blade.php',
            'template-three.blade.php',
        ];

        foreach ($this->files as $file) {
            $full_path = $this->temp_path . $file;
            if (!is_dir(dirname($full_path))) {
                mkdir(dirname($full_path), 0777, true);
            }
            file_put_contents($full_path, 'content');
        }
    });

    afterEach(function () {
        foreach ($this->files as $file) {
            $full_path = $this->temp_path . '/' . $file;
            if (file_exists($full_path)) {
                unlink($full_path);
            }
        }
        if (is_dir($this->temp_path)) {
            rmdir($this->temp_path);
        }
    });

    it('should return formatted file names as headlines', function () {
        $expected = [
            'template-one' => 'Template One',
            'template-two' => 'Template Two',
            'template-three' => 'Template Three',
        ];

        $result = InvoiceTemplatesService::make($this->temp_path);

        expect($result)->toEqual($expected);
    });

    it('returns cached array of files', function () {
        $cachedFiles = [
            'cached_template-one' => 'Cached Template One',
            'cached_template-two' => 'Cached Template Two',
            'cached_template-three' => 'Cached Template Three',
        ];

        Cache::put('invoice_template_services', $cachedFiles);

        $result = InvoiceTemplatesService::make();

        expect($result)->toEqual($cachedFiles);
    });
});
