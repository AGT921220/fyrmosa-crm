<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('crm:expire-quotations')->daily();
