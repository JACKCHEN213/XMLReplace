<?php

$config_mapping = require_once "config/common.php";

function config($name)
{
    global $config_mapping;
    return $config_mapping[$name] ?? null;
}
