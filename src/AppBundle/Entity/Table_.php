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
     * @var string
     *
     * @ORM\Column(name="summary", type="string", length=512, nullable=true)
     *
     * @Expose
     */
    private $summary;

    /**
     * @var \stdClass
     *
     * @ORM\Column(name="author", type="object", nullable=true)
     *
     * @Expose
     */
    private $author;

    /**
     * @var string
     *
     * @ORM\Column(name="createdBy", type="string", length=256, nullable=false)
     *
     * @Expose
     * @SerializedName("createdBy")
     */
    private $createdBy;

    /**
     * @ORM\Column(name="createdAt", type="datetime")
     *
     * @var \DateTime
     *
     * @Expose
     * @SerializedName("createdAt")
     */
    private $createdAt;

    /**
     * @var string
     *
     * @ORM\Column(name="enteredBy", type="string", length=256, nullable=false)
     *
     * @Expose
     * @SerializedName("enteredBy")
     */
    private $enteredBy;

    /**
     * @ORM\Column(name="enteredAt", type="datetime")
     *
     * @var \DateTime
     *
     * @Expose
     * @SerializedName("enteredAt")
     */
    private $enteredAt;

    /**
     * @var string
     *
     * @ORM\Column(name="lastUpdateBy", type="string", length=256, nullable=true)
     *
     * @Expose
     * @SerializedName("lastUpdateBy")
     */
    private $lastUpdateBy;

    /**
     * @ORM\Column(name="lastUpdateAt", type="datetime", nullable=true)
     *
     * @var \DateTime
     *
     * @Expose
     * @SerializedName("lastUpdateAt")
     */
    private $lastUpdateAt;

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
     * Set summary
     *
     * @param string $summary
     *
     * @return Table
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * Get summary
     *
     * @return string
     */
    public function getSummary()
    {
        return $this->summary;
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
     * Set createdBy
     *
     * @param string $createdBy
     *
     * @return Table
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return string
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Table
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set enteredAt
     *
     * @param \DateTime $enteredAt
     *
     * @return Table
     */
    public function setEnteredAt($enteredAt)
    {
        $this->enteredAt = $enteredAt;

        return $this;
    }

    /**
     * Get enteredAt
     *
     * @return \DateTime
     */
    public function getEnteredAt()
    {
        return $this->enteredAt;
    }

    /**
     * Set lastUpdateBy
     *
     * @param string $lastUpdateBy
     *
     * @return Table
     */
    public function setLastUpdateBy($lastUpdateBy)
    {
        $this->lastUpdateBy = $lastUpdateBy;

        return $this;
    }

    /**
     * Get lastUpdateBy
     *
     * @return string
     */
    public function getLastUpdateBy()
    {
        return $this->lastUpdateBy;
    }

    /**
     * Set lastUpdateAt
     *
     * @param \DateTime $lastUpdateAt
     *
     * @return Table
     */
    public function setLastUpdateAt($lastUpdateAt)
    {
        $this->lastUpdateAt = $lastUpdateAt;

        return $this;
    }

    /**
     * Get lastUpdateAt
     *
     * @return \DateTime
     */
    public function getLastUpdateAt()
    {
        return $this->lastUpdateAt;
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

