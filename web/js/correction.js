/**
 * Created by mrson.
 */


function correctTect(array_container, array_p_div){
    var correct_text= window.prompt("Entrez le texte corrigé!", array_container[0][0]);
    if (correct_text!=null){
        $.post(window.location,{array_in_p: JSON.stringify(array_container), array_p_div: JSON.stringify(array_p_div), correct_text : correct_text, typeCmd: "correct"},"UTF-8").done(function(data){
            if (data != "Succès!"){
                alert(data);
            }
            window.location.reload();
        }).fail(function(data){
            alert("Votre commande ne peut pas être exécutée!"+data);
            window.location.reload();
        });
    }
}

function addTag(array_container, array_p_div, type, subType){
    if (type=="article_vise"){
        var link= window.prompt("Entrez le lien de l'article visé!", "enccre.academie-sciences.fr/intranet_developpement/app_enccre/web/article/2/3");
        if (link!=null){
            $.post(window.location,{array_in_p: JSON.stringify(array_container), array_p_div: JSON.stringify(array_p_div), type: type, subType:subType, link: link, typeCmd: "add"},"UTF-8").done(function(data){
                if (data != "Succès!"){
                    alert(data);
                }
                window.location.reload();
            }).fail(function(data){
                alert("Votre commande ne peut pas être exécutée!"+data);
                window.location.reload();
            });
        }
    }
    else{
    $.post(window.location,{array_in_p: JSON.stringify(array_container), array_p_div: JSON.stringify(array_p_div), type: type, subType:subType, typeCmd: "add"},"UTF-8").done(function(data){
        if (data != "Succès!"){
            alert(data);
        }
        window.location.reload();
        }).fail(function(data){
        alert("Votre commande ne peut pas être exécutée!"+data);
        window.location.reload();
        });
    }
}

function removeTag(array_container, array_p_div, type, subType){
    $.post(window.location,{array_in_p: JSON.stringify(array_container), array_p_div: JSON.stringify(array_p_div),  type: type, subType:subType, typeCmd: "remove"},"UTF-8").done(function(data){
        if (data != "Succès!") {
            alert(data);
        }
        window.location.reload();
    }).fail(function(data){
        alert("Votre commande ne peut pas être exécutée!"+data);
        window.location.reload();
    });
}

function getPositions(parent, array_pos, array_class_name){
    array_class_name = array_class_name || null;
    var prev_sibling = parent.previousSibling;
    while (!(parent.nodeName.toLowerCase()=="p" && parent.hasAttribute("data-n"))){
        var i=0;
        prev_sibling = parent.previousSibling;
        if (array_class_name!=null){
            if (parent.attributes){
                var attributes = parent.getAttribute("class");
                if (attributes !=null){
                    attributes = attributes.split(" ");
                    for (var j=0; j<attributes.length;j++){
                        array_class_name.push(attributes[j]);
                    }
                }
            }
        }
        while(prev_sibling != null ){
            i++;
            prev_sibling = prev_sibling.previousSibling;
        }
        if (parent.nodeName.toLowerCase()=="tr"){
            i++;
        }
        array_pos.push(i);
        parent = parent.parentNode;
        var parent_node_name = parent.nodeName.toLowerCase();
        if (parent_node_name == 'ul'){
            array_pos.push(1);
            parent = parent.previousSibling;
        }
        if (parent_node_name == 'tbody'){
            array_pos.push(1);
            parent = parent.parentNode.previousSibling;
        }
    }
    return parent;
}