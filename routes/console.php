<?php

use App\Console\Commands\ImportProductsCommand;
use Illuminate\Support\Facades\Schedule;

Schedule::command(ImportProductsCommand::class)->dailyAt(env('CRON_RUN_TIME', '00:00'));