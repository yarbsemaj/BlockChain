<?php
/**
 * Created by PhpStorm.
 * User: yarbs
 * Date: 03/10/2017
 * Time: 10:32 AM
 */
include_once("Blockchain.php");
include_once("BlockVerify.php");
use Blockchain\Blockchain;
use Blockchain\BlockVerify;
$blockchain = new Blockchain("blockchain.dat");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
</head>
<body>
<nav class="navbar navbar-light bg-light">
    <span class="h1" class="navbar-brand mb-0">Block Chain</span>
</nav>
<div class="container">
    <br>
        <div class="row">
            <div class="col">

    <div class="card">
        <?php
        if(($blockchain->isValid())){
            print "<div class='card-header bg-success'>Block Chain Intact";}
        else {
            print "<div class='card-header bg-danger'>Block Chain Violated";}
        ?>
        </div>
        <div class="card-body">
<table class="table">
    <thead>
    <tr>
        <th>Block</th>
        <th>Data</th>
        <th>Key</th>
        <th>Verified</th>
        <th>Time Stamp</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if(count ($blockchain->getChain())!=0)
        foreach (array_reverse($blockchain->getChain()) as $block) {
            ?>
    <tr>
        <?php
        $data = json_decode($block["data"]);
        $verfied = BlockVerify::verify($data->data,$data->public_key,$data->signature)? "Verified": "Unverfied";
        print "<th scope='row'>".$block['height']."</th>";
        print "<th>".$data->data."</th>";
        print "<th>".sha1($data->public_key)."</th>";
        print "<th>$verfied</th>";
        print "<th>".date("F j, Y, g:i:s a",$block['timestamp'])."</th>";
        }
        ?>
    </tr>
    </tbody>
</table>
</div>
    </div>
        </div>
        </div>
    </div>
<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
</body>
</html>


