<?php

namespace AppBundle\Controller;

use AppBundle\Entity\SousArticle;
use AppBundle\Entity\Article;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class DefaultController
 * @package AppBundle\Controller
 */
class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        return $this->redirect('./article/2');
    }
    /**
     * @Route("/{volume}/")
     * @Route("/{volume}")
     */
    public function homePageByVolumeAction()
    {
        return $this->render('AppBundle::index.html.twig');
    }
    /**
     * @Route("/article/{volume}/",requirements={"volume" = "\d+"})
     * @Route("/article/{volume}",requirements={"volume" = "\d+"})
     */
    public function listArticlesByVolumeAction($volume)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $repo = $em->getRepository("AppBundle:Article");
        $query = $repo->createQueryBuilder('a')->where('a.volume=:volume')->setParameter('volume',
            intval($volume))->orderBy('a.position','ASC')->getQuery();
        $articles = $query->getResult();
        $list_articles = [];
        foreach ($articles as $article){
            $article_id = $article->getId();
            $repo = $em->getRepository("AppBundle:SousArticle");
            $query = $repo->createQueryBuilder('sa')->where('sa.article=:article_id')->setParameter('article_id',$article_id)->orderBy('sa.position','ASC')->getQuery();
            $sous_articles = $query->getResult();
            $list_sous_articles_by_article = array($article->getVedette());
            foreach ($sous_articles as $sous_article){
                array_push($list_sous_articles_by_article,$sous_article->getVedette());
            }
            $list_articles += array($article->getPosition()=>$list_sous_articles_by_article);

        }
        return $this->render('AppBundle::articles_volume.html.twig', array('volume' => $volume, 'list_articles' =>$list_articles));
    }
    /**
     * @Route("/db_create/{volume}",requirements={"volume" = "\d+"})
     */
    public function dbCreateAction($volume){
        set_time_limit(0);
        $xml_source = new \DOMDocument();
        $xml_source->load('../src/AppBundle/Resources/xml/volume'.$volume.'.xml');
        $xpath = new \DOMXpath($xml_source);
        $list_articles = $xpath->query("//div[@type='article']");
        $i=0;
        while ($i<$list_articles->length){
            $article = $list_articles->item($i);
            $i++;
            $xml_article = new \DOMDocument();
            $xml_article->appendChild($xml_article->importNode($article,true));
            $xpath_article = new \DOMXPath($xml_article);
            $vedette_adresse_find = $xpath_article->query("//div[@type='adresse']/child::p[position()
            =1]/child::seg[@type='vedette_adresse']");
            if ($vedette_adresse_find->length){
                $vedette_adresse = $vedette_adresse_find->item(0)->textContent;
            }
            else{
                $vedette_adresse="Adresse sans vedette";
            }
            $current_article = $this->createArticle($i,$volume,$vedette_adresse);
            $list_entrees = $xpath_article->query("//div[@type='entree']/child::p[position()
            =1]/child::seg[@type='vedette_entree']");
            $j=0;
            while ($j<$list_entrees->length){
                $entree = $list_entrees->item($j);
                $j++;
                $vedette_entree = $entree->textContent;
                $this->createSousArticle($current_article, $j, $vedette_entree);
            }
        }
        return new Response("Create Ok!");
    }

    public function createArticle($position, $volume, $vedette){
        $article = new Article();
        $article->setPosition($position);
        $article->setVolume($volume);
        $article->setVedette($vedette);
        $em = $this->getDoctrine()->getManager();
        $em->persist($article);
        $em->flush();
        return $article;
    }
    public function removeArticle($position, $volume){
        $em = $this->getDoctrine()->getEntityManager();
        $repo = $em->getRepository('AppBundle:Article');
        $article = $repo->findOneBy(array('position'=>$position, 'volume'=>$volume));
        $em->remove($article);
        $em->flush();
    }

    public function createSousArticle($current_article,$position,$vedette){
        $sous_article = new SousArticle();
        $sous_article->setPosition($position);
        $sous_article->setArticle($current_article);
        $sous_article->setVedette($vedette);
        $em = $this->getDoctrine()->getManager();
        $em->persist($sous_article);
        $em->flush();
    }

}
