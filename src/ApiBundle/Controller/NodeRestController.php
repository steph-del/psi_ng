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
use ApiBundle\Services\NodeService;
use AppBundle\Entity\NodeMeta;

class NodeRestController extends FOSRestController
{
	private $nodeService;

    public function __construct(NodeService $nodeService)
    {
        $this->nodeService = $nodeService;
    }

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
		$level = $data['level'];
		$rootNode = new Node($level);
		$em->persist($rootNode);

		$meta = null;
		if(isset($data['meta'])) {
			$meta = $data['meta'];

			foreach ($meta as $k => $m) {
				${'nodeMeta'.$k} = new NodeMeta();
				${'nodeMeta'.$k}->setName($m['name']);
				${'nodeMeta'.$k}->setValue($m['value']);
				$rootNode->addMeta(${'nodeMeta'.$k});
			}
		}

		$rootNode->setRepository('baseveg');
		$rootNode->setName('A synusy from my Angular app!');
		$rootNode->setLevel($level);
		//$rootNode->setFrontId($data['frontId']);
		$rootNode->setGeoJson($data['geoJson']);
		$createdAt = new \Datetime('now');//\DateTime::createFromFormat('Y/m/d', $data['createdAt']);
		$rootNode->setCreatedBy($data['createdBy']);
		$rootNode->setCreatedAt($createdAt);
		$rootNode->setEnteredBy($data['enteredBy']);
		$rootNode->setEnteredAt(new \Datetime('now'));

		if(isset($data['validation'])) {
			$validation = new Validation;
			$validation->setRepository('baseveg');
			$validation->setRepositoryIdTaxo($data['validation']['citedSyntaxonIdTaxo']);
			$validation->setRepositoryIdNomen($data['validation']['citedSyntaxonIdNomen']);
			$validation->setInputName($data['validation']['citedSyntaxonInputName']);
			$validation->setValidatedName($data['validation']['citedSyntaxonValidatedName']);
			$rootNode->addValidation($validation);
		}

		// Iterate through layers
		$layers = ($data['layerNodes']);
		$layersCount = count($layers);
		
		if($level === 'synusy' && $layersCount > 1) {
			// A synusy with several layers (not allowed)
			throw new HttpException(500, "Une synusie ne peut avoir plusieurs strates");
		}

		// Attach (and persist) children nodes from layers form data
		$rootNode = $this->nodeService->attachChildrenNodes($rootNode, $layers, $em);

		// Create (and persist) FlatNode
		$flatNode = $this->nodeService->createFlatData($rootNode, $em);

		// Flush & clear the EM
		$em->flush();
		$em->clear();

		// New view
		$view = $this->view();
		$view->setData($rootNode);

		return $this->handleView($view);
	}

}