function base64ToBlob(base64String, contentType = "") {
  const byteCharacters = atob(base64String);
  const byteArrays = [];

  for (let i = 0; i < byteCharacters.length; i++) {
    byteArrays.push(byteCharacters.charCodeAt(i));
  }

  const byteArray = new Uint8Array(byteArrays);
  return new Blob([byteArray], {
    type: contentType,
  });
}
var MARKERS = [];
var SELECTEDMARKER = [];
var SELECTEDPANORAMA = [];
var VIEWER = [];
function setViewer(viewer) {
  VIEWER = viewer;
}
function clearMarkers() {
  MARKERS = [];
}
function eraseMarkers() {
  for (let i = 0; i < MARKERS.length; i++) {
    try {
      document.getElementById(MARKERS[i].id).remove();
    } catch (e) { }
  }
}
async function getMarkers() {
  eraseMarkers();
  MARKERS = [];
  const request = new Request("sendmarkers.php", {
    method: "POST",
    body: JSON.stringify({ imagenumber: SELECTEDPANORAMA["imagenumber"] }),
  });

  const response = await fetch(request);
  const json = await response.json();
  for (let i = 0; i < json.length; i++) {
    json[i].id = i;
    json[i].imageurl = "";
  }
  MARKERS = json;
  createMarkers();
  visualize(MARKERS, VIEWER);
}

async function getPanoramas(panoramas) {
  for (let i = 0; i < panoramas.length; i++) {
    URL.revokeObjectURL(panoramas[i]["imageurl"]);
  }
  document.getElementById("gallery").innerHTML = "";
  panoramas = [];
  const response = await fetch("sendpanoramas.php");
  const json = await response.json();
  for (let i = 0; i < json.length; i++) {
    panoramas.push({
      imagenumber: json[i]["imagenumber"],
      imageurl: URL.createObjectURL(base64ToBlob(json[i]["imagedata"])),
    });
    var parent = document.getElementById("gallery");
    var child = document.createElement("a");
    child.href = "#";
    child.addEventListener("click", async () => {
      eraseMarkers();
      MARKERS = [];
      SELECTEDPANORAMA = panoramas[i];
      await getMarkers();
      VIEWER.setPanorama(panoramas[i]["imageurl"]);
      document.getElementById("panoramanumber").value =
        SELECTEDPANORAMA["imagenumber"];

      document.getElementById("markerpanoramanumber").value =
        SELECTEDPANORAMA["imagenumber"];

      document.getElementById("markerImage").src = "";
      document.getElementById("markerTextInput").value = "";
      document.getElementById("pitch").value = "";
    });
    parent.appendChild(child);
    parent = child;
    child = document.createElement("img");
    child.setAttribute("class", "galleryImage");
    child.setAttribute("src", panoramas[i]["imageurl"]);
    parent.appendChild(child);
  }
}
function loadMarkerData(marker) {
  document.getElementById("pitch").value = marker.pitch;
  document.getElementById("yaw").value = marker.yaw;
  document.getElementById("markerTextInput").value = marker.textdata;
  // const url = URL.createObjectURL(base64ToBlob(marker.imagedata));
  // document.getElementById("markerImage").src = url;
  document.getElementById("markerImage").src = marker.imageurl;
  document.getElementById("markerImageInput").value = null;
}
function retreiveMarkerData(marker) {
  loadMarkerData(marker);
}







function createMarkerHTML(marker) {
  const parent = document.getElementById("viewerDiv");
  const child = document.createElement("a");
  parent.appendChild(child);

  child.setAttribute("id", marker.id.toString());
  child.setAttribute("class", "dot");
  child.setAttribute("href", "#");

  child.addEventListener("click", function () {
    SELECTEDMARKER = marker;
    retreiveMarkerData(marker);
  });

  const size = window.innerHeight * 0.05;
  child.style.height = size.toString() + "px";
  child.style.width = size.toString() + "px";
  child.style.display = "none";
  // child.style.backgroundColor = "transparent";
  // child.style.backgroundImage = document.getElementById("markerImage").src;
  if (marker.imagedata == "") {
    return;
  }
  const grandchild = document.createElement("img");
  child.appendChild(grandchild);
  const url = URL.createObjectURL(base64ToBlob(marker.imagedata));
  marker.imageurl = url;

  grandchild.setAttribute("src", url);
  grandchild.style.borderRadius = "100%";
  grandchild.style.height = "100%";
  grandchild.style.width = "100%";
  grandchild.style.display = "inline";
}
function createMarkers() {
  for (let i = 0; i < MARKERS.length; i++) {
    createMarkerHTML(MARKERS[i]);
  }
}
function deleteMarkerHTML(marker) {
  try {
    document.getElementById(marker.id).remove();
  } catch (e) { }
}

function deleteSelectedMarker() {
  if (SELECTEDMARKER == []) {
    return;
  }
  deleteMarkerHTML(SELECTEDMARKER);
  SELECTEDMARKER = [];
}

function updateSelectedMarker(newMarker) {
  // alert(newMarker);
  for (let i = 0; i < MARKERS.length; i++) {
    if (MARKERS[i].pitch == SELECTEDMARKER.pitch && MARKERS[i].yaw == SELECTEDMARKER.yaw) {
      deleteMarkerHTML(MARKERS[i]);
      if (newMarker != null) {
        MARKERS[i].textdata = newMarker.textdata;
        if (newMarker.imagedata != null)
          MARKERS[i].imagedata = newMarker.imagedata;
        else MARKERS[i].imagedata = "";
        createMarkerHTML(MARKERS[i]);
        SELECTEDMARKER = MARKERS[i];
      } else {
        MARKERS.splice(i, 1);
        SELECTEDMARKER = [];
      }

      break;
    }
  }
}





function addMarker(id, pitch, yaw) {
  MARKERS.push({
    id: id,
    pitch: pitch,
    yaw: yaw,
    textdata: "",
    imagedata: "",
    imageurl: ""
  });
  createMarkerHTML(MARKERS[MARKERS.length - 1]);
  visualize();
  document.getElementById(id).click();
  retreiveMarkerData(MARKERS[MARKERS.length - 1]);
}
function visualize() {
  for (let i = 0; i < MARKERS.length; i++) {
    const child = document.getElementById(MARKERS[i].id);
    if (VIEWER.dataHelper.isPointVisible(MARKERS[i])) {
      const coords = VIEWER.dataHelper.sphericalCoordsToViewerCoords(
        MARKERS[i]
      );
      const size = window.innerHeight * 0.05;
      if (
        coords.x - size / 2 <= 0 ||
        coords.x + size * 0.75 >= VIEWER.getSize().width ||
        coords.y - size / 2 <= 0 ||
        coords.y + size * 0.75 >= VIEWER.getSize().height
      ) {
        child.style.display = "none";
        continue;
      }
      child.style.display = "block";
      child.style.position = "absolute";
      child.style.left = (coords.x - size / 2).toString() + "px";
      child.style.top = (coords.y - size / 2).toString() + "px";
    }
    else {
      child.style.display = "none";
    }
  }
}
function loadFilePanorama(panorama) {
  for (let i = 0; i < MARKERS.length; i++) {
    document
      .getElementById(MARKERS[i].id)
      .parentElement.removeChild(document.getElementById(MARKERS[i].id));
  }

  VIEWER.setPanorama(panorama["imageurl"]);
}
