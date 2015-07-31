/**
 * Created by mrson.
 */
function changeHref(href){
    window.location = href;
}

function copyText(text) {
    var isSafari = Object.prototype.toString.call(window.HTMLElement).indexOf('Constructor') > 0;
    if (isSafari){
        prompt("Votre navigateur ne supporte pas cette commande. \nUtilisez Ctrl+C ou Cmd+C pour copier ce texte!",text);
    }
    else{
        try {
            // Now that we've selected the anchor text, execute the copy command
            document.execCommand('copy');
        } catch(err) {
            prompt("Votre navigateur ne supporte pas cette commande. \nUtilisez Ctrl+C ou Cmd+C pour copier ce texte!",text);
        }
    }
}

$(window).load(function(){
    $('#button_couleur').click(function(){
        $(".tableau_couleur").toggle();
    });
});

