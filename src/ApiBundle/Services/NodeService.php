<?php
// src/ApiBundle/Services/NodeService.php

namespace ApiBundle\Services;

use Symfony\Component\HttpKernel\Exception\HttpException;
use AppBundle\Entity\Node;
use AppBundle\Entity\Validation;
use AppBundle\Entity\FlatNode;

class NodeService {

	public function attachChildrenNodes(Node $rootNode, $layerNodes, $em) {
		
		$level = $rootNode->getLevel();

		switch ($level) {
			case 'synusy':
				$layer = $layerNodes[0]['layer'];
				$nodes = $layerNodes[0]['nodes'];
				$rootNode->setLayer($layer);
				// Create idiotaxonNodes
				$rootNode = $this->attachIdiotaxonNodesToSynusyNode($rootNode, $nodes, $em);
				break;

			case 'microcenosis':
				$rootNode = $this->attachSynusyNodesToMicroCNode($rootNode, $layerNodes, $em);
				break;
			
			default:
				throw new HttpException(500, "Le niveau d'intégration '".$level."' n'est pas reconnu");
				break;
		}

		return $rootNode;

	}

	/**
	 * Create a synusy
	 */
	private function attachSynusyNodesToMicroCNode(Node $rootNode, $layerNodes, $em) {
		// Create the synusies
		foreach ($layerNodes as $key => $layerNode) {
			${'node'.$key} = new Node('synusy');
			// new validation needed ?
			$em->persist(${'node'.$key});
			// persist validation ?

			$rootNode->addChild(${'node'.$key});
			${'node'.$key}->setParent($rootNode);
			//${'node'.$key}->setParentNode($rootNode);

			${'node'.$key}->setLevel('synusy');
			${'node'.$key}->setLayer($layerNode['layer']);

			${'node'.$key} = $this->attachRootNodeMetaToChildNode($rootNode, ${'node'.$key});

			// Attach the idiotaxons for the new synusy
			${'node'.$key} = $this->attachIdiotaxonNodesToSynusyNode(${'node'.$key}, $layerNode['nodes'], $em);

		}

		return $rootNode;
	}

	/**
	 * Populate a given synusy with several idiotaxons, attach them to rootNode and return rootNode
	 *
	 * @param \Node     			$rootNode  	The synusy node
     * @param [\Node] 				$previous 	The idiotaxons nodes
     * @param \EntityManager       	$em     	The parent's entity manager
     *
     * @return \Node rootNode
	 */
	private function attachIdiotaxonNodesToSynusyNode(Node $rootNode, array $idiotaxonNodes, $em) {
		// Iterate through idiotaxons nodes ; create children nodes and hydrate them
		foreach ($idiotaxonNodes as $key => $node) {
			${'node'.$key} = new Node('idiotaxon');
			${'validation'.$key} = new Validation;

			$em->persist(${'validation'.$key});
			$em->persist(${'node'.$key});
			
			$rootNode->addChild(${'node'.$key});
			${'node'.$key}->setParent($rootNode);
			//${'node'.$key}->setParentNode($rootNode);

			${'node'.$key}->setFrontId($node['frontId']);
			${'node'.$key}->setName($node['name']);
			${'node'.$key}->setCoef($node['coef']);
			${'node'.$key}->setLayer($rootNode->getLayer());
			${'node'.$key}->setRepository('baseflor');

			${'validation'.$key}->setRepository('baseflor');
			${'validation'.$key}->setRepositoryIdTaxo($node['repositoryIdTaxo']);
			${'validation'.$key}->setRepositoryIdNomen($node['repositoryIdNomen']);
			${'validation'.$key}->setInputName($node['inputName']);
			${'validation'.$key}->setValidatedName($node['validatedName']);

			${'node'.$key} = $this->attachRootNodeMetaToChildNode($rootNode, ${'node'.$key});

			${'node'.$key}->addValidation(${'validation'.$key});

			${'validation'.$key}->setParentNode($rootNode);

			$em->persist(${'node'.$key});
			$em->persist(${'validation'.$key});
		}

		return $rootNode;
	}

	/**
	 * Some metadata have to be duplicated from a root node to its children nodes
	 */
	private function attachRootNodeMetaToChildNode(Node $rootNode, Node $childNode) {
		
		$childNode->setGeoJson($rootNode->getGeoJson());
		$childNode->setCreatedBy($rootNode->getCreatedBy());
		$childNode->setCreatedAt($rootNode->getCreatedAt());
		$childNode->setEnteredBy($rootNode->getEnteredBy());
		$childNode->setEnteredAt($rootNode->getEnteredAt());

		return $childNode;
	}

