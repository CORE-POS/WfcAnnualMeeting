<?php

function getArrivedStatus($db)
{
    $q = "SELECT typeDesc,pending,arrived,ttl 
        FROM arrivals as a left join mealttl as t
        ON a.typeDesc=t.name";
    $r = $db->query($q);
    $arr = array();
    while ($w = $db->fetch_row($r)) {
        $arr[] = $w;
    }

    return $arr;

}

