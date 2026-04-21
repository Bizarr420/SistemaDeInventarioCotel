<?php

namespace App\Console\Commands;

use App\Jobs\CheckObsoleteness;
use Illuminate\Console\Command;

class CheckObsoletenessCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'obsolescence:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for obsolete assets and generate alerts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        CheckObsoleteness::dispatch();
        $this->info('Obsoleteness check job dispatched.');
    }
}