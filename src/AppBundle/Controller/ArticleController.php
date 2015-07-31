<?php

namespace AppBundle\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class ArticleController
 * @package AppBundle\Controller
 * @Route("/article")
 */
class ArticleController extends Controller{
    /**
     * @Route("/{volume}/{id}", requirements={"id" = "\d+", "volume" = "\d+"})
     * @param $volume
     * @param $id
     * @param bool|false $showByName
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showArticleById($volume,$id,$showByName=false){
        $id_article = 'v'.$volume.'-'.$id;
        $xml_source = new \DOMDocument();
        $xml_source->load('../src/AppBundle/Resources/xml/volume'.$volume.'.xml');
        $xslt_root = new \DOMDocument();
        $xslt_root->load('../src/AppBundle/Resources/xslt/article.xsl');
        $transform = new \XSLTProcessor();
        $transform->importStylesheet($xslt_root);
        $xpath = new \DOMXpath($xml_source);
        $page_debut_xpath = $xpath->query("//div[@type='article' and @xml:id='".$id_article
            ."']/preceding::pb[position()
        =1]");
        $num_vue = $page_debut_xpath->item(0)->attributes->item(1)->nodeValue;
        $page_debut = substr($num_vue,strpos($num_vue,"p")+1);
        $article_result = $xpath->query("//div[@type='article' and @xml:id='".$id_article."']");
        $vedette_adresse_text = $xpath->query("//div[@type='article' and @xml:id='".$id_article."']/child::div[position()=1]/child::p[position()=1]/child::seg[@type='vedette_adresse']/descendant::text()");
        $vedette_adresse = '';
        if ($vedette_adresse_text->length >0){
            for ($i=0;$i<$vedette_adresse_text->length;$i++){
                $vedette_adresse .=  $vedette_adresse_text->item($i)->nodeValue;
            }
        }
        $pos_article =null;
        if ($article_result->length >0){
            $content =  $article_result->item(0);
            $xml_transform = new \DOMDocument();
            $xml_transform->appendChild($xml_transform->importNode($content,true));
            $article = $transform->transformToDoc($xml_transform)->saveHTML();
            $pos_article = intval($id);
            $next_article = $pos_article+1;
            $previous_article = $pos_article-1;
            if ($pos_article ==1){
                $previous_article = null;
            }
            else{
                if ($showByName){
                    $previous_article_id = 'v'.$volume.'-'.$previous_article;
                    $previous_article_vedette_adresse_text = $xpath->query("//div[@type='article' and @xml:id='".$previous_article_id."']/child::div[position()=1]/child::p[position()=1]/child::seg[@type='vedette_adresse']");
                    if ($previous_article_vedette_adresse_text->length >0){
                        $previous_article = $previous_article_vedette_adresse_text->item(0)->textContent;
                        $previous_article = str_replace(" ","_",$previous_article);
                    }
                }
            }
            $next_article_id = 'v'.$volume.'-'.$next_article;
            $xpath_find_next_article = $xpath->query("//div[@type='article' and @xml:id='".$next_article_id."']");
            if ($xpath_find_next_article->length == 0){
                $next_article = null;
            }
            else{
                if ($showByName){
                    $next_article_vedette_adresse_text = $xpath->query("//div[@type='article' and @xml:id='".$next_article_id."']/child::div[position()=1]/child::p[position()=1]/child::seg[@type='vedette_adresse']");
                    if ($next_article_vedette_adresse_text->length >0){
                        $next_article = $next_article_vedette_adresse_text->item(0)->textContent;
                        $next_article=str_replace(" ","_",$next_article);
                    }
                }
            }
            return $this->render('AppBundle::article.html.twig', array('article' => $article,
                                                                        'id_article'=>$id,
                                                                        'name_article' => $vedette_adresse,
                                                                        'next_article' => $next_article,
                                                                        'previous_article' => $previous_article,
                                                                        'volume' => $volume,
                                                                        'first_page'=>$page_debut));
        }
        return $this->render('AppBundle::404.html.twig', array('error'=>404));
    }
    /**
     * @Route("/{volume}/{name}", requirements={"volume" = "\d+", "name"="\D.*"})
     * @param $volume
     * @param $name
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showArticleByName($volume,$name){
        $vedette_adresse = str_replace("_"," ",$name);
        $xml_source = new \DOMDocument();
        $xml_source->load('../src/AppBundle/Resources/xml/volume'.$volume.'.xml');
        $xpath = new \DOMXpath($xml_source);
        $find_vedette = false;
        $vedette_adresse_result = $xpath->query("//div[@type='article']/child::div[position()=1]/child::p[position()=1]/child::seg[@type='vedette_adresse']");
        if ($vedette_adresse_result->length>0){
            $i = 0;
            while ($i<$vedette_adresse_result->length and !$find_vedette){
                $vedette_text = $vedette_adresse_result->item($i)->textContent;
                if ($vedette_text == $vedette_adresse){
                    $find_vedette = true;
                }
                $i++;
            }
            if ($find_vedette){
                $xml_id = $vedette_adresse_result->item($i-1)->parentNode->parentNode->parentNode->attributes->item(1)->nodeValue;
                $id = substr($xml_id,strrpos($xml_id,'-')+1);
                return $this->showArticleById($volume,$id,true);
            }
        }
        return $this->render('AppBundle::404.html.twig', array('error'=>404));
    }
} 