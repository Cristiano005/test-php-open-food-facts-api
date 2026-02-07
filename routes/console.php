<?php

use App\Console\Commands\ImportProductsCommand;
use Illuminate\Support\Facades\Schedule;

Schedule::command(ImportProductsCommand::class)->dailyAt('06:21');