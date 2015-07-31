/**
 * Created by mrson.
 */
var fontSize = 1;
function zoomIn() {
    fontSize += 0.1;
    document.getElementById("content").style.fontSize = fontSize + "em";
}
function zoomOut() {
    fontSize -= 0.1;
    if (fontSize < 0.5)
        fontSize = 0.5;
    document.getElementById("content").style.fontSize = fontSize + "em";
}

document.onkeyup = checkKey;
function checkKey(e) {

    e = e || window.event;

    if (e.keyCode == '37') {
        // left arrow
        $(".previous_article button").click();
    }
    else if (e.keyCode == '39') {
        // right arrow
        $(".next_article button").click();
    }

}