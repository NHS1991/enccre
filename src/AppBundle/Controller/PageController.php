<?php

namespace AppBundle\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
define('__ROOT__', dirname(dirname(__FILE__)));
require_once(__ROOT__."/Utils/Utils.php");

/**
 * Class PageController
 * @package AppBundle\Controller
 * @Route("/page")
 */
class PageController extends Controller{
    /**
     * Afficher la page ($page) du volume ($volume)
     * @Route("/correction/{volume}/{page}", requirements={"page" = "\d+", "volume" = "\d+"})
     * @param $volume
     * @param $page
     * @param Request $request
     * @return Response
     */
    public function showPageActionTest($volume, $page, Request $request){
        $id_page = 'v'.$volume.'-p'.$page;
        /**
         * Charger le fichier correspondant pour récupérer les infos de la page $page du volume $volume par DOMDocument
         */
        $xml_source = new \DOMDocument();
        $xml_source->load('../src/AppBundle/Resources/xml/Correction/volume'.$volume.'.xml');
        $xpath = new \DOMXpath($xml_source);
        /**
         * Traiter la méthode GET
         */
        if ($request->isMethod('GET')){
            $id_next_page = 'v'.$volume.'-p'.strval(intval($page)+1);
            $id_previous_page = 'v'.$volume.'-p'.strval(intval($page)-1);
            $next_page_href = strval(intval($page)+1);
            $previous_page_href = strval(intval($page)-1);
            $xslt_root = new \DOMDocument();
            $xslt_root->load('../src/AppBundle/Resources/xslt/page_test.xsl');
            $transform = new \XSLTProcessor();
            $transform->importStylesheet($xslt_root);
            $data = file_get_contents('../src/AppBundle/Resources/xml/Correction/volume'.$volume.'.xml');
            preg_match("~<pb(?P<atts>.*? xml:id=\"".$id_page."\".*?)/>~", $data, $find_pb, PREG_OFFSET_CAPTURE);
            if ($find_pb) {
                $prefix_id = "";
                $array_page_content = $this->getPageContent($data, $find_pb, $id_page, $xpath, $id_next_page, $id_previous_page, $previous_page_href, $next_page_href, $prefix_id);
                $content = $array_page_content[0];
                $previous_page_href = $array_page_content[1];
                $next_page_href = $array_page_content[2];
                $xml_transform = new \DOMDocument();
                $xml_transform->loadXML($content);
                $page_content = $transform->transformToDoc($xml_transform)->saveHTML();
                return $this->render('AppBundle::page_test.html.twig', array(
                    'page_content' => $page_content,
                    'next_page' => $next_page_href,
                    'previous_page' => $previous_page_href,
                    'volume' => $volume));
            }
        }
        /**
         * Traiter la méthode POST
         */
        elseif ($request->isMethod('POST')){
            $type_cmd_get = $request->get("typeCmd");
            $type = $request->get("type");
            if ($type == 'italic' || $type == 'smallcaps') {
                $element_type = 'emph';
                $attribute_name = 'rend';
            } else {
                $element_type = 'seg';
                $attribute_name = 'type';
            }
            $sub_type = $request->get("subType");
            $array_in_p = json_decode($request->get("array_in_p"));
            $array_p_div = json_decode($request->get("array_p_div"));
            $id_article = 'v'.$volume.'-'.substr($array_p_div[1],0,strpos($array_p_div[1],"-"));
            $id_sous_article = 'v'.$volume.'-'.$array_p_div[1];
            $num_p = $array_p_div[0];
            /**
             * Tous les enfants sont dans le même parent de type d'élément (seg ou emph)
             */
            if (count($array_in_p)==1){
                $selected_content = $array_in_p[0][0];
                $array_pos = $array_in_p[0][1];
                $start_pos_rpl = $array_pos[0];
                $len_rpl = $array_pos[1];
                $xpath_query = create_xpath_query($xpath,$array_pos,$start_pos_rpl,$id_page,$id_article,$id_sous_article,$num_p);
                $result = $xpath->query($xpath_query);
                if ($result->length>0){
                    $rpl_node = $result->item(0);
                    $rpl_node_type = $rpl_node->nodeType;
                    $str_before = "";
                    $str_after = "";
                    if ($rpl_node_type == 3){
                        $str_rpl_node_val = $rpl_node->textContent;
                        $str_rpl_val = mb_substr($str_rpl_node_val, $start_pos_rpl, $len_rpl, 'UTF-8');
                        if ($str_rpl_val != $selected_content){
                            return new Response('Erreur' . $xpath_query);
                        }
                        else{
                            $str_before = mb_substr($str_rpl_node_val, 0, $start_pos_rpl, 'UTF-8');
                            $str_after = mb_substr($str_rpl_node_val, $start_pos_rpl + $len_rpl, null, 'UTF-8');
                            $selected_node = $xml_source->createTextNode($selected_content);
                        }
                    }
                    else{
                        $selected_node = $rpl_node->cloneNode(true);
                    }
//                    echo $selected_content.'---'.$str_rpl_val;
                    /**
                     * Pour l'action d'ajoute d'une catégorie
                     */
                    if ($type_cmd_get == "add") {
                        if ($type == 'article_vise') {
                            $link_article_vise = $request->get("link");
                            $id_article_vise = "#inconnu";
                            $pos_last_slash = strrpos($link_article_vise, "/");
                            $volume_article_vise = intval(substr(substr($link_article_vise, 0, $pos_last_slash), strrpos(substr($link_article_vise, 0, $pos_last_slash), "/") + 1));
                            $num_article_vise = intval(substr($link_article_vise, $pos_last_slash + 1));
                            if (is_int($volume_article_vise) && is_int($num_article_vise)) {
                                $id_article_vise = "#v" . $volume_article_vise . "-" . $num_article_vise;
                            }
                            $target_element = $xml_source->createElement("ref");
                            $attribute_target = $xml_source->createAttribute("target");
                            $attribute_target->value = $id_article_vise;
                            $target_element->appendChild($attribute_target);
                            $target_element->appendChild($selected_node);
                            $new_element = $xml_source->createElement($element_type);
                            $attribute = $xml_source->createAttribute($attribute_name);
                            $attribute->value = $type;
                            $new_element->appendChild($attribute);
                            $new_element->appendChild($target_element);
                        } else {
                            $new_element = $xml_source->createElement($element_type);
                            $attribute = $xml_source->createAttribute($attribute_name);
                            $attribute->value = $type;
                            $new_element->appendChild($attribute);
                            $new_element->appendChild($selected_node);
                            if ($sub_type != null) {
                                $attribute = $xml_source->createAttribute("subtype");
                                $attribute->value = $sub_type;
                                $new_element->appendChild($attribute);
                            }
                        }
                        $parent_node = $rpl_node->parentNode;
                        if ($str_before != "") {
                            $new_text_node_before = $xml_source->createTextNode($str_before);
                            $parent_node->insertBefore($new_text_node_before, $rpl_node);
                        }
                        $parent_node->insertBefore($new_element, $rpl_node);
                        if ($str_after != "") {
                            $new_text_node_after = $xml_source->createTextNode($str_after);
                            $parent_node->insertBefore($new_text_node_after, $rpl_node);
                        }
                        $parent_node->removeChild($rpl_node);
                        $xml_source->save("../src/AppBundle/Resources/xml/Correction/volume" . $volume . ".xml");
                        return new Response("Succès!");
                    }
                    /**
                     * Pour l'action d'enlèvement d'une catégorie
                     */
                    elseif($type_cmd_get == "remove"){
                        if ($rpl_node_type == 3){
                            $parent_node = $rpl_node->parentNode;
                        }
                        elseif ($rpl_node_type == 1){
                            $parent_node = $rpl_node;
                        }
                        else{
                            return new Response('Erreur' . $xpath_query);
                        }
                        $found_remove_node = false;
                        $remove_node = $parent_node;
                        while ($remove_node->nodeName!="p" && !$found_remove_node){
                            $parent_remove_att = $remove_node->attributes->getNamedItem($attribute_name);
                            if ( $remove_node->nodeName == $element_type && $parent_remove_att!=null) {
                                if ($parent_remove_att->nodeValue == $type) {
                                    $found_remove_node = true;
                                    break;
                                }
                            }
                            $remove_node = $remove_node->parentNode;
                        }
                        if ($found_remove_node){
                            if ($remove_node->isSameNode($rpl_node)){
                                $children_remove_node = $remove_node->childNodes;
                                for($i=0;$i<$children_remove_node->length;$i++){
                                    $remove_node->parentNode->insertBefore($children_remove_node->item($i)->cloneNode(true),$remove_node);
                                }
                                $remove_node->parentNode->removeChild($remove_node);
                            }
                            else{
                                if ($parent_node->isSameNode($rpl_node)){
                                    $parent_node = $parent_node->parentNode;
                                }
                                $test_same_remove_node = false;
                                /**
                                 * Grouper les frères précédents et suivants par la balise correspondante
                                 * au parent du texte selectionné (les descendants aussi).
                                 * Remplacer la valeur du parent du texte sélectionné par ces éléments
                                 */
                                $selected_node_to_remove = $xml_source->createDocumentFragment();
                                $selected_node_to_remove->appendChild($selected_node);
                                if ($str_before != "") {
                                    $text_node_before = $xml_source->createTextNode($str_before);
                                    $parent_node->insertBefore($text_node_before,$rpl_node);
                                }
                                $selected_node_to_remove = $parent_node->insertBefore($selected_node_to_remove,$rpl_node);
                                if ($str_after != "") {
                                    $text_node_after = $xml_source->createTextNode($str_after);
                                    $parent_node->insertBefore($text_node_after,$rpl_node);
                                }
                                $parent_node->removeChild($rpl_node);
                                $elem_before_selected_node = null;
                                $elem_after_selected_node=null;
                                while (!$test_same_remove_node){
                                    $parent_node_name = $parent_node->nodeName;
                                    $parent_node_attributes = $parent_node->attributes;
                                    /**
                                     * Grouper les frères précédents de l'ancêtre du texte sélectionné (ou son ancetre)
                                     */
                                    $elem_before_selected_node = $xml_source->createElement($parent_node_name);
                                    for ($i=0;$i<$parent_node_attributes->length;$i++){
                                        $att = $xml_source->createAttribute($parent_node_attributes->item($i)->nodeName);
                                        $att->value = $parent_node_attributes->item($i)->nodeValue;
                                        $elem_before_selected_node->appendChild($att);
                                    }
                                    $prev_sibling_selected_node = $selected_node_to_remove->previousSibling;
                                    while ($prev_sibling_selected_node != null){
                                        $elem_before_selected_node->insertBefore($prev_sibling_selected_node->cloneNode(true),$elem_before_selected_node->firstChild);
                                        $prev_sibling_selected_node = $prev_sibling_selected_node->previousSibling;
                                    }
                                    /**
                                     * Grouper les frères suivants de l'ancêtre du texte sélectionné (ou son ancetre)
                                     */
                                    $elem_after_selected_node = $xml_source->createElement($parent_node_name);
                                    for ($i=0;$i<$parent_node_attributes->length;$i++){
                                        $att = $xml_source->createAttribute($parent_node_attributes->item($i)->nodeName);
                                        $att->value = $parent_node_attributes->item($i)->nodeValue;
                                        $elem_after_selected_node->appendChild($att);
                                    }
                                    $next_sibling_selected_node = $selected_node_to_remove->nextSibling;
                                    while ($next_sibling_selected_node != null){
                                        $elem_after_selected_node->appendChild($next_sibling_selected_node->cloneNode(true));
                                        $next_sibling_selected_node = $next_sibling_selected_node->nextSibling;
                                    }
                                    /**
                                     * Créer la balise contenant le texte sélectionnné
                                     */
                                    if (!$parent_node->isSameNode($remove_node)){
                                        if ($parent_node_name != "ref" || $type!="article_vise"){
                                            $new_element_selected = $xml_source->createElement($parent_node_name);
                                            for ($i=0;$i<$parent_node_attributes->length;$i++){
                                                $att = $xml_source->createAttribute($parent_node_attributes->item($i)->nodeName);
                                                $att->value = $parent_node_attributes->item($i)->nodeValue;
                                                $new_element_selected->appendChild($att);
                                            }
                                            $new_element_selected->appendChild($selected_node_to_remove);
                                            $selected_node_to_remove = $new_element_selected;
                                        }
                                    }
                                    else{
                                        $test_same_remove_node = true;
                                    }
                                    /**
                                     * Remplacer le noeud correspondant au parent actuel par ses enfants créés
                                     */
                                    $grand_parent_node = $parent_node->parentNode;
                                    if ($elem_before_selected_node->textContent !=""){
                                        $grand_parent_node->insertBefore($elem_before_selected_node,$parent_node);
                                    }
                                    $grand_parent_node->insertBefore($selected_node_to_remove,$parent_node);
                                    if ($elem_after_selected_node->textContent !=""){
                                        $grand_parent_node->insertBefore($elem_after_selected_node,$parent_node);
                                    }
                                    $grand_parent_node->removeChild($parent_node);
                                    $parent_node= $selected_node_to_remove->parentNode;
                                }
                            }
                            if ($xml_source->save("../src/AppBundle/Resources/xml/Correction/volume" . $volume . ".xml"))
                                return new Response("Succès!");
                            else
                                return new Response("La modification n'est pas encore enregistrée!");
                        }
                        else{
                            return new Response('Erreur' . $xpath_query);
                        }
                    }
                    /**
                     * Pour l'action de correction du texte sélectionné
                     */
                    elseif($type_cmd_get == "correct"){
                        $correct_text = $request->get("correct_text");
                        $parent_node = $rpl_node->parentNode;
                        if ($str_before != "") {
                            $new_text_node_before = $xml_source->createTextNode($str_before);
                            $parent_node->insertBefore($new_text_node_before, $rpl_node);
                        }
                        $text_node_to_replace = $xml_source->createTextNode($correct_text);
                        $parent_node->insertBefore($text_node_to_replace, $rpl_node);
                        if ($str_after != "") {
                            $new_text_node_after = $xml_source->createTextNode($str_after);
                            $parent_node->insertBefore($new_text_node_after, $rpl_node);
                        }
                        $parent_node->removeChild($rpl_node);
                        if ($xml_source->save("../src/AppBundle/Resources/xml/Correction/volume" . $volume . ".xml"))
                            return new Response("Succès!");
                        else
                            return new Response("La modification n'est pas encore enregistrée!");
                    }
                }
                else{
                    return new Response('Query-Erreur'.$xpath_query);
                }
            }
            /**
             * Tous les enfants sont dans le même parent de type d'élément (p)
             */
            elseif (count($array_in_p)==2){
                $start_container_text = $array_in_p[0][0];
                $array_pos_start_container = $array_in_p[0][1];
                $start_pos_start_container = $array_pos_start_container[0];
                $end_container_text = $array_in_p[1][0];
                $array_pos_end_container = $array_in_p[1][1];
                $length_end_container = $array_pos_end_container[1];
                /**
                 * Rechercher le premier élément du texte sélectionné
                 */
                $xpath_query_start_container = create_xpath_query($xpath,$array_pos_start_container,$start_pos_start_container,$id_page,$id_article,$id_sous_article,$num_p);
                $result_start_container = $xpath->query($xpath_query_start_container);
                /**
                 * Si le premier élément existe
                 */
                if ($result_start_container->length>0){
                    $start_container_node = $result_start_container->item(0);
                    $start_container_node_type = $start_container_node->nodeType;
                    $str_before = "";
                    if ($start_container_node_type == 3){
                        $str_start_container_node = $start_container_node->textContent;
                        $selected_str_start_container_node = mb_substr($str_start_container_node, $start_pos_start_container, null, 'UTF-8');
                        if ($selected_str_start_container_node != $start_container_text){
                            return new Response('Erreur' . $xpath_query_start_container);
                        }
                        else{
                            $str_before = mb_substr($str_start_container_node, 0, $start_pos_start_container, 'UTF-8');
                            $selected_start_container_node = $xml_source->createTextNode($selected_str_start_container_node);
                        }
                    }
                    else{
                        $selected_start_container_node = $start_container_node->cloneNode(true);
                    }
                    /**
                     * Rechercher le dernier élément du texte sélectionné
                     */
                    $xpath_query_end_container = create_xpath_query($xpath,$array_pos_end_container,$start_pos_end_container,$id_page,$id_article,$id_sous_article,$num_p);
                    $result_end_container = $xpath->query($xpath_query_end_container);
                    /**
                     * Si le dernier élément existe
                     */
                    if ($result_end_container->length>0){
                        $end_container_node = $result_end_container->item(0);
                        $end_container_node_type = $end_container_node->nodeType;
                        $str_after = "";
                        if ($end_container_node_type == 3){
                            $str_end_container_node = $end_container_node->textContent;
                            $selected_str_end_container_node = mb_substr($str_end_container_node, 0, $length_end_container, 'UTF-8');
                            if ($selected_str_end_container_node != $end_container_text){
                                return new Response('Erreur' . $xpath_query_end_container);
                            }
                            else{
                                $str_after = mb_substr($str_end_container_node, $length_end_container, null, 'UTF-8');
                                $selected_end_container_node = $xml_source->createTextNode($selected_str_end_container_node);
                            }
                        }
                        else{
                            $selected_end_container_node = $end_container_node->cloneNode(true);
                        }
                        $fragment_to_replace = $xml_source->createDocumentFragment();
                        $fragment_to_replace->appendChild($selected_start_container_node);
                        $next_sibling_start_container = $start_container_node->nextSibling;
                        while (!$next_sibling_start_container->isSameNode($end_container_node)){
                            $fragment_to_replace->appendChild($next_sibling_start_container->cloneNode(true));
                            $next_sibling_start_container = $next_sibling_start_container->nextSibling;
                        }
                        $fragment_to_replace->appendChild($selected_end_container_node);
                        /**
                         * Pour l'action d'ajoute d'une catégorie
                         */
                        if ($type_cmd_get == "add") {
                            if ($type == 'article_vise') {
                                $link_article_vise = $request->get("link");
                                $id_article_vise = "#inconnu";
                                $pos_last_slash = strrpos($link_article_vise, "/");
                                $volume_article_vise = intval(substr(substr($link_article_vise, 0, $pos_last_slash), strrpos(substr($link_article_vise, 0, $pos_last_slash), "/") + 1));
                                $num_article_vise = intval(substr($link_article_vise, $pos_last_slash + 1));
                                if (is_int($volume_article_vise) && is_int($num_article_vise)) {
                                    $id_article_vise = "#v" . $volume_article_vise . "-" . $num_article_vise;
                                }
                                $target_element = $xml_source->createElement("ref");
                                $attribute_target = $xml_source->createAttribute("target");
                                $attribute_target->value = $id_article_vise;
                                $target_element->appendChild($attribute_target);
                                $target_element->appendChild($fragment_to_replace->cloneNode(true));
                                $new_element = $xml_source->createElement($element_type);
                                $attribute = $xml_source->createAttribute($attribute_name);
                                $attribute->value = $type;
                                $new_element->appendChild($attribute);
                                $new_element->appendChild($target_element);
                            } else {
                                $new_element = $xml_source->createElement($element_type);
                                $attribute = $xml_source->createAttribute($attribute_name);
                                $attribute->value = $type;
                                $new_element->appendChild($attribute);
                                $new_element->appendChild($fragment_to_replace->cloneNode(true));
                                if ($sub_type != null) {
                                    $attribute = $xml_source->createAttribute("subtype");
                                    $attribute->value = $sub_type;
                                    $new_element->appendChild($attribute);
                                }
                            }
                            $parent_node = $end_container_node->parentNode;
                            if ($str_before != "") {
                                $new_text_node_before = $xml_source->createTextNode($str_before);
                                $parent_node->insertBefore($new_text_node_before, $start_container_node);
                            }
                            $parent_node->insertBefore($new_element, $start_container_node);
                            if ($str_after != "") {
                                $new_text_node_after = $xml_source->createTextNode($str_after);
                                $parent_node->insertBefore($new_text_node_after, $start_container_node);
                            }
                            $next_sibling_start_container = $start_container_node->nextSibling;
                            $clone_next_sibling_start_container = $next_sibling_start_container;
                            $parent_node->removeChild($start_container_node);
                            while(!$clone_next_sibling_start_container->isSameNode($end_container_node)){
                                $clone_next_sibling_start_container = $clone_next_sibling_start_container->nextSibling;
                                $parent_node->removeChild($next_sibling_start_container);
                                $next_sibling_start_container = $clone_next_sibling_start_container;
                            }
                            $parent_node->removeChild($end_container_node);
                            if ($xml_source->save("../src/AppBundle/Resources/xml/Correction/volume" . $volume . ".xml"))
                                return new Response("Succès!");
                            else
                                return new Response("La modification n'est pas encore enregistrée!");
                        }
                        else{
                            return new Response("Erreur-2");
                        }
                    }
                    /**
                     * Si le dernier élément n'existe pas
                     */
                    else{
                        return new Response('Erreur-EndContainer'.$xpath_query_end_container);
                    }
                }
                /**
                 * Si le premier élément n'existe pas
                 */
                else{
                    return new Response('Erreur-StartContainer'.$xpath_query_start_container);
                }

            }
            /**
             * Il existe un enfant qui n'est pas complet
             */
            else{
                return new Response('Erreur');
            }
        }
        return $this->render('AppBundle::404.html.twig', array('error' => 404));
    }
    /**
     * Afficher la page ($page) du volume ($volume)
     * @Route("/{volume}/{page}", requirements={"page" = "\d+", "volume" = "\d+"})
     * @param $volume
     * @param $page
     * @param Request $request
     * @return Response
     */
    public function showPageAction($volume, $page, Request $request){
        $id_page = 'v'.$volume.'-p'.$page;
        /**
         * Charger le fichier correspondant pour récupérer les infos de la page $page du volume $volume par DOMDocument
         */
        $xml_source = new \DOMDocument();
        $xml_source->load('../src/AppBundle/Resources/xml/volume'.$volume.'.xml');
        $xpath = new \DOMXpath($xml_source);
        /**
         * Traiter la méthode GET
         */
        if ($request->isMethod('GET')){
            $id_next_page = 'v'.$volume.'-p'.strval(intval($page)+1);
            $id_previous_page = 'v'.$volume.'-p'.strval(intval($page)-1);
            $next_page_href = strval(intval($page)+1);
            $previous_page_href = strval(intval($page)-1);
            $xslt_root = new \DOMDocument();
            $xslt_root->load('../src/AppBundle/Resources/xslt/page.xsl');
            $transform = new \XSLTProcessor();
            $transform->importStylesheet($xslt_root);
            $data = file_get_contents('../src/AppBundle/Resources/xml/volume'.$volume.'.xml');
            preg_match("~<pb(?P<atts>.*? xml:id=\"".$id_page."\".*?)/>~", $data, $find_pb, PREG_OFFSET_CAPTURE);
            if ($find_pb) {
                $prefix_id = "";
                $array_page_content = $this->getPageContent($data, $find_pb, $id_page, $xpath, $id_next_page, $id_previous_page, $previous_page_href, $next_page_href, $prefix_id);
                $content = $array_page_content[0];
                $previous_page_href = $array_page_content[1];
                $next_page_href = $array_page_content[2];
                $xml_transform = new \DOMDocument();
                $xml_transform->loadXML($content);
                $page_content = $transform->transformToDoc($xml_transform)->saveHTML();
                return $this->render('AppBundle::page.html.twig', array(
                    'page_content' => $page_content,
                    'next_page' => $next_page_href,
                    'previous_page' => $previous_page_href,
                    'volume' => $volume));
            }
        }
//        /**
//         * Traiter la méthode POST
//         */
//        elseif ($request->isMethod('POST')){
//            $type_cmd_get = $request->get("typeCmd");
//            $type = $request->get("type");
//            if ($type == 'italic' || $type == 'smallcaps') {
//                $element_type = 'emph';
//                $attribute_name = 'rend';
//            } else {
//                $element_type = 'seg';
//                $attribute_name = 'type';
//            }
//            $sub_type = $request->get("subType");
//            $array_in_p = json_decode($request->get("array_in_p"));
//            $array_p_div = json_decode($request->get("array_p_div"));
//            $id_article = 'v'.$volume.'-'.substr($array_p_div[1],0,strpos($array_p_div[1],"-"));
//            $id_sous_article = 'v'.$volume.'-'.$array_p_div[1];
//            $num_p = $array_p_div[0];
//            /**
//             * Tous les enfants sont dans le même parent de type d'élément (seg ou emph)
//             */
//            if (count($array_in_p)==1){
//                $selected_content = $array_in_p[0][0];
//                $array_pos = $array_in_p[0][1];
//                $start_pos_rpl = $array_pos[0];
//                $len_rpl = $array_pos[1];
//                $xpath_query = create_xpath_query($xpath,$array_pos,$start_pos_rpl,$id_page,$id_article,$id_sous_article,$num_p);
//                $result = $xpath->query($xpath_query);
//                if ($result->length>0){
//                    $rpl_node = $result->item(0);
//                    $rpl_node_type = $rpl_node->nodeType;
//                    $str_before = "";
//                    $str_after = "";
//                    if ($rpl_node_type == 3){
//                        $str_rpl_node_val = $rpl_node->textContent;
//                        $str_rpl_val = mb_substr($str_rpl_node_val, $start_pos_rpl, $len_rpl, 'UTF-8');
//                        if ($str_rpl_val != $selected_content){
//                            return new Response('Erreur' . $xpath_query);
//                        }
//                        else{
//                            $str_before = mb_substr($str_rpl_node_val, 0, $start_pos_rpl, 'UTF-8');
//                            $str_after = mb_substr($str_rpl_node_val, $start_pos_rpl + $len_rpl, null, 'UTF-8');
//                            $selected_node = $xml_source->createTextNode($selected_content);
//                        }
//                    }
//                    else{
//                        $selected_node = $rpl_node->cloneNode(true);
//                    }
////                    echo $selected_content.'---'.$str_rpl_val;
//                    /**
//                     * Pour l'action d'ajoute d'une catégorie
//                     */
//                    if ($type_cmd_get == "add") {
//                        if ($type == 'article_vise') {
//                            $link_article_vise = $request->get("link");
//                            $id_article_vise = "#inconnu";
//                            $pos_last_slash = strrpos($link_article_vise, "/");
//                            $volume_article_vise = intval(substr(substr($link_article_vise, 0, $pos_last_slash), strrpos(substr($link_article_vise, 0, $pos_last_slash), "/") + 1));
//                            $num_article_vise = intval(substr($link_article_vise, $pos_last_slash + 1));
//                            if (is_int($volume_article_vise) && is_int($num_article_vise)) {
//                                $id_article_vise = "#v" . $volume_article_vise . "-" . $num_article_vise;
//                            }
//                            $target_element = $xml_source->createElement("ref");
//                            $attribute_target = $xml_source->createAttribute("target");
//                            $attribute_target->value = $id_article_vise;
//                            $target_element->appendChild($attribute_target);
//                            $target_element->appendChild($selected_node);
//                            $new_element = $xml_source->createElement($element_type);
//                            $attribute = $xml_source->createAttribute($attribute_name);
//                            $attribute->value = $type;
//                            $new_element->appendChild($attribute);
//                            $new_element->appendChild($target_element);
//                        } else {
//                            $new_element = $xml_source->createElement($element_type);
//                            $attribute = $xml_source->createAttribute($attribute_name);
//                            $attribute->value = $type;
//                            $new_element->appendChild($attribute);
//                            $new_element->appendChild($selected_node);
//                            if ($sub_type != null) {
//                                $attribute = $xml_source->createAttribute("subtype");
//                                $attribute->value = $sub_type;
//                                $new_element->appendChild($attribute);
//                            }
//                        }
//                        $parent_node = $rpl_node->parentNode;
//                        if ($str_before != "") {
//                            $new_text_node_before = $xml_source->createTextNode($str_before);
//                            $parent_node->insertBefore($new_text_node_before, $rpl_node);
//                        }
//                        $parent_node->insertBefore($new_element, $rpl_node);
//                        if ($str_after != "") {
//                            $new_text_node_after = $xml_source->createTextNode($str_after);
//                            $parent_node->insertBefore($new_text_node_after, $rpl_node);
//                        }
//                        $parent_node->removeChild($rpl_node);
//                        $xml_source->save("../src/AppBundle/Resources/xml/Correction/volume" . $volume . ".xml");
//                        return new Response("Succès!");
//                    }
//                    /**
//                     * Pour l'action d'enlèvement d'une catégorie
//                     */
//                    elseif($type_cmd_get == "remove"){
//                        if ($rpl_node_type == 3){
//                            $parent_node = $rpl_node->parentNode;
//                        }
//                        elseif ($rpl_node_type == 1){
//                            $parent_node = $rpl_node;
//                        }
//                        else{
//                            return new Response('Erreur' . $xpath_query);
//                        }
//                        $found_remove_node = false;
//                        $remove_node = $parent_node;
//                        while ($remove_node->nodeName!="p" && !$found_remove_node){
//                            $parent_remove_att = $remove_node->attributes->getNamedItem($attribute_name);
//                            if ( $remove_node->nodeName == $element_type && $parent_remove_att!=null) {
//                                if ($parent_remove_att->nodeValue == $type) {
//                                    $found_remove_node = true;
//                                    break;
//                                }
//                            }
//                            $remove_node = $remove_node->parentNode;
//                        }
//                        if ($found_remove_node){
//                            if ($remove_node->isSameNode($rpl_node)){
//                                $children_remove_node = $remove_node->childNodes;
//                                for($i=0;$i<$children_remove_node->length;$i++){
//                                    $remove_node->parentNode->insertBefore($children_remove_node->item($i)->cloneNode(true),$remove_node);
//                                }
//                                $remove_node->parentNode->removeChild($remove_node);
//                            }
//                            else{
//                                if ($parent_node->isSameNode($rpl_node)){
//                                    $parent_node = $parent_node->parentNode;
//                                }
//                                $test_same_remove_node = false;
//                                /**
//                                 * Grouper les frères précédents et suivants par la balise correspondante
//                                 * au parent du texte selectionné (les descendants aussi).
//                                 * Remplacer la valeur du parent du texte sélectionné par ces éléments
//                                 */
//                                $selected_node_to_remove = $xml_source->createDocumentFragment();
//                                $selected_node_to_remove->appendChild($selected_node);
//                                if ($str_before != "") {
//                                    $text_node_before = $xml_source->createTextNode($str_before);
//                                    $parent_node->insertBefore($text_node_before,$rpl_node);
//                                }
//                                $selected_node_to_remove = $parent_node->insertBefore($selected_node_to_remove,$rpl_node);
//                                if ($str_after != "") {
//                                    $text_node_after = $xml_source->createTextNode($str_after);
//                                    $parent_node->insertBefore($text_node_after,$rpl_node);
//                                }
//                                $parent_node->removeChild($rpl_node);
//                                $elem_before_selected_node = null;
//                                $elem_after_selected_node=null;
//                                while (!$test_same_remove_node){
//                                    $parent_node_name = $parent_node->nodeName;
//                                    $parent_node_attributes = $parent_node->attributes;
//                                    /**
//                                     * Grouper les frères précédents de l'ancêtre du texte sélectionné (ou son ancetre)
//                                     */
//                                    $elem_before_selected_node = $xml_source->createElement($parent_node_name);
//                                    for ($i=0;$i<$parent_node_attributes->length;$i++){
//                                        $att = $xml_source->createAttribute($parent_node_attributes->item($i)->nodeName);
//                                        $att->value = $parent_node_attributes->item($i)->nodeValue;
//                                        $elem_before_selected_node->appendChild($att);
//                                    }
//                                    $prev_sibling_selected_node = $selected_node_to_remove->previousSibling;
//                                    while ($prev_sibling_selected_node != null){
//                                        $elem_before_selected_node->insertBefore($prev_sibling_selected_node->cloneNode(true),$elem_before_selected_node->firstChild);
//                                        $prev_sibling_selected_node = $prev_sibling_selected_node->previousSibling;
//                                    }
//                                    /**
//                                     * Grouper les frères suivants de l'ancêtre du texte sélectionné (ou son ancetre)
//                                     */
//                                    $elem_after_selected_node = $xml_source->createElement($parent_node_name);
//                                    for ($i=0;$i<$parent_node_attributes->length;$i++){
//                                        $att = $xml_source->createAttribute($parent_node_attributes->item($i)->nodeName);
//                                        $att->value = $parent_node_attributes->item($i)->nodeValue;
//                                        $elem_after_selected_node->appendChild($att);
//                                    }
//                                    $next_sibling_selected_node = $selected_node_to_remove->nextSibling;
//                                    while ($next_sibling_selected_node != null){
//                                        $elem_after_selected_node->appendChild($next_sibling_selected_node->cloneNode(true));
//                                        $next_sibling_selected_node = $next_sibling_selected_node->nextSibling;
//                                    }
//                                    /**
//                                     * Créer la balise contenant le texte sélectionnné
//                                     */
//                                    if (!$parent_node->isSameNode($remove_node)){
//                                        if ($parent_node_name != "ref" || $type!="article_vise"){
//                                            $new_element_selected = $xml_source->createElement($parent_node_name);
//                                            for ($i=0;$i<$parent_node_attributes->length;$i++){
//                                                $att = $xml_source->createAttribute($parent_node_attributes->item($i)->nodeName);
//                                                $att->value = $parent_node_attributes->item($i)->nodeValue;
//                                                $new_element_selected->appendChild($att);
//                                            }
//                                            $new_element_selected->appendChild($selected_node_to_remove);
//                                            $selected_node_to_remove = $new_element_selected;
//                                        }
//                                    }
//                                    else{
//                                        $test_same_remove_node = true;
//                                    }
//                                    /**
//                                     * Remplacer le noeud correspondant au parent actuel par ses enfants créés
//                                     */
//                                    $grand_parent_node = $parent_node->parentNode;
//                                    if ($elem_before_selected_node->textContent !=""){
//                                        $grand_parent_node->insertBefore($elem_before_selected_node,$parent_node);
//                                    }
//                                    $grand_parent_node->insertBefore($selected_node_to_remove,$parent_node);
//                                    if ($elem_after_selected_node->textContent !=""){
//                                        $grand_parent_node->insertBefore($elem_after_selected_node,$parent_node);
//                                    }
//                                    $grand_parent_node->removeChild($parent_node);
//                                    $parent_node= $selected_node_to_remove->parentNode;
//                                }
//                            }
//                            if ($xml_source->save("../src/AppBundle/Resources/xml/Correction/volume" . $volume . ".xml"))
//                                return new Response("Succès!");
//                            else
//                                return new Response("La modification n'est pas encore enregistrée!");
//                        }
//                        else{
//                            return new Response('Erreur' . $xpath_query);
//                        }
//                    }
//                    /**
//                     * Pour l'action de correction du texte sélectionné
//                     */
//                    elseif($type_cmd_get == "correct"){
//                        $correct_text = $request->get("correct_text");
//                        $parent_node = $rpl_node->parentNode;
//                        if ($str_before != "") {
//                            $new_text_node_before = $xml_source->createTextNode($str_before);
//                            $parent_node->insertBefore($new_text_node_before, $rpl_node);
//                        }
//                        $text_node_to_replace = $xml_source->createTextNode($correct_text);
//                        $parent_node->insertBefore($text_node_to_replace, $rpl_node);
//                        if ($str_after != "") {
//                            $new_text_node_after = $xml_source->createTextNode($str_after);
//                            $parent_node->insertBefore($new_text_node_after, $rpl_node);
//                        }
//                        $parent_node->removeChild($rpl_node);
//                        if ($xml_source->save("../src/AppBundle/Resources/xml/Correction/volume" . $volume . ".xml"))
//                            return new Response("Succès!");
//                        else
//                            return new Response("La modification n'est pas encore enregistrée!");
//                    }
//                }
//                else{
//                    return new Response('Query-Erreur'.$xpath_query);
//                }
//            }
//            /**
//             * Tous les enfants sont dans le même parent de type d'élément (p)
//             */
//            elseif (count($array_in_p)==2){
//                $start_container_text = $array_in_p[0][0];
//                $array_pos_start_container = $array_in_p[0][1];
//                $start_pos_start_container = $array_pos_start_container[0];
//                $end_container_text = $array_in_p[1][0];
//                $array_pos_end_container = $array_in_p[1][1];
//                $length_end_container = $array_pos_end_container[1];
//                /**
//                 * Rechercher le premier élément du texte sélectionné
//                 */
//                $xpath_query_start_container = create_xpath_query($xpath,$array_pos_start_container,$start_pos_start_container,$id_page,$id_article,$id_sous_article,$num_p);
//                $result_start_container = $xpath->query($xpath_query_start_container);
//                /**
//                 * Si le premier élément existe
//                 */
//                if ($result_start_container->length>0){
//                    $start_container_node = $result_start_container->item(0);
//                    $start_container_node_type = $start_container_node->nodeType;
//                    $str_before = "";
//                    if ($start_container_node_type == 3){
//                        $str_start_container_node = $start_container_node->textContent;
//                        $selected_str_start_container_node = mb_substr($str_start_container_node, $start_pos_start_container, null, 'UTF-8');
//                        if ($selected_str_start_container_node != $start_container_text){
//                            return new Response('Erreur' . $xpath_query_start_container);
//                        }
//                        else{
//                            $str_before = mb_substr($str_start_container_node, 0, $start_pos_start_container, 'UTF-8');
//                            $selected_start_container_node = $xml_source->createTextNode($selected_str_start_container_node);
//                        }
//                    }
//                    else{
//                        $selected_start_container_node = $start_container_node->cloneNode(true);
//                    }
//                    /**
//                     * Rechercher le dernier élément du texte sélectionné
//                     */
//                    $xpath_query_end_container = create_xpath_query($xpath,$array_pos_end_container,$start_pos_end_container,$id_page,$id_article,$id_sous_article,$num_p);
//                    $result_end_container = $xpath->query($xpath_query_end_container);
//                    /**
//                     * Si le dernier élément existe
//                     */
//                    if ($result_end_container->length>0){
//                        $end_container_node = $result_end_container->item(0);
//                        $end_container_node_type = $end_container_node->nodeType;
//                        $str_after = "";
//                        if ($end_container_node_type == 3){
//                            $str_end_container_node = $end_container_node->textContent;
//                            $selected_str_end_container_node = mb_substr($str_end_container_node, 0, $length_end_container, 'UTF-8');
//                            if ($selected_str_end_container_node != $end_container_text){
//                                return new Response('Erreur' . $xpath_query_end_container);
//                            }
//                            else{
//                                $str_after = mb_substr($str_end_container_node, $length_end_container, null, 'UTF-8');
//                                $selected_end_container_node = $xml_source->createTextNode($selected_str_end_container_node);
//                            }
//                        }
//                        else{
//                            $selected_end_container_node = $end_container_node->cloneNode(true);
//                        }
//                        $fragment_to_replace = $xml_source->createDocumentFragment();
//                        $fragment_to_replace->appendChild($selected_start_container_node);
//                        $next_sibling_start_container = $start_container_node->nextSibling;
//                        while (!$next_sibling_start_container->isSameNode($end_container_node)){
//                            $fragment_to_replace->appendChild($next_sibling_start_container->cloneNode(true));
//                            $next_sibling_start_container = $next_sibling_start_container->nextSibling;
//                        }
//                        $fragment_to_replace->appendChild($selected_end_container_node);
//                        /**
//                         * Pour l'action d'ajoute d'une catégorie
//                         */
//                        if ($type_cmd_get == "add") {
//                            if ($type == 'article_vise') {
//                                $link_article_vise = $request->get("link");
//                                $id_article_vise = "#inconnu";
//                                $pos_last_slash = strrpos($link_article_vise, "/");
//                                $volume_article_vise = intval(substr(substr($link_article_vise, 0, $pos_last_slash), strrpos(substr($link_article_vise, 0, $pos_last_slash), "/") + 1));
//                                $num_article_vise = intval(substr($link_article_vise, $pos_last_slash + 1));
//                                if (is_int($volume_article_vise) && is_int($num_article_vise)) {
//                                    $id_article_vise = "#v" . $volume_article_vise . "-" . $num_article_vise;
//                                }
//                                $target_element = $xml_source->createElement("ref");
//                                $attribute_target = $xml_source->createAttribute("target");
//                                $attribute_target->value = $id_article_vise;
//                                $target_element->appendChild($attribute_target);
//                                $target_element->appendChild($fragment_to_replace->cloneNode(true));
//                                $new_element = $xml_source->createElement($element_type);
//                                $attribute = $xml_source->createAttribute($attribute_name);
//                                $attribute->value = $type;
//                                $new_element->appendChild($attribute);
//                                $new_element->appendChild($target_element);
//                            } else {
//                                $new_element = $xml_source->createElement($element_type);
//                                $attribute = $xml_source->createAttribute($attribute_name);
//                                $attribute->value = $type;
//                                $new_element->appendChild($attribute);
//                                $new_element->appendChild($fragment_to_replace->cloneNode(true));
//                                if ($sub_type != null) {
//                                    $attribute = $xml_source->createAttribute("subtype");
//                                    $attribute->value = $sub_type;
//                                    $new_element->appendChild($attribute);
//                                }
//                            }
//                            $parent_node = $end_container_node->parentNode;
//                            if ($str_before != "") {
//                                $new_text_node_before = $xml_source->createTextNode($str_before);
//                                $parent_node->insertBefore($new_text_node_before, $start_container_node);
//                            }
//                            $parent_node->insertBefore($new_element, $start_container_node);
//                            if ($str_after != "") {
//                                $new_text_node_after = $xml_source->createTextNode($str_after);
//                                $parent_node->insertBefore($new_text_node_after, $start_container_node);
//                            }
//                            $next_sibling_start_container = $start_container_node->nextSibling;
//                            $clone_next_sibling_start_container = $next_sibling_start_container;
//                            $parent_node->removeChild($start_container_node);
//                            while(!$clone_next_sibling_start_container->isSameNode($end_container_node)){
//                                $clone_next_sibling_start_container = $clone_next_sibling_start_container->nextSibling;
//                                $parent_node->removeChild($next_sibling_start_container);
//                                $next_sibling_start_container = $clone_next_sibling_start_container;
//                            }
//                            $parent_node->removeChild($end_container_node);
//                            if ($xml_source->save("../src/AppBundle/Resources/xml/Correction/volume" . $volume . ".xml"))
//                                return new Response("Succès!");
//                            else
//                                return new Response("La modification n'est pas encore enregistrée!");
//                        }
//                        else{
//                            return new Response("Erreur-2");
//                        }
//                    }
//                    /**
//                     * Si le dernier élément n'existe pas
//                     */
//                    else{
//                        return new Response('Erreur-EndContainer'.$xpath_query_end_container);
//                    }
//                }
//                /**
//                 * Si le premier élément n'existe pas
//                 */
//                else{
//                    return new Response('Erreur-StartContainer'.$xpath_query_start_container);
//                }
//
//            }
//            /**
//             * Il existe un enfant qui n'est pas complet
//             */
//            else{
//                return new Response('Erreur');
//            }
//        }
        return $this->render('AppBundle::404.html.twig', array('error' => 404));
    }

