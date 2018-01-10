<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Gedmo\Mapping\Annotation as Gedmo;
use AppBundle\Entity\Validation;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Accessor;


/*
 * Why using SerializedName ?
 * See https://stackoverflow.com/questions/22738466/symfony2-jmsserializerbundle-changes-the-attribute-name-from-classname-to-cl
 */

/**
 * Node
 *
 * @author Stéphane Delplanque <stephane@phytoscopa.fr>
 *
 * @ORM\Table(name="psi_node")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\NodeRepository")
 * @Gedmo\Tree(type="nested")
 *
 * @ExclusionPolicy("all")
 */
class Node
{
    CONST IDIOTAXON_NAME = 'idiotaxon';
    CONST SYNUSY_NAME    = 'synusy';
    CONST MICROC_NAME    = 'microcenosis';
    CONST PHYTOC_NAME    = 'phytocenosis';

    CONST IDIOTAXON      = ['name' => self::IDIOTAXON_NAME,
                            'canContain' => array(null)];
    CONST SYNUSY         = ['name' => self::SYNUSY_NAME,
                            'canContain' => array(self::IDIOTAXON_NAME)];
    CONST MICROC         = ['name' => self::MICROC_NAME,
                            'canContain' => array(self::SYNUSY_NAME)];
    CONST PHYTOC         = ['name' => self::PHYTOC_NAME,
                            'canContain' => array(self::SYNUSY_NAME, self::MICROC_NAME)];

    CONST LEVELS         = [self:: IDIOTAXON, self::SYNUSY, self::MICROC, self::PHYTOC];

    public function __construct($level)
    {
        $this->level        = $level;
        $this->children     = new ArrayCollection();
        $this->validation   = new ArrayCollection();
        $this->parents      = new ArrayCollection();
        $this->tables       = new ArrayCollection();
        $this->meta         = new ArrayCollection();

        $this->canContainSetter($level);
    }

    private function canContainSetter($level)
    {
        foreach (self::LEVELS as $keyConstLevel => $constLevel) {
            if($level === $constLevel['name']) {
                $this->canContain = $constLevel['canContain'];
                break;
            } else {
                if($keyConstLevel == count(self::LEVELS) -1) {
                    throw new HttpException(500, "The level '".$level."' does not exist");
                };
            }
        }
    }

    /**
     * Id
     *
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Expose
     */
    private $id;

    /**
     * frontId
     *
     * When we send a new node through the API, we don't even know its id because Doctrine generates it. This id is used in 
     * the front app to know wich node has to be updated after the db persistance.
     *
     * @var bigint
     * @ORM\Column(name="frontId", type="bigint", options={"unsigned"=true}, nullable=true)
     *
     * @Expose
     * @SerializedName("frontId")
     */
    private $frontId;

    /**
     * Level : node level
     *
     * @var string
     * @ORM\Column(name="level", type="string", length=128)
     *
     * @Expose
     */
    private $level;

    /**
     * Layer : node layer
     *
     * @var string
     * @ORM\Column(name="layer", type="string", length=2, nullable=true)
     *
     * @Expose
     */
     
    private $layer;

    /**
     * CanContain : which levels can this node contain ?
     * Not persisted id Db
     *
     * @var array
     *
     * @Expose
     * @SerializedName("canContain")
     */
    private $canContain;

    /**
     * Repository : the name of the repository (référentiel)
     *
     * @var string
     * @ORM\Column(name="repository", type="string", length=128, nullable=true)
     *
     * @Expose
     */
    private $repository;

    /**
     * RepositoryIdNomen : nomenclatural id of the element inside the repository
     *
     * @var integer
     * @ORM\Column(name="repositoryIdNomen", type="integer", nullable=true)
     *
     * @Expose
     * @SerializedName("repositoryIdNomen")
     */
    private $repositoryIdNomen;

    /**
     * Name : name of the element
     * 
     * @var string
     * @ORM\Column(name="name", type="string", length=512, nullable=true)
     *
     * @Expose
     */
    private $name;

    /**
     * Coef : ab/dom value
     * 
     * @var string
     * @ORM\Column(name="coef", type="string", length=6, nullable=true)
     *
     * @Expose
     */
    private $coef;

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
     * @ORM\Column(name="createdAt", type="date")
     *
     * @var \Date
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
     * Coef : ab/dom value
     * 
     * @var string
     * @ORM\Column(name="geoJson", type="json_array", nullable=true)
     *
     * @Expose
     * @SerializedName("geoJson")
     */
    private $geoJson;

