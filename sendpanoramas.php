<?php
session_start();
require "db.php";

function getPanoramas()
{
    $db = new Db();
    $conn = $db->getConnection();
    $sql = "select imagenumber,imagedata from panoramaimages
    where username=?
    order by imagenumber desc;";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_SESSION['username']]);
    $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($arr as &$value) {
        $value['imagedata'] =  base64_encode($value['imagedata']);
    }
    return $arr;
}

echo json_encode(getPanoramas());
