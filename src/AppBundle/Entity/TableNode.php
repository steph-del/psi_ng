<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\SerializedName;

/**
 * TableNode
 *
 * @ORM\Table(name="psi_table_node")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TableNodeRepository")
 *
 * @ExclusionPolicy("all")
 */
class TableNode
{
    public function __construct($table = null, $node = null, $position = null)
    {
        if($table) $this->table = $table;
        if($node) $this->node = $node;
        if($position) $this->position = $position;
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
     * @var integer
     *
     * @ORM\Column(name="position", type="integer")
     *
     * @Expose
     */
    private $position;

    /**
     * @var string
     *
     * @ORM\Column(name="groupSocio", type="string", nullable=true)
     *
     * @Expose
     * @SerializedName("groupSocio")
     */
    private $groupSocio;

    /**
    * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Table_", inversedBy="tNodes", cascade={"persist"})
    * @ORM\JoinColumn(nullable=false)
    *
    * @Expose
    * @Type("AppBundle\Entity\Table_")
    */
    private $table;

    /**
    * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Node", inversedBy="tables", cascade={"persist", "merge"})
    * @ORM\JoinColumn(nullable=false)
    *
    * @Expose
    * @Type("AppBundle\Entity\Node")
    */
    private $node;

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
     *
     * @return TableNode
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
     * Set table
     *
     * @param \AppBundle\Entity\Table $table
     *
     * @return TableNode
     */
    public function setTable(\AppBundle\Entity\Table_ $table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Get table
     *
     * @return \AppBundle\Entity\Table
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Set node
     *
     * @param \AppBundle\Entity\Node $node
     *
     * @return TableNode
     */
    public function setNode(\AppBundle\Entity\Node $node)
    {
        $this->node = $node;

        return $this;
    }

    /**
     * Get node
     *
     * @return \AppBundle\Entity\Node
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * Set groupSocio
     *
     * @param string $groupSocio
     *
     * @return TableNode
     */
    public function setGroupSocio($groupSocio)
    {
        $this->groupSocio = $groupSocio;

        return $this;
    }

    /**
     * Get groupSocio
     *
     * @return string
     */
    public function getGroupSocio()
    {
        return $this->groupSocio;
    }
}

