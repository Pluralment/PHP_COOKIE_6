<?php
if ((!empty($_GET['session_active'])) and ($_GET['session_active'] == 1))
    session_start();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <link href="FormStyles.css" rel="stylesheet">
    <meta charset="UTF-8">
    <?php
    require_once 'Connection.php';
    //session_start();
    if (!(empty($_SESSION['auth'])) and ($_SESSION['auth']))
    {
        $userName = $_SESSION['name'];
        echo '<h3>'."Добро пожаловать,  $userName!".'</h3>';
        $isOnline = true;
    }
    else
    {
        // Закрытие сессии.
        if ((!empty($_GET['session_active'])) and ($_GET['session_active'] == 1))
            session_destroy();
        if ( (!empty($_COOKIE['login'])) and (!empty($_COOKIE['key'])) )
        {
            $login = $_COOKIE['login'];
            $key = $_COOKIE['key'];

            if ($mysqli = connectToDB($host, $userName, $password, $dbName))
            {
                $query = "SELECT * FROM users WHERE user_name = '$login' AND cookie = '$key'";
                if ($result = $mysqli->query($query)->fetch_assoc())
                {
                    session_start();
                    $_SESSION['auth'] = true;
                    $_SESSION['name'] = $result['user_name'];
                    $name = $_SESSION['name'];
                    echo "Пользователь <b>$name</b> найден по cookie<br/>";
                }
            }
            else
            {
                echo "Сервер временно недоступен".'<br/>';
            }
        }
        else
        {
            echo '<h3>'."Войдите в систему".'</h3>';
        }
    }
    ?>
    <title>Форма регистрации/авторизации</title>
</head>
<body>
<div class="formContainer">
    <form action="UserLogCheck.php" method="post">
        <h2>Регистрация</h2>
        <div class="formField inputRight">
            <input name="remember" type='checkbox' value='1'>
        </div>
        <div class="formField inputRight">
            <label for="email">E-mail</label>
            <input type="email" name="email" id="email" placeholder="Youremail@gmail.com" value="">
        </div>
        <div class="formField inputRight">
            <label for="password">Password</label>
            <input type="password" name="pass" id="pass" placeholder="password" value="">
        </div>
        <div class="formField inputRight submitField">
            <input type="submit" value="Отправить">
        </div>
    </form>
</div>
</body>
</html>