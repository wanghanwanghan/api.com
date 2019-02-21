<?php

return [
    'production' => env('JPUSH_PRODUCTION', true),// 是否是正式环境
    'key' => env('JPUSH_APP_KEY', '65ebbd4f30633c15456516dc'),// key
    'secret' => env('JPUSH_MASTER_SECRET', '1982fb9427498fed6c291ceb'),// master secret
    'log' => env('JPUSH_LOG_PATH', storage_path('logs/jpush.log')), // 日志文件路径
];