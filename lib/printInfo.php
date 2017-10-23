<?php

if (!function_exists("printImage")) include_once(__DIR__ . '/printLib.php');

function print_info($INFO){
    //return;
    $receipt = "";
    $dts = 0;
    foreach ($INFO['meals'] as $mtype) {
        $receipt .= centerString("Dinner Choice")."\n";
        switch (strtolower($mtype)) {
        case 'salmon':
            $receipt .= printImage("images/s_bpp.bmp");
            $dts++;
            break;
        case 'salmonnosauce':
            $receipt .= printImage("images/s.bmp");
            $dts++;
            break;
        case 'chicken':
            $receipt .= printImage("images/c_bpp.bmp");
            $dts++;
            break;
        case 'chickennosauce':
            $receipt .= printImage("images/c.bmp");
            $dts++;
            break;
        case 'tempeh':
            $receipt .= printImage("images/v_bpp.bmp");
            $dts++;
            break;
        case 'kid':
            $receipt .= printImage("images/k_bpp.bmp");
            break;
        }
        $receipt .= centerString("Please place this ticket face up on the table")."\n";
        $receipt .= centerString("visible so the server knows your meal choice")."\n";
        $receipt .= cut();
    }
    /*
    for ($i=0; $i<$dts*2;$i++){
        $receipt .= biggerFont(centerBig("Free Drink Ticket"))."\n\n";
        $receipt .= centerString("This drink is good for one designated soda, beer")."\n"; 
        $receipt .= centerString("or wine. Ticket must be surrendered to the")."\n"; 
        $receipt .= centerString("bartender in exchange for beverages.")."\n";
        $receipt .= boldFont();
        $receipt .= centerString("You must be at least 21 years old to redeem")."\n"; 
        $receipt .= centerString("for alcoholic beverages.")."\n";
        $receipt .= normalFont();
        $receipt .= cut();
    }
    */

    for($i=0;$i<2;$i++){
        $receipt .= centerString("Raffle Ticket - Your number is:")."\n\n";
        $receipt .= biggerFont(centerBig($INFO['card_no']))."\n\n";
        $receipt .= centerString("Please hold this ticket for the raffle")."\n";
        $receipt .= cut();
    }

    /** Disabled; all pre-pay
    if (!isset($INFO['amt']) || $INFO['amt'] == 0){
        $receipt .= biggerFont(centerBig("PAID IN FULL"))."\n";
        $receipt .= cut();
    }
    else {
        $receipt .= biggerFont(centerBig(sprintf('AMOUNT DUE: $%.2f',$INFO['amt'])))."\n";
        $receipt .= cut();
    }
    */

    writeLine($receipt);
}

