<?php

use COREPOS\common\SQLManager;

class MicroLog
{
    public function debug($msg)
    {
        $log = __DIR__ . '\\..\\queries.log';
        $fp = fopen($log, 'a');
        fwrite($fp, $msg);
        fclose($fp);
    }
}

function db()
{
    $dbc = new SQLManager('localhost','MYSQLI','meeting','meeting','meeting');
    $dbc->setQueryLog(new MicroLog());
    return $dbc;
}

