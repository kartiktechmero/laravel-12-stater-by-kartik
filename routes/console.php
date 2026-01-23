<?php

use App\Console\Commands\DailyAppleSubscriptionValidateCommand;

Schedule::command(DailyAppleSubscriptionValidateCommand::class)->daily();
