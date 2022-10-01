<?php

require_once "vendor/autoload.php";
require_once "common.php";

use src\Replace;

var_dump((new Replace())->run());
