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
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css"
          integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
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
                <div class='card-header bg-success'>Block# <?php print $_GET['block_id'] ?></div>
                <div class="card-body" style="word-wrap: break-word">
                    <table class="table">
                        <tbody>
                        <?php
                        $block = $blockchain->getBlock($_GET['block_id']);
                        if ($block != null)
                            foreach ($block as $key => $value) {
                                if ($key == "data") {

                                    foreach (json_decode($value) as $keyData => $valueData) {
                                        print "<tr>";
                                        print "<th>" . $keyData . "</th>";
                                        print "<td style='word-wrap: break-word;'>" . wordwrap($valueData, 72, "<br>", true). "</td>";
                                        print "</tr>";
                                    }
                                } else {
                                    print "<tr >";
                                    print "<th>" . $key . "</th>";
                                    print "<td style='word-wrap: break-word;'>" . $value . "</td>";
                                    print "</tr >";
                                }
                            }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
        integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN"
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"
        integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4"
        crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js"
        integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1"
        crossorigin="anonymous"></script>
</body>
</html>


