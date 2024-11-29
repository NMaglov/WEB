<?php
session_start();
require "db.php";

function getMarkers($imagenumber)
{
    $db = new Db();
    $conn = $db->getConnection();
    $sql = "select pitch,yaw,textdata,imagedata from markers
    where username=? and imagenumber=?;";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_SESSION['username'], $imagenumber]);
    $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($arr as &$value) {
        if (is_null($value['imagedata'])) {
            $value['imagedata'] = "";
        } else {
            $value['imagedata'] =  base64_encode($value['imagedata']);
        }
    }
    return $arr;
}

$arr = json_decode(file_get_contents("php://input"), true);
echo json_encode(getMarkers($arr['imagenumber']));
