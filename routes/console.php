<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('telescope:prune --hours=120')
    ->daily();