	public function createFlatData(Node $node, $em) {		
		// Location
		$geoJson = $node->getGeoJson();
		$flatLocation = $geoJson["features"][0]["geometry"];
		$flatTypeLocation = $geoJson["features"][0]["geometry"]["type"];
		$flatLngLatLocation = $geoJson["features"][0]["geometry"]["coordinates"]; // Long Lat coordinates
		$flatLatLngLocation = array();		// Lat Long coordinates, needed for elasticsearch
		if($flatTypeLocation == 'Point') {
			array_push($flatLatLngLocation, $flatLngLatLocation[1]);
			array_push($flatLatLngLocation, $flatLngLatLocation[0]);
		}
		$flatLocation['coordinates'] = $flatLatLngLocation;

		// Validation
		$nodeValidations = $node->getValidations();
		$flatValidation = '';
		$flatChildrenValidation = '';
		
		if($nodeValidations) {
			foreach ($nodeValidations as $key => $validation) {
				$flatValidation .= $validation->getRepository().'-'.$validation->getRepositoryIdTaxo();
				if($key != count($nodeValidations)-1) $flatValidation.=' ';
			}
		}

		// Children validation
		$level = $node->getLevel();
		switch ($level) {
			case 'synusy':
				$flatChildrenValidation .= $this->amendFlatValidation($node, $flatChildrenValidation);
				break;

			case 'microcenosis':
				//$flatChildrenValidation .= $this->amendFlatValidation($node, $flatChildrenValidation);

				$childNodes = $node->getChildren();	// $childNodes is an array of synusy nodes
				foreach ($childNodes as $keyCN => $childNode) {
					$flatChildrenValidation .= $this->amendFlatValidation($childNode);
				}
				break;
		}

		// Merge flat things into a FlatNode
		$flatNode = new FlatNode();
		$em->persist($flatNode);
		$flatNode->setNodeId($node->getId());
		$flatNode->setNode($node);
		$flatNode->setLocation($flatLocation);
		$flatNode->setValidation($flatValidation);
		$flatNode->setChildrenValidation($flatChildrenValidation);

		return $flatNode;
	}

	private function amendFlatValidation($node, $flatChildrenValidation = '') {
		$childNodes = $node->getChildren();
		foreach ($childNodes as $keyCN => $childNode) {
			$childValidations = $childNode->getValidations();
			if(!empty($childValidations)) {
				foreach ($childValidations as $keyV => $validation) {
					$flatChildrenValidation .= $validation->getRepository().'-'.$validation->getRepositoryIdTaxo();
					if($keyV != count($childValidations)-1) $flatChildrenValidation.=' ';
				}
			}
			if($keyCN != count($childNodes) && !empty($childValidations)) $flatChildrenValidation.=' ';
		}

		return $flatChildrenValidation;
	}

	/**
	 *
	 */
	public function dumpNode(Node $node, $trace = '') {
		$eol = "\n";

		// Root node informations
		$spaces = '';
		$trace .= $spaces.'- '.$node->getLevel().' ['.$node->getId().'] '.$node->getLayer().' : ';
		$validationsStr = '';

		$validations = $node->getValidations();
		if(empty($validations)) {
			$validationsStr = '';
		} else {
			foreach ($node->getValidations() as $key => $validation) {
				$validationsStr .= '['.$validation->getRepository().' '.$validation->getRepositoryIdTaxo().'] '.$validation->getValidatedName();
				if($key != count($validations)-1) $validations .= ' + ';
			}
		}
		$trace .= $validationsStr.$eol;

		// 1st child informations
		$nodeChildren = $node->getChildren();
		if(count($nodeChildren) > 0) {
			$spaces = '  ';
			foreach ($nodeChildren as $key => $node) {
				$validationsStr = '';
				$validations = $node->getValidations();
				$trace .= $spaces.'- '.$node->getLevel().' ['.$node->getId().'] '.$node->getLayer().' : ';
				if(empty($node->getValidations())) {
					$validationsStr = '';
				} else {
					foreach ($node->getValidations() as $key => $validation) {
						$validationsStr .= '['.$validation->getRepository().' '.$validation->getRepositoryIdTaxo().'] '.$validation->getValidatedName();
						if($key != count($validations)-1) $validationsStr .= ' + ';
					}
				}
				$trace .= $validationsStr.$eol;

				// 2nd child informations
				$nodeGrandchildren = $node->getChildren();
				if(count($nodeGrandchildren) > 0) {
					$spaces2 = '    ';
					foreach ($nodeGrandchildren as $key2 => $node2) {
						$validationsStr2 = '';
						$validations2 = $node2->getValidations();
						$trace .= $spaces2.'- '.$node2->getLevel().' ['.$node2->getId().'] '.$node2->getLayer().' : ';
						if(empty($node2->getValidations())) {
							$validationsStr2 = '';
						} else {
							foreach ($node2->getValidations() as $key => $validation2) {
								$validationsStr2 .= '['.$validation2->getRepository().' '.$validation2->getRepositoryIdTaxo().'] '.$validation2->getValidatedName();
								if($key != count($validations2)-1) $validationsStr2 .= ' + ';
							}
						}
						$trace .= $validationsStr2.$eol;
					}
				}
			}
		}

		$trace .= $eol;

		return $trace;
	}

}