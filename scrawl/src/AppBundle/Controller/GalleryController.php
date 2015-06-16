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
 *
 * note that ALL methods are called AJAXly from the anjular controller photo.js
 *
 * in ALL cases, the response is a hash in which they key is the name of the photo file
 * and the value is the path relative to the web directory (i.e., uploads/username_filename.filetye)
 * so that the value can be passed directly to an HTML img directive in the view to render the photo
 */
class GalleryController extends Controller
{

	//create a JSON response to ajaxly return all photo
    //filepaths so that we can render photos with ng-repeat
	
	public function getPhotoPathsAction()
	{
		if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) 
		{
			return $this->loggedInGalleryAction();
		}
		return $this->anonymousGalleryAction();
	}

	public function loggedInGalleryAction()
	{
		$paths = array();

		$sql = 'SELECT path From has_tag Where tagName IN (SELECT tagName From has_viewed WHERE username=:username ORDER BY count)';

		$stmt = $this->getDoctrine()->getManager()
		->getConnection()->prepare($sql);

		$username = $this->get('security.token_storage')->getToken()->getUser()->getId();

		$stmt->bindValue('username', $username);

        //execute query
		$stmt->execute();

        //get all rows of results 
		$entities = $stmt->fetchAll();

		foreach ($entities as $entity) {
			$paths[$entity['path']] = 'uploads/'.$entity['path'];
		}

		return new JsonResponse($entities);
	}

	public function anonymousGalleryAction()
	{
		$paths = array();

		$sql = 'SELECT sp.path, MAX(sp.viewCount) AS count 
		FROM scrawl_photos sp, scrawl_locations1 l1, scrawl_locations2 l2 
		WHERE sp.latitude = l2.latitude AND sp.longitude = l2.longitude 
		AND l2.postalCode = l1.postalCode GROUP BY l1.city
		ORDER BY COUNT DESC';

		$stmt = $this->getDoctrine()->getManager()
		->getConnection()->prepare($sql);

        //execute query
		$stmt->execute();

        //get all rows of results 
		$entities = $stmt->fetchAll();

		foreach ($entities as $entity) {
			$paths[$entity['path']] = 'uploads/'.$entity['path'];
		}

		return new JsonResponse($entities);
	}

	//consume a username and return a hash of photo paths
	//that represent all photos uploaded by that user
	public function getUserPhotosAction($username)
	{
		$paths = array();

		$sql = 'SELECT * FROM uploaded_by WHERE username=:username';

		$stmt = $this->getDoctrine()->getManager()
		->getConnection()->prepare($sql);

		$stmt->bindValue('username', $username);

        //execute query
		$stmt->execute();

        //get all rows of results 
		$entities = $stmt->fetchAll();

		foreach ($entities as $entity) {
			$paths[$entity['path']] = 'uploads/'.$entity['path'];
		}

		return new JsonResponse($paths);
	}

	//simply gets all photo paths
	public function defaultGetPhotoPathsAction()
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