<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CronTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crontest:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '别动我代码mlbd，爬英文网站';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        file_get_contents('http://47.106.169.68/en_web');
        Log::info('I say dont touch my code on'.time());
    }
}
