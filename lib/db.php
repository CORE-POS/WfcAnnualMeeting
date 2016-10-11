<?php

use COREPOS\common\SQLManager;

function db()
{
    $dbc = new SQLManager('localhost','MYSQL','meeting','meeting','meeting');
    return $dbc;
}

