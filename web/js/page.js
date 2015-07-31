/**
 * Created by mrson.
 */
var fontSize = 1;
function zoomIn() {
    fontSize += 0.1;
    document.getElementById("doc_transform").style.fontSize = fontSize + "em";
}
function zoomOut() {
    fontSize -= 0.1;
    if (fontSize < 0.5)
        fontSize = 0.5;
    document.getElementById("doc_transform").style.fontSize = fontSize + "em";
}
document.onkeyup = checkKey;
function checkKey(e) {

    e = e || window.event;

    if (e.keyCode == '37') {
        // left arrow
        $(".previous_page button").click();
    }
    else if (e.keyCode == '39') {
        // right arrow
        $(".next_page button").click();
    }

}
// assign and set the zoom percent
wheelzoom(document.querySelector('#doc_original img'),{zoom:0.05});
var zoomImg =0.10;
function zoomImgIn(img){
    var img_elem = document.querySelector(img);
    var backgroundSize = window.getComputedStyle(img_elem,null).backgroundSize.trim().split(/\s+/),
        sizes = {
            x : parseFloat(backgroundSize[0]),
            y : parseFloat(backgroundSize[1])
        };
    sizes.x += sizes.x*zoomImg;
    sizes.y += sizes.y*zoomImg;
    var att_width = document.createAttribute("width_img");
    att_width.value = sizes.x;
    var att_height = document.createAttribute("height_img");
    att_height.value = sizes.y;
    img_elem.setAttributeNode(att_width);
    img_elem.setAttributeNode(att_height);
    img_elem.dispatchEvent(new CustomEvent('clickzoom'));
}
function zoomImgOut(img){
    var img_elem = document.querySelector(img);
    var backgroundSize = window.getComputedStyle(img_elem,null).backgroundSize.trim().split(/\s+/),
        sizes = {
            x : parseFloat(backgroundSize[0]),
            y : parseFloat(backgroundSize[1])
        };
    sizes.x -= sizes.x*zoomImg;
    sizes.y -= sizes.y*zoomImg;
    var att_width = document.createAttribute("width_img");
    att_width.value = sizes.x;
    var att_height = document.createAttribute("height_img");
    att_height.value = sizes.y;
    img_elem.setAttributeNode(att_width);
    img_elem.setAttributeNode(att_height);
    img_elem.dispatchEvent(new CustomEvent('clickzoom'));
}