<?php
session_start();
require "db.php";

function checkIfMarkerExists()
{
    $db = new Db();
    $conn = $db->getConnection();
    $sql = "select * from markers
    where username=? and imagenumber=? and pitch=? and yaw=?;";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_SESSION['username'], $_POST['markerpanoramanumber'], $_POST['pitch'], $_POST['yaw']]);
    if ($stmt->rowCount() > 0) {
        return true;
    }
    return false;
}

function getCurrentImage()
{
    $db = new Db();
    $conn = $db->getConnection();
    $sql = "select imagedata from markers
    where username=? and imagenumber=? and pitch=? and yaw=?;";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $_SESSION['username'],
        $_POST['markerpanoramanumber'],
        $_POST['pitch'],
        $_POST['yaw']
    ]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['imagedata'];
}

function addMarker()
{
    $db = new Db();
    $conn = $db->getConnection();
    $sql = "insert into markers
    values(?,?,?,?,?,?);";
    $stmt = $conn->prepare($sql);
    $filedata = null;
    if (!empty($_FILES['markerImageInput']['tmp_name'])) {
        $filedata = file_get_contents($_FILES['markerImageInput']['tmp_name']);
    }
    if ($_POST['erase'] == 'yes') {
        $filedata = null;
    }
    $stmt->execute([
        $_SESSION['username'],
        $_POST['markerpanoramanumber'],
        $_POST['pitch'],
        $_POST['yaw'],
        $_POST['markerTextInput'],
        $filedata
    ]);
}
function updateMarker()
{
    $db = new Db();
    $conn = $db->getConnection();
    $sql = "update markers
    set textdata=?,imagedata=?
    where username=? and imagenumber=? and pitch=? and yaw=?;";
    $stmt = $conn->prepare($sql);
    $filedata = getCurrentImage();
    if (!empty($_FILES['markerImageInput']['tmp_name'])) {
        $filedata = file_get_contents($_FILES['markerImageInput']['tmp_name']);
    }
    if ($_POST['erase'] == 'yes') {
        $filedata = null;
    }
    $stmt->execute([
        $_POST['markerTextInput'],
        $filedata,
        $_SESSION['username'],
        $_POST['markerpanoramanumber'],
        $_POST['pitch'],
        $_POST['yaw']
    ]);
}
function eraseMarker()
{
    $db = new Db();
    $conn = $db->getConnection();
    $sql = "delete from markers
    where username=? and imagenumber=? and pitch=? and yaw=?;";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $_SESSION['username'],
        $_POST['markerpanoramanumber'],
        $_POST['pitch'],
        $_POST['yaw']
    ]);
}

function getMarker()
{
    $db = new Db();
    $conn = $db->getConnection();
    $sql = "select pitch,yaw,textdata,imagedata from markers
    where username=? and imagenumber=? and pitch=? and yaw=?;";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $_SESSION['username'],
        $_POST['markerpanoramanumber'],
        $_POST['pitch'],
        $_POST['yaw']
    ]);
    if ($stmt->rowCount() == 1) {
        $arr = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!is_null($arr['imagedata']))
            $arr['imagedata'] = base64_encode($arr['imagedata']);;
        return $arr;
    }
    return null;
}

// $arr = json_decode(file_get_contents("php://input"), true);
// echo json_encode(getMarker($arr['imagenumber'], $arr['pitch'], $arr['yaw']));
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // if (empty($_POST['markerpanoramanumber']) || empty($_POST['pitch']) || empty($_POST['yaw'])) {
    //     exit;
    // }
    if ($_POST['action'] == "Save marker") {
        if (checkIfMarkerExists()) {
            updateMarker();
        } else {
            addMarker();
        }
    } else if ($_POST['action'] == "Erase marker") {
        eraseMarker();
    }
    echo json_encode(getMarker());
}
