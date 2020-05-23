<?php
include_once 'CheckEmailSyntax.php';
include_once 'Salt.php';
require_once 'Connection.php';

echo "<div style=\"font-size:1.5em;padding: 5px;font-family: 'Arial Black';
    display: flex;justify-content: center;align-items: center;height: 100vh;margin: 0 auto\">";

if (!(isset($_POST['pass']) and isset($_POST['email'])))
{
    echo "Fields are empty or incorrect data entered";
    exit();
}
if(($_POST['pass'] == "") or ($_POST['email'] == ""))
{
    echo "Fields are empty or incorrect data entered";
    exit();
}
$pass = $_POST['pass'];
$email = $_POST['email'];
if(CheckEmailSyntax($email))
{
    if($mysqli = connectToDB($host, $userName, $password, $dbName))
    {
        $tableName = "users";
        $dbFieldName = "email";
        if(findFieldInTable($mysqli, $tableName, $dbFieldName, $email))
        {
            $query = "SELECT password FROM $tableName WHERE email = '$email'";
            if ($passField = $mysqli->query($query))
            {
                $DBPass = $passField->fetch_array();
                $query = "SELECT salt FROM $tableName WHERE email = '$email'";

                // Достаём "соль" из БД.
                $salt = $mysqli->query($query)->fetch_array();
                $passwdMD5 = md5($pass.$salt[0]);

                // Проверка на совпадение введенного пароля и записи в БД.
                if ($DBPass[0] == $passwdMD5)
                {
                    $query = "SELECT user_name FROM $tableName WHERE email = '$email'";
                    $nameField = $mysqli->query($query)->fetch_array();

                    session_start();
                    // Обозначение, что мы авторизовались.
                    $_SESSION['auth'] = true;
                    $_SESSION['name'] = $nameField[0];

                    // Проверяем, что была нажата галочка 'Запомнить меня'.
                    if ( !empty($_POST['remember']) and ($_POST['remember'] == 1))
                    {
                        // Сформируем случайную строку для куки (используем функцию generateSalt).
                        $key = generateSalt();
                        $cookieDuration = time()+60*60*24*30;
                        setcookie('login', $_SESSION['name'] , $cookieDuration);
                        // Случайная строка.
                        setcookie('key', $key, $cookieDuration);
                        $query = "UPDATE users SET cookie='$key' WHERE email = '$email'";
                        $mysqli->query($query);
                    }
                    $new_url = 'LogForm.php?session_active=1';
                    header('Location: '.$new_url);
                }
                else
                {
                    echo "Wrong password";
                    session_start();
                    $_SESSION['auth'] = false;
                    session_destroy();

                    // Удаляем куки авторизации путем установления времени их жизни на текущий момент.
                    setcookie('login', '', time()-3600);
                    setcookie('key', '', time()-3600);
                }
            }
        }
    }
    else
        exit();
}
else
{
    echo "Email is INCORRECT";
}
echo '</div>';
$mysqli->close();


function findFieldInTable($mysqli, $tableName, $dbFieldName, $field)
{
    $tableName = $mysqli->real_escape_string($tableName);
    $query = "SELECT * FROM ".$tableName;
    if($tableFields = $mysqli->query($query))
    {
        while($row = $tableFields->fetch_assoc())
            if($field == $row[$dbFieldName])
                return true;
    }
    else
        echo "Cannot get data from table `".$tableName."`";
    return false;
}

