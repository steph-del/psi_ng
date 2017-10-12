<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\TableNode;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\SerializedName;

/**
 * Table_
 * Doctrine "Table" is a reserved name, so we call it Table_
 *
 * @ORM\Table(name="psi_table")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Table_Repository")
 *
 * @ExclusionPolicy("all")
 */
class Table_
{
    public function __construct()
    {
        $this->tNodes = new ArrayCollection();
    }

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Expose
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     *
     * @Expose
     */
    private $name;

    /**
     * @var \stdClass
     *
     * @ORM\Column(name="author", type="object", nullable=true)
     *
     * @Expose
     */
    private $author;

    /**
    * @var \stdClass
    *
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\TableNode", mappedBy="table", cascade={"persist"})
    *
    * @Expose
    * @Type("ArrayCollection<AppBundle\Entity\TableNode>")
    * @SerializedName("tNodes")
    */
    private $tNodes;


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
     * Set name
     *
     * @param string $name
     *
     * @return Table
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set author
     *
     * @param \stdClass $author
     *
     * @return Table
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return \stdClass
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     *
     * @param TableNode $tNode
     */
    public function addTNode($tNode)
    {
        $this->tNodes[] = $tNode;

        //$node->addTable($this);

        return $this;
    }

    public function removeTNode($tNode)
    {
        $this->tNodes->removeElement($tNode);
    }

    /**
     * Get tNodes
     *
     * @return array
     */
    public function getTNodes()
    {
        return $this->tNodes;
    }
}

