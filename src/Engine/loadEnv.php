<?php

namespace App\Engine;

class loadEnv{
    public function __construct()
    {
        $file = __DIR__ . '/../../.env';
        $contentFile = parse_ini_file($file);
        foreach ($contentFile as $key => $value)
            $_ENV[$key]=$value;
        
    }
}