<?php
$config = [
    'storage_directory' => __DIR__ . '/files/',
    'max_size' => 50000000000, 
    'db_dsn' => 'mysql:host=;dbname=',
    'db_user' => '',
    'db_pass' => ''
];

try {
    $db = new \PDO($config['db_dsn'], $config['db_user'], $config['db_pass']);
} catch (PDOException $e) {
    die('Connect Error (' . $e->getCode() . ') '
        . $e->getMessage());
}

$mode_id = str_replace('/', '', $_SERVER['REQUEST_URI']);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>zpcz.eu</title>
    <link rel="stylesheet" href="https://bootswatch.com/cyborg/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
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
            <a class="navbar-brand" href="/">zpcz.eu</a>
        </div>

        <div class="collapse navbar-collapse" id="main-nav-collapse">
            <ul class="nav navbar-nav navbar-right">
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
                    if (!($stmt = $db->prepare('INSERT INTO `videos` (`hash`, `type`, `added`) VALUES (:hash, :type, :added)'))) {
                        echo 'Wystąpił błąd: (' . $db->errorCode() . ') ' . $db->error;
                    }

                    $dateNow = date('Y-m-d H:i:s');

                    $stmt->bindParam(':hash', $new_id, \PDO::PARAM_STR);
                    $stmt->bindParam(':type', $fType, \PDO::PARAM_STR);
                    $stmt->bindParam(':added', $dateNow, \PDO::PARAM_STR);

                    if ($stmt->execute()) {
                        die('<span>Udało się zuploadować twój film z ID `<a href="/' . $new_id . '">' . $new_id . '</a>`');
                    }
                }

                die('Przepraszamy, ale znależliśmy błąd w twoim pliku.');
            } else {
                ?>
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="uploadInput" class="uploadCover btn btn-default">
                            <input class="uploadinput" type="file" style=" height: 0; width: 0;" name="upload"
                                   id="uploadInput">
                            <span><span class="glyphicon glyphicon-upload"></span> Wybierz filmik do wysłania</span>
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
                    <?php

                }
            } else {
                ?>
                <div class="text-center">
                    <h1>404</h1>
                    <h2>Ten film nie istnieje!</h2>
                </div>
                <?php
            }
        } else {
            ?>
            <div class="container well">
			<center><h3>zpcz.eu to prywatny hosting filmów - nie musisz martwić się o ich usunięcie.</h3></center><br>
			<center><h5>Kontakt: <a href="mailto:kontakt@zpcz.eu">kontakt@zpcz.eu</a> <br />Sprawy dot. praw autorskich: <a href="mailto:dmca@zpcz.eu">dmca@zpcz.eu</a></h5></center><br>
                <input title="Szukaj" id="id" type="text" name="id" class="big-search" autocomplete="off"/>
                <script type="text/javascript">
                    $('#id').keypress(function () {
                        if (event.which == 13) {
                            var id = $(this).val();
                            window.location = "/" + id + "/";
                        }
                    });
                </script>
				<center><h6>zpcz ❤ <a href="https://github.com/vistafan12/zpcz">src</a></h6></center>
            </div>
            <?php
        }
        ?>
    </div>
</div>
</body>
</html>
