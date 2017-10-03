<?php
/**
 * Created by PhpStorm.
 * User: yarbs
 * Date: 03/10/2017
 * Time: 03:32 PM
 */
include_once ("blockchain.php");
use blockchain\blockchain;


$blockchain = new blockchain("blockchain.dat");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    print $blockchain->addBlock($_POST["data"]);
}

header( 'Location: /');