<?php
session_start();
require "db.php";

function erasePanorama()
{
    $db = new Db();
    $conn = $db->getConnection();
    $sql = "delete from panoramaimages
    where username=? and imagenumber=?;";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_SESSION['username'], $_POST['panoramanumber']]);

    $sql = "delete from markers
    where username=? and imagenumber=?;";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_SESSION['username'], $_POST['panoramanumber']]);
}

erasePanorama();
echo "panorama";
