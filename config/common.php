<?php

use tools\Logger;

return [
    'template_path' => 'resources/template/',
    'source_path' => 'resources/source/',
    'output_path' => 'resources/output/',

    'follow_source' => false, // 文件对比, 是否使用目前文件的

    'level' => Logger::DEBUG,

    'translate_path' => 'resources/translate/',
];
