<?php


namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="SousArticle")
 */
class SousArticle {
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\ManyToOne(targetEntity="Article", inversedBy="sous_articles")
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id")
     */
    protected $article;

    /**
     * @ORM\Column(type="integer")
     */
    protected $position;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $vedette;

    /**
     * Set article
     *
     * @param \AppBundle\Entity\Article $article
     * @return SousArticle
     */
    public function setArticle(\AppBundle\Entity\Article $article = null)
    {
        $this->article = $article;

        return $this;
    }

    /**
     * Get article
     *
     * @return \AppBundle\Entity\Article 
     */
    public function getArticle()
    {
        return $this->article;
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
     * @return SousArticle
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
     * Set vedette
     *
     * @param string $vedette
     * @return SousArticle
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
}
