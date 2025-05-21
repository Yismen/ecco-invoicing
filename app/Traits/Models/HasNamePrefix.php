<?php

namespace App\Traits\Models;

trait HasNamePrefix
{
    public function getinvoicePrefixAttribute()
    {

        $name = str($this->attributes['name'])->upper()->replace(['-', '_', '/', ',', '\''], ' ');

        $name = preg_replace('/s+/', ' ', $name);

        $name = explode(" ", $name, 3);

        return $name[0] . (str($name[1] ?? '')->substr(0, 1)) . (str($name[2] ?? '')->substr(0, 1));
    }
}
