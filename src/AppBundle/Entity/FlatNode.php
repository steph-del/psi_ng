<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FlatNode
 *
 * @ORM\Table(name="flat_node")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FlatNodeRepository")
 */
class FlatNode
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var
     *
     * @ORM\OneToOne(targetEntity="Node", cascade={"persist"})
     */
    private $node;

    /**
     * @var int
     *
     * @ORM\Column(name="nodeId", type="integer")
     */
    private $nodeId;

    /**
     * @var array
     *
     * @ORM\Column(name="location", type="json_array")
     */
    private $location;

    /**
     * @var string
     *
     * @ORM\Column(name="validation", type="text")
     */
    private $validation;

    /**
     * @var string
     *
     * @ORM\Column(name="childrenValidation", type="text")
     */
    private $childrenValidation;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set node
     *
     * @param AppBundle\Entity\Node $node
     *
     * @return FlatNode
     */
    public function setNode($node)
    {
        $this->node = $node;

        return $this;
    }

    /**
     * Get node
     *
     * @return AppBundle\Entity\Node $node
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * Set nodeId
     *
     * @param integer $id
     *
     * @return FlatNode
     */
    public function setNodeId($id)
    {
        $this->nodeId = $id;

        return $this;
    }

    /**
     * Get nodeId
     *
     * @return int
     */
    public function getNodeId()
    {
        return $this->nodeId;
    }


    /**
     * Set location
     *
     * @param array $location
     *
     * @return FlatNode
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return array
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set validation
     *
     * @param string $validation
     *
     * @return FlatNode
     */
    public function setValidation($validation)
    {
        $this->validation = $validation;

        return $this;
    }

    /**
     * Get validation
     *
     * @return string
     */
    public function getValidation()
    {
        return $this->validation;
    }

    /**
     * Set childrenValidation
     *
     * @param string $childrenValidation
     *
     * @return FlatNode
     */
    public function setChildrenValidation($childrenValidation)
    {
        $this->childrenValidation = $childrenValidation;

        return $this;
    }

    /**
     * Get childrenValidation
     *
     * @return string
     */
    public function getChildrenValidation()
    {
        return $this->childrenValidation;
    }
}

