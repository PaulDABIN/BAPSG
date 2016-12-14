<?php
function debug($variable){
    echo '<pre>' . print_r($variable, true) . '</pre>';
}

function str_random($length){
    $alphabet = "0123456789azertyuiopqsdfghjklmwxcvbnAZERTYUIOPQSDFGHJKLMWXCVBN";
    return substr(str_shuffle(str_repeat($alphabet, $length)), 0, $length);
}

function logged_only(){
    if(session_status() == PHP_SESSION_NONE){
        session_start();
    }
    if(!isset($_SESSION['auth'])){
        $_SESSION['flash']['danger'] = "Vous n'avez pas le droit d'accéder à cette page";
        header('Location: login.php');
        exit();
    }
}

function reconnect_from_cookie(){
    if(session_status() == PHP_SESSION_NONE){
        session_start();
    }
    if(isset($_COOKIE['remember']) && !isset($_SESSION['auth']) ){
        require_once 'db.php';
        if(!isset($pdo)){
            global $pdo;
        }
        $remember_token = $_COOKIE['remember'];
        $parts = explode('==', $remember_token);
        $user_id = $parts[0];
        $req = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $req->execute([$user_id]);
        $user = $req->fetch();
        if($user){
            $expected = $user_id . '==' . $user->remember_token . sha1($user_id . 'ratonlaveurs');
            if($expected == $remember_token){
                session_start();
                $_SESSION['auth'] = $user;
                setcookie('remember', $remember_token, time() + 60 * 60 * 24 * 7);
            } else{
                setcookie('remember', null, -1);
            }
        }else{
            setcookie('remember', null, -1);
        }
    }
}

function affiche ($id_case) {

    require 'db.php';

    $req = $pdo->prepare("SELECT * FROM cases WHERE id = $id_case");
    $req->execute([$_POST['type']]);
    $contenu=$req->fetch();
}


function afficheQuestion($idquestion, $iduser) { //fonction pour afficher l'énoncé du question.
// L'id de la question et l'id user sont  passés en variables.

    require 'db.php';

    $req = $pdo->prepare("SELECT enonce FROM questions WHERE id = $idquestion");
    $req->execute();
    $row = $req->fetchAll();

    $array = (array)$row[0];//je recupère et je stocke l'énoncé correspond à l'id donné en paramètre.


//je l'affiche
    echo'
         <h4>' . $array['enonce'] . '</h4>
        ';

    //je lance la question pour afficher les propositions de réponses
    afficheReponses($idquestion, $iduser);

}

function afficheReponses($idquestion, $iduser) { //meme variables que dans la fonction au dessus (id de la question et id user)



    require 'db.php';

    $req = $pdo->prepare("SELECT * FROM reponses WHERE id_question = $idquestion");
    $req->execute();
    $row = $req->fetchAll();

    $row = (array)$row; //je récupère et je stock dans $row les 4 propositions de réponses
    // pour cette question (texte et valeur (vrai ou faux))


    //j'affiche toutes les réponses en mettant en input hidden les infos que je veux faire passer (valeur) dans les afficher
    for($i=0;$i<4;$i++){
        $array =(array)$row[$i];

        echo '<form method="POST" action="index.php">';

        echo'
           <input type="hidden" name="explication" value='.$array['explication'].'>
           <input type="hidden" name="valeur" value='.$array['valeur'].'>
         ';

        echo'<input type="submit" id='.$i.' value='.$array['texte'].'>';

        echo '</form>';
    }

    //je récupère la valeur de la varaible $valeur du bouton cliqué (réponse choisi). Si $_POST['valeur'] = 1 alors c'est la
    //bonne réponse sinon c'est la mauvaise
    $valeur = $_POST['valeur'];



    if($valeur == 1){
        vrai($idquestion, $iduser);
    }
    else{
        faux($idquestion, $iduser);
    }
}

//dans les function vrai et faux je récupère et je modifie le score de l'utilisteur

function vrai($idReponse, $iduser)
{
    require 'db.php';


    $req = $pdo->prepare("SELECT score FROM users WHERE  id=$iduser");
    $req->execute();
    $row = $req->fetchAll();


    $score = (array)$row[0];
    $score = $score["score"];
    $score =  $score + 10;

    //je récupère, je stocke et j'implémente le score

    echo '<h5>Bonne réponse - score '.$score.'</h5>'; //je l'affiche


    $req = $pdo->prepare("UPDATE users SET score=$score WHERE id=$iduser"); //je fais un update du score
    $req->execute();

    $req = $pdo->prepare("SELECT avancement FROM users WHERE id=$iduser");
    $req->execute();
    $row = $req->fetchAll();   //je récupère la variable d'avancement de l'user

    $avanc = (array)$row[0];
    $avanc = $avanc["avancement"]; //je l'implémente car il vient de répondre à une question
    $avanc =  $avanc +1 ;


    $req = $pdo->prepare("UPDATE users SET avancement=$avanc WHERE id=$iduser"); //j'update le tout
    $req->execute();

    echo '<form method="POST" action="index.php">';   //form pour lancer la question d'apres
    echo' <input type="hidden" name="idreponse" value='.$idReponse.'>';
    echo' <input type="hidden" name="iduser" value='.$iduser.'>';
    echo' <input type="submit" onclick="vider()" name="push" value="Suivant">';
    echo '</form>';


}

//meme code qu'au dessus mais je mets - 10 au lieu de + 10 au score.

function faux($idReponse, $iduser){
    require 'db.php';

    $idReponse = $idReponse;

    $req = $pdo->prepare("SELECT score FROM users WHERE id=$iduser");
    $req->execute();
    $row = $req->fetchAll();

    $score = (array)$row[0];
    $score = $score["score"];
    $score =  $score - 10;

    echo '<h4>Mauvaise réponse  - score '.$score.'</h4>';

    $req = $pdo->prepare("UPDATE users SET score=$score WHERE  id=$iduser");
    $req->execute();


    $req = $pdo->prepare("SELECT avancement FROM users WHERE id=$iduser");
    $req->execute();
    $row = $req->fetchAll();

    $avanc = (array)$row[0];
    $avanc = $avanc["avancement"];
    $avanc =  $avanc +1 ;


    $req = $pdo->prepare("UPDATE users SET avancement=$avanc WHERE id=$iduser");
    $req->execute();

    echo '<form method="POST" action="index.php">';
    echo' <input type="hidden" name="idreponse" value='.$idReponse.'>';
    echo' <input type="hidden" name="iduser" value='.$iduser.'>';
    echo' <input type="submit" onclick="vider()" name="push" value="Suivant">';
    echo '</form>';

}
