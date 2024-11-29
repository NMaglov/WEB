<?php
session_start();
if (!isset($_SESSION['username'])) {
    echo "Not logged in";
    exit;
}
?>
<!DOCTYPE html>
<html>

<head>
    <!-- for optimal display on high DPI devices -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@photo-sphere-viewer/core/index.min.css" />

    <link rel="stylesheet" href="mainpagestyles.css" />
</head>

<body id="body">
    <script src="functions.js"></script>
    <!-- the viewer container must have a defined size -->
    <!-- <div style="width: 70vw; height: 90vh; background-color: red;"></div> -->
    <div id="viewerDiv"></div>

    <!-- <button id="panoramaFile" class="button">Upload Panorama Image</button> -->
    <iframe name="dummyframe" id="dummyframe"> </iframe>

    <form id="panoramaForm" method="POST" action="uploadpanorama.php" target="dummyframe" enctype="multipart/form-data">
        <label id="panoramaFileLabel" for="panoramaFile" class="button">
            <img src="uploadicon.png" style="height:40px;width:40px;" alt="">
            <br>
            Upload Panorama File</label>
        <input type="file" id="panoramaFile" name="panoramaFile" />
    </form>

    <form id="eraseForm" method="POST" action="erasepanorama.php" target="dummyframe" enctype="multipart/form-data">
        <input type="text" id="panoramanumber" name="panoramanumber" style="display:none">
        <label id="eraseFileLabel" for="eraseFile" class="button">
            Erase Selected Panorama</label>
        <input type="submit" id="eraseFile" name="eraseFile" />
    </form>

    <form id="logoutForm" method="POST" action="logout.php">
        <!-- <label id="logoutLabel" for="logout"></label> -->
        <input type="submit" id="logout" name="logout" value="Log out">
    </form>

    <div id="markerView">
        <form id="markerForm" action="markeractions.php" method="POST" target="dummyframe" enctype="multipart/form-data">
            <input type="text" id="markerpanoramanumber" name="markerpanoramanumber" style="display:none">
            <input type="text" id="pitch" name="pitch" style="display:none">
            <input type="text" id="yaw" name="yaw" style="display:none">
            <input type="text" id="erase" name="erase" style="display:none" value="no">

            <textarea id="markerTextInput" name="markerTextInput"></textarea>
            <div id="markerImageDiv">
                <img id="markerImage" src="" alt="No image selected" />
            </div>
            <label id="markerImageLabel" for="markerImageInput" class="button">
                <img src="uploadicon.png" style="height:18px;width:18px;" alt="">
                Upload marker image</label>
            <input id="markerImageInput" name="markerImageInput" type="file" />

            <label id="eraseMarkerImageLabel" for="eraseMarkerImage" class="button"> Erase marker image</label>
            <input id="eraseMarkerImage" name="eraseMarkerImage" type="checkbox">

            <label id="submitMarkerLabel" for="submitMarker" class="button">Save marker</label>
            <input id="submitMarker" type="submit" name="action" value="Save marker" />
            <label id="eraseMarkerLabel" for="eraseMarker" class="button">Erase marker</label>
            <input id="eraseMarker" type="submit" name="action" value="Erase marker" />
        </form>
    </div>

    <ul id="gallery">
    </ul>

    <script type="importmap">
        {
        "imports": {
          "three": "https://cdn.jsdelivr.net/npm/three/build/three.module.js",
          "@photo-sphere-viewer/core": "https://cdn.jsdelivr.net/npm/@photo-sphere-viewer/core/index.module.js"
        }
      }
    </script>

    <script type="module">
        import {
            Viewer
        } from "@photo-sphere-viewer/core";

        const viewer = new Viewer({
            container: document.getElementById("viewerDiv"),
            panorama: "",
        });
        setViewer(viewer);
        let panoramas = [];
        getPanoramas(panoramas);

        let id = 100;
        let panoramaUrl = [];
        let selectedMarker = null;
        viewer.addEventListener("click", (event) => {
            id++;
            addMarker(id, event.data.pitch, event.data.yaw);
            visualize();
        });
        viewer.addEventListener("render", () => {
            visualize();
        });



        document
            .getElementById("panoramaFile")
            .addEventListener("change", function(event) {
                const file = document.getElementById("panoramaFile").files[0];
                loadFilePanorama(file);
                //markers = [];
                eraseMarkers();
                clearMarkers();
                visualize();

                document.getElementById("panoramaForm").submit();
            });

        document
            .getElementById("markerImageInput")
            .addEventListener("change", function(event) {
                document.getElementById("erase").value = "no";
                var reader = new FileReader();
                reader.readAsDataURL(document
                    .getElementById("markerImageInput").files[0]);
                reader.onload = function(e) {
                    var image = new Image();
                    image.src = e.target.result;
                    image.onload = function() {
                        document.getElementById("markerImage").src = image.src;
                    };
                }
            });

        document
            .getElementById("eraseMarkerImage")
            .addEventListener("click", function(event) {
                document.getElementById("erase").value = "yes";
                document.getElementById("markerImage").src = "";
            });

        document.getElementById("dummyframe").addEventListener("load", async function() {
            const response = document.getElementById('dummyframe').contentWindow.document.body.innerHTML;
            if (response == "panorama") {
                getPanoramas(panoramas);
            } else {
                // alert(response);
                updateSelectedMarker(JSON.parse(response));
                // eraseMarkers();
                // await getMarkers();
            }
            visualize();
        });

        document.getElementById("eraseMarker").addEventListener("click", function() {
            // deleteSelectedMarker();
            document.getElementById("markerTextInput").value = "";
            document.getElementById("markerImage").src = "";
            document.getElementById("markerImageInput").value = null;
        });

        document.getElementById("eraseForm").addEventListener("submit", function() {
            eraseMarkers();
            clearMarkers();
            viewer.setPanorama("");
        });
    </script>
</body>

</html>