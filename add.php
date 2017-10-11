<?php
/**
 * Created by PhpStorm.
 * User: yarbs
 * Date: 03/10/2017
 * Time: 03:32 PM
 */
include_once("Blockchain.php");
use Blockchain\Blockchain;


$blockchain = new Blockchain("blockchain.dat");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = array("data"=>$_POST["data"],
        "signature"=>$_POST["sig"],
        "public_key"=>$_POST["key"]);
    $blockchain->addBlock(json_encode($data));
}

//header( 'Location: /');