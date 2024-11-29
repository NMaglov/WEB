<?php
session_start();
require "db.php";

function checkIfUserExists($username)
{
    $db = new Db();
    $conn = $db->getConnection();
    $sql = "select * from users
    where username=?;";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$username]);
    if ($stmt->rowCount() > 0) {
        return true;
    }
    return false;
}
function addUser($username, $userpassword)
{
    $db = new Db();
    $conn = $db->getConnection();
    $sql = "insert into users
    values(?,?);";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$username, $userpassword]);
    if ($stmt->rowCount() > 0) {
        return true;
    }
    return false;
}
function checkIfPassowordIsCorrect($username, $userpassword)
{
    $db = new Db();
    $conn = $db->getConnection();
    $sql = "select userpassword from users
    where username=?;";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$username]);
    if ($stmt->rowCount() == 1 && password_verify($userpassword, $stmt->fetch(PDO::FETCH_ASSOC)['userpassword'])) {
        return true;
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (empty($_POST['user']) || empty($_POST['pass'])) {
        echo "Username and password cannot be empty";
        exit;
    }
    if ($_POST['action'] == "Register") {
        if (checkIfUserExists($_POST['user'])) {
            echo "User with this username already exist";
        } else {
            addUser($_POST['user'], password_hash($_POST['pass'], PASSWORD_DEFAULT));
            echo "Successful registration";
        }
    } else if ($_POST['action'] == "Login") {
        if (checkIfPassowordIsCorrect($_POST['user'], $_POST['pass'])) {
            $_SESSION['username'] = $_POST['user'];
            echo "Ok";
        } else {
            echo "Incorrect username or password";
        }
    }
}
