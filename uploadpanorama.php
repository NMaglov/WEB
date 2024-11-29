<?php
session_start();
require "db.php";

function nextNumber()
{
    $db = new Db();
    $conn = $db->getConnection();
    $sql = "select max(imagenumber) as maximum from panoramaimages
    where username=?;";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_SESSION['username']]);
    $number = $stmt->fetch(PDO::FETCH_ASSOC)['maximum'];
    if (is_null($number)) {
        return 0;
    }
    return $number + 1;
}
function addPanorama()
{
    $db = new Db();
    $conn = $db->getConnection();
    $sql = "insert into panoramaimages
    values(?,?,?);";
    $stmt = $conn->prepare($sql);
    // $stmt->execute(["georgi", $_FILES['ff']['name'], file_get_contents($_FILES['ff']['tmp_name'])]);
    $stmt->execute([$_SESSION['username'], nextNumber(), file_get_contents($_FILES['panoramaFile']['tmp_name'])]);
}


addPanorama();
echo "panorama";