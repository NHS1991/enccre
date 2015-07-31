<?php


namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="Article")
 */
class Article {
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\OneToMany(targetEntity="SousArticle", mappedBy="article")
     */
    protected $sous_articles;
    /**
     * @ORM\Column(type="integer")
     */
    protected $position;
    /**
     * @ORM\Column(type="integer")
     */
    protected $volume;
    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $vedette;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->sous_articles = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set position
     *
     * @param integer $position
     * @return Article
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer 
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set volume
     *
     * @param integer $volume
     * @return Article
     */
    public function setVolume($volume)
    {
        $this->volume = $volume;

        return $this;
    }

    /**
     * Get volume
     *
     * @return integer 
     */
    public function getVolume()
    {
        return $this->volume;
    }

    /**
     * Set vedette
     *
     * @param string $vedette
     * @return Article
     */
    public function setVedette($vedette)
    {
        $this->vedette = $vedette;

        return $this;
    }

    /**
     * Get vedette
     *
     * @return string 
     */
    public function getVedette()
    {
        return $this->vedette;
    }

    /**
     * Add sous_articles
     *
     * @param \AppBundle\Entity\SousArticle $sousArticles
     * @return Article
     */
    public function addSousArticle(\AppBundle\Entity\SousArticle $sousArticles)
    {
        $this->sous_articles[] = $sousArticles;

        return $this;
    }

    /**
     * Remove sous_articles
     *
     * @param \AppBundle\Entity\SousArticle $sousArticles
     */
    public function removeSousArticle(\AppBundle\Entity\SousArticle $sousArticles)
    {
        $this->sous_articles->removeElement($sousArticles);
    }

    /**
     * Get sous_articles
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSousArticles()
    {
        return $this->sous_articles;
    }
}
