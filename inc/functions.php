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
    //var_dump($contenu);
}


function afficheQuestion($idquestion, $iduser) {

    require 'db.php';

    $req = $pdo->prepare("SELECT enonce FROM questions WHERE id = $idquestion");
    $req->execute();
    $row = $req->fetchAll();

    $array = (array)$row[0];

    echo'
         <h4>' . $array['enonce'] . '</h4>
        ';
    afficheReponses($idquestion, $iduser);

}

function afficheReponses($idReponse, $iduser) {

    require 'db.php';

    $req = $pdo->prepare("SELECT * FROM reponses WHERE id_question = $idReponse");
    $req->execute();
    $row = $req->fetchAll();

    $row = (array)$row;

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
    $valeur = $_POST['valeur'];

    if($valeur == 1){
        vrai($idReponse, $iduser);
    }
    else{
        faux($idReponse, $iduser);
    }
}

function vrai($idReponse, $iduser)
{
    require 'db.php';



    $req = $pdo->prepare("SELECT score FROM users WHERE  id=$iduser");
    $req->execute();
    $row = $req->fetchAll();

    $score = (array)$row[0];
    $score = $score["score"];
    $score =  $score + 10;
    echo '<h5>Bonne réponse - score '.$score.'</h5>';
    $req = $pdo->prepare("UPDATE users SET score=$score WHERE id=$iduser");
    $req->execute();




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
