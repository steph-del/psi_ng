<?php
// src/eveg/PsiBundle/Controller/PsiRestController.php

namespace ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Put;
use AppBundle\Entity\Node;
use AppBundle\Entity\Table_;
use AppBundle\Entity\TableNode;
use AppBundle\Entity\Validation;
use AppBundle\Entity\FlatNode;

class NodeRestController extends FOSRestController
{

	/**
	 * GET Route annotation
	 * Get all nodes
	 * @Get("/nodes")
	 */
	public function getNodesAction()
	{
		// New view
		$view = $this->view();

		// Grabbing Entity manager & repository
		$em		  = $this->getDoctrine()->getManager('psi_db');
		$nodeRepo = $em->getRepository('AppBundle:Node');

		$nodes    = $nodeRepo->getAllNotIdiotaxons();

		// Return
		$view->setData($nodes);
		return $this->handleView($view);
	}

	/**
	 * GET Route annotation.
	 * Get single node
	 * @Get("/nodes/{id}", requirements={"id" = "\d+"})
	 */
	public function getNodeIdAction($id)
	{
		// New view
		$view = $this->view();
		
		$serializer = $this->container->get('jms_serializer');

		// Grabbing Entity manager & repository
		$em		  = $this->getDoctrine()->getManager('psi_db');
		$nodeRepo = $em->getRepository('AppBundle:Node');

		// Find node by id
		$node 	  = $nodeRepo->findById($id);

		// Return
		$view->setData($node);
		return $this->handleView($view);

	}

	/**
	 * Get Route annotation
	 * Get nodes by ids
	 * @Get("/nodes/{ids}", requirements={"ids"= "\d+(?:,\d+)+"})
	 */
	public function getNodesByIdsAction($ids)
	{
		// New view
		$view = $this->view();
		
		$serializer = $this->container->get('jms_serializer');

		// Grabbing Entity manager & repository
		$em		  = $this->getDoctrine()->getManager('psi_db');
		$nodeRepo = $em->getRepository('AppBundle:Node');

		// Find nodes by id
		$arrayOfIds = explode(",", $ids);
		$nodes 	  = $nodeRepo->findByIds($arrayOfIds);

		// Return
		$view->setData($nodes);
		return $this->handleView($view);
	}

	/**
	 * GET Route annotation.
	 * Get nodes by term
	 * @Get("/nodes/{term}", requirements={"term" = "\w+"})
	 */
	public function getNodesByTermAction($term)
	{
		// New view
		$view = $this->view();
		
		$serializer = $this->container->get('jms_serializer');

		// Grabbing Entity manager & repository
		$em		  = $this->getDoctrine()->getManager('psi_db');
		$nodeRepo = $em->getRepository('AppBundle:Node');

		// Find node by term
		$term = str_replace(' ', '%', $term);
		$nodes 	  = $nodeRepo->findByTerm($term);

		// Return
		$view->setData($nodes);
		return $this->handleView($view);

	}

	/**
	 * PUT Route annotation
	 * Create a new node
	 * @Put("/nodes")
	 */
	public function putNodeAction(Request $request)
	{
		/*
		 * About the requested data :
		 * $request contains serialized data from front app :
		 * eg : {"frontId":1498505893522,"nodes":[{"frontId":1498505893522,"name":"lolium perenne","coef":"1"},{"frontId":1498505900103,"name":"agrostis stolonifera","coef":"1"}]}
		 * this example is a simple node (only described by its frontId) with 2 child nodes
		 * Data from the front app should always have this pattern : {rootNode,"nodes":[...]}
		 */

		$em		  = $this->getDoctrine()->getManager('psi_db');
		$nodeRepo = $em->getRepository('AppBundle:Node');

		$jsonData = $request->get('data');
		$data = json_decode($request->getContent(), true);

		// Create the root node and hydrate it
		$rootNode = new Node('synusy');
		$em->persist($rootNode);
		$rootNode->setRepository('baseveg');
		$rootNode->setName('A synusy from my Angular app!');
		$rootNode->setFrontId($data['frontId']);
		$rootNode->setGeoJson($data['geoJson']);

		$validation = new Validation;
		$validation->setRepository('baseveg');
		$validation->setRepositoryIdTaxo($data['validation']['validatedSyntaxonIdTaxo']);
		$validation->setRepositoryIdNomen($data['validation']['validatedSyntaxonIdNomen']);
		$validation->setInputName($data['validation']['validatedSyntaxonInputName']);
		$validation->setValidatedName($data['validation']['validatedSyntaxonValidatedName']);
		$rootNode->addValidation($validation);

		// flatNode attributes
		$geoJson = $data['geoJson'];
		$flatLocation = $geoJson["features"][0]["geometry"];
		$flatValidation = '';
		$flatChildrenValidation = '';

		// flatNode validation
		$flatValidation = $validation->getRepository().'-'.$validation->getRepositoryIdTaxo();

		// Iterate through nodes ; create children nodes and hydrate them
		foreach ($data['nodes'] as $key => $node) {
			${'node'.$key} = new Node('idiotaxon');
			${'validation'.$key} = new Validation;
			
			$rootNode->addChild(${'node'.$key});

			${'node'.$key}->setFrontId($node['frontId']);
			${'node'.$key}->setName($node['name']);
			${'node'.$key}->setCoef($node['coef']);
			${'node'.$key}->setRepository('baseflor');
			${'node'.$key}->setGeoJson($data['geoJson']);

			${'validation'.$key}->setRepository('baseflor');
			${'validation'.$key}->setRepositoryIdTaxo($node['repositoryIdTaxo']);
			${'validation'.$key}->setRepositoryIdNomen($node['repositoryIdNomen']);
			${'validation'.$key}->setInputName($node['inputName']);
			${'validation'.$key}->setValidatedName($node['validatedName']);

			${'node'.$key}->addValidation(${'validation'.$key});

			${'node'.$key}->setParent($rootNode);
			${'validation'.$key}->setParentNode($rootNode);

			$em->persist(${'node'.$key});
			$em->persist(${'validation'.$key});

			// flatNode childrenValidation
			$flatChildrenValidation .= ${'validation'.$key}->getRepository().'-'.${'validation'.$key}->getRepositoryIdTaxo().' ';

		}

		// Flush
		$em->flush();
		$em->clear();

		// Create the FlatNode item
		$flatNode = new FlatNode();
		$em->persist($flatNode);
		$flatNode->setNode($rootNode);
		$flatNode->setNodeId($rootNode->getId());
		$flatNode->setLocation($flatLocation);
		$flatNode->setValidation($flatValidation);
		$flatNode->setChildrenValidation($flatChildrenValidation);

		// 2nd flush & clear the EM
		$em->flush();
		$em->clear();

		// New view
		$view = $this->view();
		$view->setData($rootNode);

		return $this->handleView($view);
	}

}