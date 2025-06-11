<?php

namespace App\Traits\Models;

trait HasNamePrefix
{
    public function invoiceNamePrefix(null|int $length = null): string
    {

        $name = str($this->attributes['name'])->upper()->replace(['-', '_', '/', ',', '\''], ' ');

        $name = preg_replace('/s+/', ' ', $name);

        // $name = explode(" ", $name, 3, PREG_SPLIT_NO_EMPTY);

        // return $name[0] . (str($name[1] ?? '')->substr(0, 1)) . (str($name[2] ?? '')->substr(0, 1));

        $name = explode(" ", $name, 2);

        return $length ? substr($name[0], 0, $length) : $name[0];
    }
}