    private $localization;
        private $country;           // TODO : include inside localization
        private $departement;       // TODO : include inside localization
        private $city;          // TODO : include inside localization

    private $biblio;
    private $isDiagnosis;

    /**
     *
     * @ORM\OneToMany(targetEntity="NodeMeta", mappedBy="node", cascade={"persist"})
     *
     * @Expose
     * @Type("ArrayCollection<AppBundle\Entity\NodeMeta>")
     */
    private $meta;

    /**
     * @var
     *
     * @ORM\ManyToMany(targetEntity="Node", inversedBy="parents", cascade={"persist"})
     * @ORM\JoinTable(name="psi_nodes_relations",
     *         joinColumns={@ORM\JoinColumn(name="node_id", referencedColumnName="id")},
     *         inverseJoinColumns={@ORM\JoinColumn(name="related_node_id", referencedColumnName="id")}
     * )
     *
     * @Expose
     * @Type("ArrayCollection<AppBundle\Entity\Node>")
     * @Accessor(getter="getChildren", setter="addChildren")
     */
    private $children;

    /**
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Validation", mappedBy="node", cascade={"persist"})
     *
     * @Expose
     * @Type("ArrayCollection<AppBundle\Entity\Validation>")
     * @Accessor(getter="getValidations", setter="addValidations")
     */
    private $validations;

    /*
     * @ORM\ManyToMany(targetEntity="Node", mappedBy="children")
     */
    //private $parents;

    /*
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\TableNode", mappedBy="node", cascade={"persist"})
    */
    //private $tables;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(type="integer")
     */
    private $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(type="integer")
     */
    private $lvl;
    
    /**
     * @Gedmo\TreeRight
     * @ORM\Column(type="integer")
     */
    private $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\ManyToOne(targetEntity="Node")
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    private $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Node", inversedBy="childrenTree", cascade={"persist"})
     * @ORM\JoinColumn(referencedColumnName="id")
     * @SerializedName("parentTree")
     */
    private $parentTree;

    /**
     * @ORM\OneToMany(targetEntity="Node", mappedBy="parentTree")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    private $childrenTree;

    public function getId() {
        return $this->id;
    }

    public function setFrontId($frontId) {
        $this->frontId = $frontId;
    }

    public function getFrontId() {
        return $this->frontId;
    }

    public function setLevel($level) { $this->level = $level; }
    public function getLevel() { return $this->level; }

    public function setLayer($layer) { $this->layer = $layer; }
    public function getLayer() { return $this->layer; }

    public function setName($name) { $this->name = $name; return $this->name; }
    public function getName() { return $this->name; }

    public function setCanContain($canContain) { $this->canContain = $canContain; return $this->canContain; }
    public function canContain() { return $this->canContain; }

    public function addMeta(NodeMeta $meta) { $this->meta[] = $meta; $meta->setNode($this); }
    public function removeMeta($meta) { unset($this->meta[$meta]); }
    public function getMeta() { return $this->meta; }

    public function addChild($childNode)
    {
        if($childNode instanceof Node)
        {
            // canContain can be empty if a Node is initialized without its constructor
            // It happens when JMS serializer deserialize a Node, so we have to add canContain manually
            if(empty($this->canContain)) {
                $this->canContainSetter($this->level);
            }
            if(in_array($childNode->getLevel(), $this->canContain)) {
                $this->children[] = $childNode;
                $childNode->setParent($this);
            } else {
                echo('A(n)'.$this->getLevel().' entity type can\'t contain a(n) '.$childNode->getLevel().' entity type'."\n");die;
            }
        } else {
            echo('Child must be an instance of Node');die;
        }
    }

    public function addChildren($children)
    {
        if($children instanceof ArrayCollection)
        {
            foreach ($children as $key => $child) {
                $this->addChild($child);
            }
        } else {
            echo('Children must be an instance of ArrayCollection');die;
        }
    }

    public function removeChild($childNode)
    {
        if($childNode instanceof Node)
        {
            if(in_array($childNode, $this->children))
            {
                unset($this->children[$keyToRemove]);
            } else {
                echo('childNode not founded');
            }
        }
    }

    public function getChildren() { return $this->children; }

    public function addValidation($validation)
    {
        if($validation instanceof Validation)
        {
            $validation->setNode($this);
            $this->validations[] = $validation;
        } else {
            throw new HttpException(500, "Error Processing Request. $validation is not of type Validation (in Node.php)");
        }
    }
    public function addValidations($validations)
    {
        if($validations instanceof ArrayCollection)
        {
            foreach ($validations as $key => $validation) {
                $this->addValidation($validation);
            }
        } else {
            throw new HttpException(500, "Error Processing Request. $validations is not of type ArrayCollection (in Node.php)");
        }
    }
    public function removeValidation($validation)
    {
        if($validation instanceof Validation)
        {
            unset($this->validations[$validation]);
        }
    }
    public function getValidations() { return $this->validations; }

    public function setCoef($coef) { $this->coef = $coef; return $this->coef; }
    public function getCoef() { return $this->coef; }

    public function setCreatedBy($createdBy) { $this->createdBy = $createdBy; return $this->createdBy; }
    public function getCreatedBy() { return $this->createdBy; }

    public function setCreatedAt($createdAt) { $this->createdAt = $createdAt; return $this->createdAt; }
    public function getCreatedAt() { return $this->createdAt; }

    public function setEnteredBy($enteredBy) { $this->enteredBy = $enteredBy; return $this->enteredBy; }
    public function getEnteredBy() { return $this->enteredBy; }

    public function setEnteredAt($enteredAt) { $this->enteredAt = $enteredAt; return $this->enteredAt; }
    public function getEnteredAt() { return $this->enteredAt; }

    public function setRepository($repo) { $this->repository = $repo; return $this->repository; }
    public function getRepository() { return $this->repository; }

    public function setRepositoryIdNomen($idNomen) { $this->repositoryIdNomen = $idNomen; return $this->repositoryIdNomen; }
    public function getRepositoryIdNomen() { return $this->repositoryIdNomen;}

    //public function addTable($table) { $this->tables[] = $table; }
    //public function removeTable($table) { $this->tables->removeElement($table); }
    //public function getTables() { return $this->tables; }

    public function setParent(Node $parent = null) { $this->parentTree = $parent; }
    public function getParent() { return $this->parentTree;  }
    public function getRoot() { return $this->root; }

    public function setGeoJson($geoJson) { $this->geoJson = $geoJson; }
    public function getGeoJson() { return $this->geoJson; }

    /**
     * Set lft
     *
     * @param integer $lft
     *
     * @return Node
     */
    public function setLft($lft)
    {
        $this->lft = $lft;

        return $this;
    }

