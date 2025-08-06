<?php
//扫描地址
return [
    #扫描控制器目录
    'scan_dir' => [
        'Http\Controllers'
    ],
    #自动路由
    'autoRoute' => true,
    #排除
    'exclude_namespaces' => [

    ],
    #缓存
    'cache' => [
        'cache_enabled' => env('ASPECT_CACHE', true),
        'cache_driver' => env('ASPECT_CACHE_DRIVER', 'file'),
        'cache_path' => env('ASPECT_CACHE_PATH', app_path('runtime/aspect/cache')),
        'cache_ttl' => 86400,
    ]

];
