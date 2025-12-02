<?php

namespace Gogol\Invoices\Helpers\Banks\Concerns;

use Illuminate\Support\Facades\Log;

trait HasLogger
{
    protected static $command;

    public static function setCommand($command)
    {
        self::$command = $command;
    }

    public function log($message)
    {
        $message = '['.class_basename($this).':'.$this->account->name.'] ' . $message;

        if ( self::$command ) {
            self::$command->info($message);
        }

        Log::channel('bank_accounts')->info($message);
    }

    public function error($message)
    {
        $message = '['.class_basename($this).':'.$this->account->name.'] ' . $message;

        if ( self::$command ) {
            self::$command->error($message);
        }

        Log::channel('bank_accounts')->error($message);
    }
}
