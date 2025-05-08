<?php

arch('models')
    ->expect('App\Models')
    ->toUseTrait('App\Traits\Models\InteracstsWithModelCaching');