    /**
     * @Route("/{volume}/{start_page}-{end_page}", requirements={"start_page" = "\d+", "end_page" = "\d+","volume" = "\d+"})
     * @param $volume
     * @param $start_page
     * @param $end_page
     * @return Response
     */
    public function showPagesAction($volume, $start_page, $end_page){
        set_time_limit(0);
        ini_set("memory_limit", "-1");
        $id_start_page = 'v'.$volume.'-p'.$start_page;
        $id_end_page = 'v'.$volume.'-p'.$end_page;
        $xml_source = new \DOMDocument();
        $xml_source->load('../src/AppBundle/Resources/xml/volume'.$volume.'.xml');
        $xslt_root = new \DOMDocument();
        $xslt_root->load('../src/AppBundle/Resources/xslt/pages.xsl');
        $transform = new \XSLTProcessor();
        $transform->importStylesheet($xslt_root);
        $xpath = new \DOMXpath($xml_source);
        $data = file_get_contents('../src/AppBundle/Resources/xml/volume'.$volume.'.xml');
        $id_first_page = $xpath->query("//pb[position()=1]/@xml:id")->item(0)->nodeValue;
        $id_last_page = $xpath->query("//pb[position()=last()]/@xml:id")->item(0)->nodeValue;
        $num_first_page = intval(substr($id_first_page,strpos($id_first_page,"p")+1));
        $num_last_page = intval(substr($id_last_page,strpos($id_last_page,"p")+1));
        if ($num_first_page<=intval($start_page) and $num_last_page+1>=intval($end_page)){
            $content ="";
            while ($id_start_page!=$id_end_page){
                $id_next_page  = 'v'.$volume.'-p'.strval(intval(substr($id_start_page,strpos($id_start_page,"p")+1))+1);
                preg_match("~<pb(?P<atts>.*? xml:id=\"".$id_start_page."\".*?)/>~", $data, $find_start_pb,PREG_OFFSET_CAPTURE);
                $prefix_id = $id_start_page.'-';
                $array_content_page = $this->getPageContent($data,$find_start_pb,$id_start_page,$xpath,$id_next_page,null,null,null,$prefix_id);
                $content .= $array_content_page[0];
                $id_start_page = $id_next_page;
            }
            $content = '<div type="pages">'.$content.'</div>';
            $xml_transform = new \DOMDocument();
            $xml_transform->loadXML($content);
            $page_content = $transform->transformToDoc($xml_transform)->saveHTML();
            return $this->render('AppBundle::pages.html.twig', array(
                'page_content' => $page_content));
        }
        else{
            return $this->render('AppBundle::404.html.twig', array('error' => 404));
        }
    }
    /**
     * @Route("/2/part{x}",requirements={"x" = "\d+"})
     * @param $x
     * @return Response
     */
    public function showPagesByVolumePartAction($x){
        $data = file_get_contents('../src/AppBundle/Resources/views/part'.$x.'.html');
        return new Response($data);
    }
    /**
     * @Route("/2/")
     * @Route("/2")
     */
    public function showPagesByVolumeAction(){
        $data = file_get_contents('../src/AppBundle/Resources/views/volume2.html');
        return new Response($data);
    }

