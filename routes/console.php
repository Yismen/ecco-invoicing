<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('telescope:prune --hours=120')
    ->daily();

Schedule::command('app:update-invoices-status')
    ->dailyAt('2:00')
    ->withoutOverlapping();
