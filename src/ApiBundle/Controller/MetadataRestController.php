<?php
// src/eveg/PsiBundle/Controller/MetadataRestController.php

namespace ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Metadata;

class MetadataRestController extends FOSRestController
{

	/**
	 * GET Route annotation
	 * Get all metadata
	 * @Get("/metadata")
	 */
	public function getMetadata() {

		// New view
		$view = $this->view();

		// Grabbing Entity manager & repository
		$em		  = $this->getDoctrine()->getManager('psi_db');
		$metaRepo = $em->getRepository('AppBundle:Metadata');

		$metadata  = $metaRepo->findAll();

		// Return
		$view->setData($metadata);
		return $this->handleView($view);

	}

}