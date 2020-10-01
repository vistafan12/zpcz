<?php
include 'config.php';
session_start();
$config = [
    'storage_directory' => __DIR__ . '/files/',
    'max_size' => 12000000000, 
    'db_dsn' => 'mysql:host=;dbname=',
    'db_user' => 'm',
    'db_pass' => ''
];

try {
    $db = new \PDO($config['db_dsn'], $config['db_user'], $config['db_pass']);
} catch (PDOException $e) {
    die('Connect Error (' . $e->getCode() . ') '
        . $e->getMessage());
}
if(isset($_POST['login'])) {
  $dzisiejsza_data = date("Y-m-d H:i:s");
  $username = $_POST['username'];
  $stmt = $PDOdbconn->prepare('SELECT * FROM users WHERE username = :username');
  $change_date = $PDOdbconn->prepare("UPDATE users SET lastlogin='$dzisiejsza_data' WHERE username='$username'");
  $change_date->execute();
    $stmt->bindValue('username', $_POST['username']);
    $stmt->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
    $stmt->execute();
    $account = $stmt->fetch(PDO::FETCH_OBJ);
    if($account != NULL) {
        if(password_verify($_POST['password'], $account->password)) {
            $_SESSION['username'] = $_POST['username'];
            header('location:/');
        } else {
            $error = 'Konto nieznalezione!';
        }
    } else {
        $error = 'Konto nieznalezione!';
    }

}
if(isset($_POST['register'])) {
    $stmt = $PDOdbconn->prepare("INSERT INTO users(username,email,password,register_date) VALUES(:username, :email, :password, :reg_date);");
    $stmt->setAttribute(PDO::ATTR_EMULATE_PREPARES, 0);
    $stmt->bindValue('username', $_POST['username']);
    $stmt->bindValue('password', password_hash($_POST['password'], PASSWORD_BCRYPT));
    $stmt->bindValue('email', $_POST['email']);
    $stmt->bindValue('reg_date', date('Y-m-d'));
    $stmt->execute();
    header('location:/');
}
function user_ZPCZ() {
    if (isset($_SESSION['username'])) {
        echo '<li><a href="/profile"><span class="glyphicon glyphicon-user"></span> ' . $_SESSION['username'] . '</a> </li>';
        echo '<li><a href="/server.php?action=logout"><span class="glyphicon glyphicon-off"></span> Wyloguj się</a></li>';
    } else { 
        echo '<li><a href="/login"><span class="glyphicon glyphicon-user"></span> anon </a></li>';
    }
}
function user_videos_ZPCZ($username) {
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name); 
$query = 'SELECT * FROM videos where user="'. $username .'"';
if ($result = $mysqli->query($query)) {
 
    while ($row = $result->fetch_assoc()) {
        $vid = $row["hash"];
        $tytul = $row["title"];
        echo '';
         if($tytul!=null) echo $tytul; else echo '<i>brak tytułu</i>';
        echo ' - <a href="/'.$vid.'">'.$vid.'</a><br>';
    }
        $result->free();
    }
}
    if (isset($_SESSION['username'])) {
        $user_vid = $_SESSION['username'];
    } else { 
        $user_vid = 'anon';
    } 
