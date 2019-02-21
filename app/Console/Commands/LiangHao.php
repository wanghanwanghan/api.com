<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use QL\Ext\Chrome;
use QL\QueryList;

class LiangHao extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:lianghao';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '爬靓号';

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
        //联通靓号url
        $url='http://num.10010.com/lianghao/list?goodNumAttr=featureNiceType';

        $ql=QueryList::getInstance();

        $ql->use(Chrome::class);

        //控制最多循环
        $i=1;

        while ($i<=30)
        {
            $res=$ql->chrome($url)->find('.number')->texts()->toArray();

            if (!empty($res))
            {
                break;
            }

            $i++;
        }

        if (empty($res)) exit;

        foreach ($res as $ongPhone)
        {
            preg_match_all('/\d+/',$ongPhone,$value);

            $value=$value[0][0].$value[0][1];

            \App\Http\Model\lianghao::firstOrCreate(['phone'=>$value]);
        }

        $time=date('Y-m-d',time());

        $tot=count($res);

        Log::info($time.'：'.$tot);
    }
}
