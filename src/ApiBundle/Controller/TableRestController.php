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
use AppBundle\Entity\Table_ as Table;
use AppBundle\Entity\TableNode;
use AppBundle\Entity\Validation;
use AppBundle\Form\Table_Type as TableType;

class TableRestController extends FOSRestController
{

	/**
	 * Get Route annotation
	 * Get Tables
	 * @Get("/tables")
	 */
	public function getTablesAction()
	{
		// New view
		$view = $this->view();

		// Grabbing Entity manager & repository
		$em	   	   = $this->getDoctrine()->getManager('psi_db');
		$tableRepo = $em->getRepository('AppBundle:Table_');

		$tables    = $tableRepo->findAll();

		// Return
		$view->setData($tables);
		return $this->handleView($view);
	}


	/**
	 * GET Route annotation
	 * Get new empty table
	 * @Get("/tables/new")
	 */
	public function getNewTableAction()
	{
		// New view
		$view = $this->view();
		$newTable = new Table();

		// Return
		$view->setData($newTable);
		return $this->handleView($view);
	}

	/**
	 * PUT Route annotation
	 * Create a new Table
	 * @Put("/tables")
	 */
	public function putTableAction(Request $request)
	{
		$em		  	= $this->getDoctrine()->getManager('psi_db');
		$nodeRepo 	= $em->getRepository('AppBundle:Node');
		$serializer = $this->container->get('jms_serializer');
		//$dataJson 	= $request->query->get('table');
		$dataJson	= $request->getContent();
		$table 		= $serializer->deserialize($dataJson, Table::class, 'json');

		$table->setCreatedAt(new \DateTime('now'));
		$table->setEnteredAt(new \DateTime('now'));
		$table->setLastUpdateBy('Stéphane');
		$table->setLastUpdateAt(new \DateTime('now'));

		$tns = $table->getTNodes();
		foreach ($tns as $tn) {
			$tn->setTable($table);
			$nodeId = $tn->getNode()->getId();
			$dbNode = $nodeRepo->find($nodeId);
			$tn->setNode($dbNode);
		}

		$em->persist($table);
		$em->flush();

		$view = $this->view();
		$view->setData($table);
		return $this->handleView($view);
	}
}