$mode_id = str_replace('/', '', $_SERVER['REQUEST_URI']);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>zpcz</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootswatch/3.4.1/cyborg/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="http://cdnjs.cloudflare.com/ajax/libs/animate.css/3.4.0/animate.min.css">
    <style type="text/css">
        .big-search {
            padding-bottom: 0;
            border: transparent;
            border-bottom: 1px solid #008F0D;
            background: transparent;
            height: 40px;
            color: white;
            line-height: 45px;
            font-size: 40px;
            text-align: center;
            width: 100%;
            outline: none;
            max-width: 800px;
        }
	 h5 {
	color: #888888;
	}

	    
         h6 {
    
	color: #888888;
	}
    .form-control {
        background-color: transparent;
    }
        label.uploadCover {
            display: block;
            cursor: pointer;
        }

        label.uploadCover input[type="file"] {
            opacity: 0;
            cursor: pointer;
        }

        .main-vid {
            max-width: 100%;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                    data-target="#main-nav-collapse" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="/">zpcz</a>
        </div>

        <div class="collapse navbar-collapse" id="main-nav-collapse">
            <ul class="nav navbar-nav navbar-right">
                 <? user_ZPCZ(); ?>
                <li><a href="/upload"><span class="glyphicon glyphicon-upload"></span> Dodaj</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="container-fluid">
    <div class="text-center">
        <?php
        if ($mode_id == 'upload') {
            if (isset($_FILES['upload'])) {
                $new_id = uniqid();
                $title = $_POST["title"];
                $target_file = $config['storage_directory'] . $new_id;
                $tFile = $config['storage_directory'] . basename($_FILES['upload']['name']);
                $fType = pathinfo($tFile, PATHINFO_EXTENSION);
                $target_file = $target_file . '.' . $fType;

                if (file_exists($target_file)) {
                    die('Ten film istnieje w naszej bazie danych.');
                }

                if ($_FILES['upload']['size'] > $config['max_size']) {
                    die('Ten film waży za dużo!');
                }

                if ($fType != 'mp4' && $fType != 'mkv' && $fType != 'wmv') {
                    die('Przepraszamy, tylko MP4, MVP i WMV jest dozwolony.');
                }
                if (move_uploaded_file($_FILES['upload']['tmp_name'], $target_file)) {
                    if (!($stmt = $db->prepare('INSERT INTO `videos` (`hash`, `type`, `added`, `user`, `title`) VALUES (:hash, :type, :added, :user, :title)'))) {
                        echo 'Wystąpił błąd: (' . $db->errorCode() . ') ' . $db->error;
                    }

                    $dateNow = date('Y-m-d H:i:s');

                    $stmt->bindParam(':hash', $new_id, \PDO::PARAM_STR);
                    $stmt->bindParam(':type', $fType, \PDO::PARAM_STR);
                    $stmt->bindParam(':added', $dateNow, \PDO::PARAM_STR);
                    $stmt->bindParam(':user', $_SESSION['username'], \PDO::PARAM_STR);
                    $stmt->bindParam(':title', $title, \PDO::PARAM_STR);

                    if ($stmt->execute()) {
                        die('<span>Udało się zuploadować twój film z ID `<a href="/' . $new_id . '">' . $new_id . '</a>`');
                    }
                }

                die('Przepraszamy, ale znależliśmy błąd w twoim pliku.');
            } else {
                ?>

                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                    <label for=""><i>Tytuł (max. 140 znaków, niewymagany)</i></label>
                    <input type="text" name="title" class="form-control"/>
                    </div>
                    <div class="form-group">
                        <label for="uploadInput" class="uploadCover btn btn-default">
                            <input class="uploadinput" type="file" style=" height: 0; width: 0;" name="upload"
                                   id="uploadInput">
                            <span><span class="glyphicon glyphicon-upload"></span> Wybierz film do wysłania</span>
                        </label>
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn btn-default" value="Dodaj"/>
                    </div>
                </form>
                <?php
            }
        } elseif ($mode_id !== '') {
            ?>
            <?php
            if (!($stmt = $db->prepare('SELECT * FROM `videos` WHERE `hash` = :hash'))) {
                echo 'Bład: (' . $db->errorCode() . ') ' . $db->error;
            }
            if (!$stmt->bindParam(':hash', $mode_id, \PDO::PARAM_STR)) {
                echo 'Parametry bitratowe są złe: (' . $stmt->errorCode() . ') ' . $stmt->errorInfo()[2];
            }
            if (!$stmt->execute()) {
                echo 'Błąd: (' . $stmt->errorCode() . ') ' . $stmt->errorInfo()[2];
            }

            if ($video = $stmt->fetch(\PDO::FETCH_OBJ)) {
                if ($stmt !== null && file_exists($config['storage_directory'] . $video->hash . '.' . $video->type)) {
                    ?>
                    <video class="main-vid" controls>
                        <source src="/files/<?= $video->hash . '.' . $video->type ?>" type="video/<?= $video->type ?>">
                        <p>Twoja przeglądarka nie wspiera <?= $video->type ?>.</p>
                    </video>
                    <center> <h3><?if ($video->title != null){ ?> <?=$video->title ?> <? } else { ?> <i>brak tytułu</i> <? } ?></h3> </center>
                    <center> <small> dodane przez: <?if ($video->user != null){ ?> <?=$video->user ?>. <? } else { ?> anon.<? } ?> </small> </center>
                    <?php

                }
            } else {
                ?>

                <?php
            }
        } else {
            ?>
            <div class="container well">
			<center><h3>zpcz to prywatny hosting filmów - nie musisz martwić się o ich usunięcie.</h3></center><br>
			<center><h5>Kontakt: <a href="mailto:kontakt@vistafan12.eu.org">kontakt@vistafan12.eu.org</a> <br />Sprawy dot. praw autorskich: <a href="mailto:dmca@vistafan12.eu.org">dmca@vistafan12.eu.org</a></h5></center><br>
                <input title="Szukaj" id="id" type="text" name="id" class="big-search" autocomplete="off"/>
                <script type="text/javascript">
                    $('#id').keypress(function () {
                        if (event.which == 13) {
                            var id = $(this).val();
                            window.location = "/" + id + "/";
                        }
                    });
                </script>
				<center><h6><a href="changelog.html"> changelog </a> | 2016-<? echo date("Y"); ?> vistafan12 | zpcz ❤ <a href="https://github.com/vistafan12/zpcz">src</a></h6></center>
            </div>
            <?php
        }
        if ($mode_id == 'login') {
            if(isset($_SESSION['username'])) {
                header('location:/profile');
            }
            ?>
            <div class="container well">
                <h4> Logowanie </h4>
                <form action="" method="post">
                <div class="form-group">
                    <label for="">Nazwa użytkownika</label>
                    <input type="text" name="username" class="form-control"/>
                </div>
                <div class="form-group">
                    <label for="">Hasło</label>
                    <input type="password" name="password" class="form-control"/>
                </div>
                <div class="form-group">
                    <input type="submit" name="login" class="btn btn-default" value="Zaloguj"/>
                </div>
            </form>
            <a href="/register"> Nie masz konta? Zarejestruj się </a>
            </div>
       <?
        }
         if ($mode_id == 'register') {
            if(isset($_SESSION['username'])) {
                header('location:/profile');
            }
        ?>
        <div class="container well">
                <h4> Rejestracja </h4>
                <form action="" method="post">
                <div class="form-group">
                    <label for="">Nazwa użytkownika</label>
                    <input type="text" name="username" class="form-control"/>
                </div>
                <div class="form-group">
                    <label for="">Hasło</label>
                    <input type="password" name="password" class="form-control"/>
                </div>
                <div class="form-group">
                    <label for="">E-Mail</label>
                    <input type="text" name="email" class="form-control"/>
                </div>
                <div class="form-group">
                    <input type="submit" name="register" class="btn btn-default" value="Zarejestruj się"/>
                </div>
            </form>
            <a href="/login"> Masz konto? Zaloguj się </a>
        </div>
    <? } 
    if ($mode_id == 'profile') { 
            if(!isset($_SESSION['username'])) {
                header('location:/login');
            }
        ?>
        <div class="container well">
            <h3> Twój profil </h3>
            <center><small><i>(psst. tylko ty możesz zobaczyć tą stronę. jestem (vistafan12) leniem i nie chce mi się robić profilu publicznego dla każdego usera)</i></small></center>
            <h4> Dodane przez ciebie filmy: </h4>
            <?= user_videos_ZPCZ($_SESSION['username']) ?>
    </div>
<? } ?>
</div>
</body>
</html>