    /**
     * Get lft
     *
     * @return integer
     */
    public function getLft()
    {
        return $this->lft;
    }

    /**
     * Set lvl
     *
     * @param integer $lvl
     *
     * @return Node
     */
    public function setLvl($lvl)
    {
        $this->lvl = $lvl;

        return $this;
    }

    /**
     * Get lvl
     *
     * @return integer
     */
    public function getLvl()
    {
        return $this->lvl;
    }

    /**
     * Set rgt
     *
     * @param integer $rgt
     *
     * @return Node
     */
    public function setRgt($rgt)
    {
        $this->rgt = $rgt;

        return $this;
    }

    /**
     * Get rgt
     *
     * @return integer
     */
    public function getRgt()
    {
        return $this->rgt;
    }

    /**
     * Set root
     *
     * @param \AppBundle\Entity\Node $root
     *
     * @return Node
     */
    public function setRoot(\AppBundle\Entity\Node $root = null)
    {
        $this->root = $root;

        return $this;
    }

    /**
     * Set parentTree
     *
     * @param \AppBundle\Entity\Node $parentTree
     *
     * @return Node
     */
    public function setParentTree(\AppBundle\Entity\Node $parentTree = null)
    {
        $this->parentTree = $parentTree;

        return $this;
    }

    /**
     * Get parentTree
     *
     * @return \AppBundle\Entity\Node
     */
    public function getParentTree()
    {
        return $this->parentTree;
    }

    /**
     * Add childrenTree
     *
     * @param \AppBundle\Entity\Node $childrenTree
     *
     * @return Node
     */
    public function addChildrenTree(\AppBundle\Entity\Node $childrenTree)
    {
        $this->childrenTree[] = $childrenTree;

        return $this;
    }

    /**
     * Remove childrenTree
     *
     * @param \AppBundle\Entity\Node $childrenTree
     */
    public function removeChildrenTree(\AppBundle\Entity\Node $childrenTree)
    {
        $this->childrenTree->removeElement($childrenTree);
    }

    /**
     * Get childrenTree
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildrenTree()
    {
        return $this->childrenTree;
    }
}
