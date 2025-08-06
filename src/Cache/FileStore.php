<?php

namespace Lugege\LaravelAspect\Cache;
use Illuminate\Cache\FileStore as IlluminateFileStore;
use Illuminate\Filesystem\Filesystem;
class FileStore extends IlluminateFileStore
{
    public function __construct(Filesystem $files, $directory = null)
    {
        $directory = $directory ?: app_path('runtime/aspect/cache');
        // 自定义缓存目录
        parent::__construct($files, $directory,'0666');
    }
}
