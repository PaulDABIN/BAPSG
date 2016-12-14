<?php
/**
 * Created by PhpStorm.
 * User: Paul
 * Date: 13/12/2016
 * Time: 10:51
 */

?>


<body>

<?php

require 'inc/header.php' ?>

<div class="content">
<img src="img/bg3.png">
<div id="question">

    <?php 
    $user = (array)$_SESSION['auth'];
    $iduser = $user["id"];
    require 'inc/functions.php';
    
    function avancement($iduser){

        require 'inc/db.php';

        $req = $pdo->prepare("SELECT avancement FROM users WHERE id=$iduser");
        $req->execute();
        $row = $req->fetchAll();

        $avancement = (array)$row[0];
        $avancement = $avancement["avancement"];
        afficheQuestion($avancement, $iduser);

    }
    avancement($iduser);

    ?>

</div>
</div>

<script src="http://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="js/quiz.js"></script>
<script>
    function vider(){
        window.alert("zdaazd");
        $("#question").html("");
        document.getElementById('question').innerHTML= "";
    }
</script>
<script src="js/quiz1.js"></script>




