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


//je recupère le header
require 'inc/header.php' ?>

<div class="content">
<img src="img/bg3.png">
<div id="question">

    <?php


    $user = (array)$_SESSION['auth'];
    $iduser = $user["id"]; //je recup l'id de l'user connecté
    require 'inc/functions.php';


    
    function avancement($iduser){ //fonction pour récupérer la derniere question faite par l'user connecté

        require 'inc/db.php';

        $req = $pdo->prepare("SELECT avancement FROM users WHERE id=$iduser");
        $req->execute();
        $row = $req->fetchAll();

        $avancement = (array)$row[0];
        $avancement = $avancement["avancement"];

        //je stocke la variable d'avancement (id de la question où s'est arreté l'user)
        afficheQuestion($avancement, $iduser); //je lance la fonction pour afficher la question et les réponses

        //vu que la variable d'avancement va etre implémenté à chaque fin de réponse elle va afficher la question qui
        //sera la suivante dans la bdd.

    }
    avancement($iduser); //j'execute la fonction avancement

    ?>

</div>
</div>

<script src="http://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script>
    function vider(){    //fonction pour vider la div dans laquelle seront afficher les quiz (un à un)
        $("#question").html("");
        document.getElementById('question').innerHTML= "";
    }
</script>




