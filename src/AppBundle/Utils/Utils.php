<?php
/**
 * Created by PhpStorm.
 * User: NHS
 * Date: 15/07/15
 * Time: 13:35
 */

function replaceAccents($str)
{
    $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
    $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
    $str = str_replace($a, $b, $str,$count);
    return [$str,$count];
}

/**
 * @param DOMXPath $xpath
 * @param $array_pos
 * @param $start_pos_rpl
 * @param $id_page
 * @param $id_article
 * @param $id_sous_article
 * @param $num_p
 * @return string
 */
function create_xpath_query(DOMXPath $xpath,$array_pos,&$start_pos_rpl,$id_page,$id_article,$id_sous_article,$num_p){
    $length_array_pos = count($array_pos);
    $p_parent_pb_result = $xpath->query("//pb[@xml:id='".$id_page."']/ancestor::p[@n]");
    if ($p_parent_pb_result->length>0){
        $p_parent_pb = $p_parent_pb_result->item(0);
        $num_p_parent_pb = $p_parent_pb->attributes->item(0)->nodeValue;
        if ($num_p == $num_p_parent_pb){
            $id_sous_article_parent_pb = $xpath->query("//pb[@xml:id='".$id_page."']/ancestor::div[@type='adresse' or @type='entree']")->item(0)->attributes->item(1)->nodeValue;
            if ($id_sous_article_parent_pb == $id_sous_article){
                $current_pb = $xpath->query("//pb[@xml:id='".$id_page."']")->item(0);
                $parent_pb = $current_pb;
                $array_pos_pb_prev_node = [];
                while (strtolower($parent_pb->nodeName) != 'p'){
                    $count_node = 1;
                    $prev_sibling = $parent_pb->previousSibling;
                    while($prev_sibling !=null){
                        $count_node++;
                        $prev_sibling=$prev_sibling->previousSibling;
                    }
                    array_push($array_pos_pb_prev_node,$count_node);
                    $parent_pb = $parent_pb->parentNode;
                }
                $length_array_pos_pb_prev_node = count($array_pos_pb_prev_node);
                /**
                 * Verifier si le texte selectionné est un vrai enfant du paragraphe
                 */
                if ($length_array_pos -1 == 2){
                    /**
                     * Si le texte selectionné est le premier enfant du paragraphe,
                     * il faut ajouter une caractère
                     */
                    if ($array_pos[2] == 0) {
                        $start_pos_rpl--;
                    }
                    /**
                     * Vérifie si le suivant frère de pb est un texte ou un élément
                     */
                    $next_node_pb = $current_pb->nextSibling;
                    //Le suivant frère est un texte
                    if ($next_node_pb->nodeType!=1){
                        $array_pos[2] += $array_pos_pb_prev_node[$length_array_pos_pb_prev_node-1];
                    }
                    //Le suivant frère est un élément
                    else{
                        $array_pos[2] += $array_pos_pb_prev_node[$length_array_pos_pb_prev_node-1]-1;
                    }
                }
                elseif ($length_array_pos -1 > 2){
                    $index_parent = $length_array_pos -1;
                    $index_pb_prev_node = $length_array_pos_pb_prev_node-1;
                    while ($index_pb_prev_node>=0){
                        $array_pos[$index_parent] += $array_pos_pb_prev_node[$index_pb_prev_node];
                        $index_pb_prev_node--;
                        $index_parent--;
                    }
                }
            }
        }
    }
    $xpath_query = "//div[@type='articles']/div[@xml:id='".$id_article."']/div[@xml:id='".$id_sous_article."']/p[@n='".$num_p."']";
    $i=$length_array_pos-1;
    while ($i>=2){
        $xpath_query.="/child::node()[position()=".($array_pos[$i]+1)."]";
        $i--;
    }
    return $xpath_query;
}
