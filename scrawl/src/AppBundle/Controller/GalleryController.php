<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Config\Definition\Exception\Exception;
use AppBundle\Entity\Photo;

/**
 * Gallery controller
 * Logic for selecting which set of photos to render in
 * response to a query for photos
 */
class GalleryController extends Controller
{

	//create a JSON response to ajaxly return all photo
    //filepaths so that we can render photos with ng-repeat
	public function getPhotoPathsAction()
	{
		$paths = array();

		$sql = 'SELECT * FROM scrawl_photos';

		$stmt = $this->getDoctrine()->getManager()
		->getConnection()->prepare($sql);

        //execute query
		$stmt->execute();

        //get all rows of results 
		$entities = $stmt->fetchAll();

		foreach ($entities as $entity) {
        	//append 'uploads/' to the beginning of the path
        	//so that it can be referenced directly in an html
        	//img directive to render the image
			$paths[$entity['path']] = 'uploads/'.$entity['path'];
		}
		return new JsonResponse($paths);

	}

    //return all latlons to display map markers
	public function getLatLonsAction()
	{
		$geos = array();

		$sql = 'SELECT * FROM scrawl_photos';

		$stmt = $this->getDoctrine()->getManager()
		->getConnection()->prepare($sql);

        //execute query
		$stmt->execute();

        //get all rows of results 
		$entities = $stmt->fetchAll();

		foreach ($entities as $entity) {
			$geos[$entity['path']] = [$entity['latitude'], $entity['longitude']];
		}
		return new JsonResponse($geos);

	}

    //consumes a photo id and produces a JSON representation of the Photo object
	public function getArtAction($id)
	{
		$sql = 'SELECT * FROM scrawl_photos WHERE path=:path';

		$stmt = $this->getDoctrine()->getManager()
		->getConnection()->prepare($sql);

		$stmt->bindValue('path', $id);

        //execute query
		$stmt->execute();

        //get all rows of results 
		$photo = $stmt->fetch();

		$photoInfo = array(
			"device" => $photo['device'],
			"uploadDate" => $photo['uploadDate'],
			"latitude" => $photo['latitude'],
			"longitude" => $photo['longitude'],
			"path" => 'uploads/'.$photo['path']
			);

		return new JsonResponse($photoInfo);
	}
}