    /**
     * Traiter les données représentantes de la page
     * @param $data
     * @param $find_pb
     * @param $id_page
     * @param \DOMXPath $xpath
     * @param $id_next_page
     * @param $id_previous_page
     * @param $previous_page_href
     * @param $next_page_href
     * @param $prefix_id
     * @return array
     */
    public function getPageContent($data, $find_pb,$id_page, \DOMXPath $xpath, $id_next_page, $id_previous_page,$previous_page_href,$next_page_href,$prefix_id){
        $start_pos = $find_pb[0][1]+strlen($find_pb[0][0]);
        $params = $find_pb['atts'][0];
        preg_match("~<pb .*? xml:id=\"".$id_next_page."\".*?/>~", $data, $find_next_pb, PREG_OFFSET_CAPTURE);
        if ($find_next_pb){
            $end_pos = $find_next_pb[0][1];
        }
        else{
            $next_page_href = null;
            preg_match("~</div>\n<div type=\"errata\">~", $data, $find_errata,PREG_OFFSET_CAPTURE);
            $end_pos = $find_errata[0][1];
        }
        if ($id_previous_page!=null){
            $find_previous_page = preg_match("~<pb .*? xml:id=\"".$id_previous_page."\".*?/>~", $data, $find_previous_page, PREG_OFFSET_CAPTURE);
            if (!$find_previous_page){
                $previous_page_href = null;
            }
        }
        $tag_before = "<div".$params.">\r\n";
        $xpath_find_parent_current_pb = $xpath->query("//pb[@xml:id='".$id_page."']/ancestor::node()[position()=1]")->item(0)->nodeName;
        if ($xpath_find_parent_current_pb == 'seg'){
            $seg_tag = '<seg type="'.$xpath->query("//pb[@xml:id='".$id_page."']/ancestor::node()[position()=1]/@type")->item(0)->nodeValue.'">';
            $p_tag = '<p n="'.$xpath->query("//pb[@xml:id='".$id_page."']/ancestor::node()[position()=2]/@n")->item(0)->nodeValue.'" style="text-indent:0;">';
            $div_sous_article_tag = '<div type="'.$xpath->query("//pb[@xml:id='".$id_page."']/ancestor::node()[position()=3]/@type")->item(0)->nodeValue.'" xml:id="'.$prefix_id.$xpath->query("//pb[@xml:id='".$id_page."']/ancestor::node()[position()=3]/@xml:id")->item(0)->nodeValue.'">'."\r\n";
            $div_article_tag = '<div type="'.$xpath->query("//pb[@xml:id='".$id_page."']/ancestor::node()[position()=4]/@type")->item(0)->nodeValue.'" xml:id="'.$prefix_id.$xpath->query("//pb[@xml:id='".$id_page."']/ancestor::node()[position()=4]/@xml:id")->item(0)->nodeValue.'">'."\r\n";
            $tag_before .= $div_article_tag.$div_sous_article_tag.$p_tag.$seg_tag;
        }
        elseif ($xpath_find_parent_current_pb == 'p'){
            $p_tag = '<p n="'.$xpath->query("//pb[@xml:id='".$id_page."']/ancestor::node()[position()=1]/@n")->item(0)->nodeValue.'" style="text-indent:0;">'."\r\n";
            $div_sous_article_tag = '<div type="'.$xpath->query("//pb[@xml:id='".$id_page."']/ancestor::node()[position()=2]/@type")->item(0)->nodeValue.'" xml:id="'.$prefix_id.$xpath->query("//pb[@xml:id='".$id_page."']/ancestor::node()[position()=2]/@xml:id")->item(0)->nodeValue.'">'."\r\n";
            $div_article_tag = '<div type="'.$xpath->query("//pb[@xml:id='".$id_page."']/ancestor::node()[position()=3]/@type")->item(0)->nodeValue.'" xml:id="'.$prefix_id.$xpath->query("//pb[@xml:id='".$id_page."']/ancestor::node()[position()=3]/@xml:id")->item(0)->nodeValue.'">'."\r\n";
            $tag_before .= $div_article_tag.$div_sous_article_tag.$p_tag;
        }
        elseif ($xpath_find_parent_current_pb == 'div'){
            $current_div_attributes = $xpath->query("//pb[@xml:id='".$id_page."']/ancestor::node()[position()=1]")->item(0)->attributes;
            $current_div_type = $current_div_attributes->item(0)->nodeValue;
            if ($current_div_type == 'articles'){
                $tag_before .= "\r\n";
            }
            elseif ($current_div_type == 'article'){
                $current_div_id = $current_div_attributes->item(1)->nodeValue;
                $tag_before .= "<div type=\"".$current_div_type."\" xml:id=\"".$prefix_id.$current_div_id."\">\r\n";
            }
            else{
                $current_div_id = $current_div_attributes->item(1)->nodeValue;
                $tag_before .= '<div type="'.$xpath->query("//pb[@xml:id='".$id_page."']/ancestor::node()[position()=2]/@type")->item(0)->nodeValue.'" xml:id="'.$prefix_id.$xpath->query("//pb[@xml:id='".$id_page."']/ancestor::node()[position()=2]/@xml:id")->item(0)->nodeValue.'">'."\r\n".'<div type="'.$current_div_type.'" xml:id="'.$prefix_id.$current_div_id.'">'."\r\n";
            }
        }
        $tag_after='';
        $xpath_find_parent_next_pb = $xpath->query("//pb[@xml:id='".$id_next_page."']/ancestor::node()[position()=1]");
        if ($xpath_find_parent_next_pb->length >0){
            if ($xpath_find_parent_next_pb->item(0)->nodeName == 'seg'){
                $tag_after = "</seg></p></div>\r\n</div>\r\n";
            }
            elseif ($xpath_find_parent_next_pb->item(0)->nodeName == 'p'){
                $tag_after = "</p>\r\n</div>\r\n</div>\r\n";
            }
            elseif ($xpath_find_parent_next_pb->item(0)->nodeName == 'div'){
                $current_div_type = $xpath->query("//pb[@xml:id='".$id_next_page."']/ancestor::node()[position()=1]/@type")->item(0)->nodeValue;
                if ($current_div_type == 'articles'){
                    $tag_after = "\r\n";
                }
                elseif ($current_div_type == 'article'){
                    $tag_after = "</div>\r\n";
                }
                else{
                    $tag_after = "</div>\r\n</div>\r\n";
                }
            }
        }
        return [$tag_before.substr($data,$start_pos,$end_pos-$start_pos).$tag_after.'</div>',$previous_page_href,$next_page_href];
    }
}