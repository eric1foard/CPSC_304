<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Photo;
use AppBundle\Form\PhotoType;
use AppBundle\Entity\Geolocation;

use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Photo controller.
 *
 */
class PhotoController extends Controller
{

    /**
     * Lists all Photo entities.
     *
     */
    public function indexAction()
    {
        $sql = 'SELECT * FROM scrawl_photos';

        $stmt = $this->getDoctrine()->getManager()
        ->getConnection()->prepare($sql);

        //execute query
        $stmt->execute();

        //get all rows of results 
        $entities = $stmt->fetchAll();

        return $this->render('AppBundle:Photo:index.html.twig', array(
            'entities' => $entities,
            ));
    }
    /**
     * Creates a new Photo entity.
     *
     */
    public function createAction(Request $request)
    {
            //check if user is authenticated
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) 
        {
            $this->get('session')->getFlashBag()
            ->add('error','you must be logged in to upload a photo!');
            return $this->redirect($this->generateUrl('homepage'));
        }

        $entity = new Photo();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {

            $this->persistGeolocationForPhoto($entity);

            $entity->upload($this->getLoggedInUser());

            $sql = 'INSERT INTO scrawl_photos value(:path, :user_id, :uploadDate, :latitude, :longitude)';

            $stmt = $this->getDoctrine()->getManager()
            ->getConnection()->prepare($sql);

            //set path of photo to be username_somephoto
            $stmt->bindValue('path', $entity->getPath());
            $stmt->bindValue('user_id', $this->getLoggedInUser());
            $stmt->bindValue('uploadDate', date('Y-m-d'));
            $stmt->bindValue('latitude', $form["latitude"]->getData());
            $stmt->bindValue('longitude', $form["longitude"]->getData());

            //execute query
            $stmt->execute();

            $this->get('session')->getFlashBag()
            ->add('notice','photo successfully uploaded!');

            return $this->redirect($this->generateUrl('photo_show', array('id' => $entity->getPath())));
        }

        $this->get('session')->getFlashBag()
        ->add('error','oops! something went wrong. Try again!');

        return $this->redirectToRoute('homepage');
    }

    /**
    * Helper to save geolocation based on lat/long entry in Photo form
    **/
    private function persistGeolocationForPhoto($entity)
    {
        try{
            $location = $this->reverseGeocode($entity->getLatitude(), $entity->getLongitude());
   
        }
        catch(\Exception $e){
            return;
        }

        try{
            $sql = 'INSERT INTO scrawl_geolocation 
            value(:postalCode, :country, :region, :city, :latitude, :longitude, :streetAddress)';

            $stmt = $this->getDoctrine()->getManager()
            ->getConnection()->prepare($sql);

            $stmt->bindValue('postalCode', $location['postalCode']);
            $stmt->bindValue('country', $location['country']);
            $stmt->bindValue('region', $location["region"]);
            $stmt->bindValue('city', $location["city"]);
            $stmt->bindValue('latitude', $entity->getLatitude());
            $stmt->bindValue('longitude', $entity->getLongitude());
            $stmt->bindValue('streetAddress', $location["streetAddress"]);

            //execute query
            $stmt->execute();
        }
        catch (\Doctrine\DBAL\DBALException $e) { // Should check for more specific exception
            // duplicate entry. Entry we want already in the table. Everything is good.
        }

        $this->get('session')->getFlashBag()
        ->add('notice','photo location successfully saved!');
    }

    private function reverseGeocode($lat, $lon){
        $url = "http://maps.googleapis.com/maps/api/geocode/json?latlng=" . $lat . "," . $lon;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $json = json_decode(curl_exec($ch), true);

        if ($json['status'] == 'ZERO_RESULTS'){
            throw new Exception("Issues decoding specified photo location", 1);
        }

        $addressComponents = $json['results'][0]['address_components'];

        $location = array (
            'postalCode' => $addressComponents[5]['long_name'],
            'streetAddress' => $addressComponents[0]['long_name'] . " " . $addressComponents[1]['long_name'],
            'city' => $addressComponents[2]['long_name'],
            'region' => $addressComponents[3]['short_name'],
            'country' => $addressComponents[4]['long_name']
            );

        return $location;
    }

    public function testAction()
    {
        $sql = 'INSERT INTO scrawl_photos value(100, 1, "testing", "10 10 10", "50", "50")';

        $stmt = $this->getDoctrine()->getManager()
        ->getConnection()->prepare($sql);

        $stmt->execute();

        $this->get('session')->getFlashBag()
        ->add('notice','from test action!');

        return $this->redirectToRoute('homepage');
    }

    /**
     * Creates a form to create a Photo entity.
     *
     * @param Photo $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Photo $entity)
    {
        $form = $this->createForm(new PhotoType(), $entity, array(
            'action' => $this->generateUrl('photo_create'),
            'method' => 'POST',
            ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Photo entity.
     *
     */
    public function newAction()
    {
        $entity = new Photo();
        $form   = $this->createCreateForm($entity);

        return $this->render('AppBundle:Photo:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
            ));
    }

    /**
     * Finds and displays a Photo entity.
     *
     */
    public function showAction($id)
    {

        $sql = 'SELECT * FROM scrawl_photos WHERE path=?';

        $stmt = $this->getDoctrine()->getManager()
        ->getConnection()->prepare($sql);

        //replace ? in query with $id
        $stmt->bindValue(1, $id);

        //execute query
        $stmt->execute();

        //get only row of result
        $entity = $stmt->fetch();

        //pass upload dir to view to use as img src
        $uploadLocation = 'uploads/'.$entity['path'];

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Photo entity.');
        }

        return $this->render('AppBundle:Photo:show.html.twig', array(
            'entity'         => $entity,
            'uploadLocation' => $uploadLocation,
            ));
    }

    /**
     * Displays a form to edit an existing Photo entity.
     *
     */
    public function editAction($id)
    {
        $sql = 'SELECT * FROM scrawl_photos WHERE path=?';
        $stmt = $this->getDoctrine()->getManager()
        ->getConnection()->prepare($sql);
        //replace ? in query with $id
        $stmt->bindValue(1, $id);
        //execute query
        $stmt->execute();
        //get only row of result
        $result = $stmt->fetch();

        //render edit form
        $entity = new Photo();
        $editForm = $this->createEditForm($entity, $id);

        //set edit form fields to data from query
        $editForm->get('latitude')->setData($result['latitude']);
        $editForm->get('longitude')->setData($result['longitude']);


        return $this->render('AppBundle:Photo:edit.html.twig', array(
            'entity'    => $entity,
            'id'        => $id,
            'edit_form' => $editForm->createView()
            ));
    }

    /**
    * Creates a form to edit a Photo entity.
    *
    * @param Photo $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Photo $entity, $id)
    {
        $form = $this->createForm(new PhotoType(), $entity, array(
            'action' => $this->generateUrl('photo_update', array('id' => $id)),
            'method' => 'PUT',
            ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

    /**
     * Edits an existing Photo entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
     //retrieve the hash of form variables passed to the http request
     //return error message if parameters do not exist
        try
        {
            $postParamsHash = $request->request->get('appbundle_photo', 'does not exist!');

            $lat = $postParamsHash['latitude'];
            $lng = $postParamsHash['longitude'];

            $sql = 'UPDATE scrawl_photos SET latitude=:lat, longitude=:lng WHERE path=:path';

            $stmt = $this->getDoctrine()->getManager()
            ->getConnection()->prepare($sql);

     //bind variables
            $stmt->bindValue('path', $id);
            $stmt->bindValue('lat', $lat);
            $stmt->bindValue('lng', $lng);

     //execute query
            $stmt->execute();
        }
        catch (\Doctrine\DBAL\DBALException $e) {

            $this->get('session')->getFlashBag()
            ->add('error','there was a problem updating the photo! Please try again.');

            return $this->redirect($this->generateUrl('homepage'));
        }

        $this->get('session')->getFlashBag()
        ->add('notice','photo successfully uploaded!');

        return $this->redirect($this->generateUrl('photo_show', array('id' => $id)));
    }
    /**
     * Deletes a Photo entity.
     *
     */
    public function deleteAction($id)
    {
        $sql = 'DELETE FROM scrawl_photos WHERE path=?';

        $stmt = $this->getDoctrine()->getManager()
        ->getConnection()->prepare($sql);

        //replace ? in query with $id
        $stmt->bindValue(1, $id);

        //execute query
        $stmt->execute();

        return $this->redirect($this->generateUrl('photo'));
    }

    /**
     * Creates a form to delete a Photo entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */

    //create a JSON response to ajaxly return all photo
    //filepaths so that we can render photos with ng-repeat
    public function getPhotoPathsAction()
    {
        $paths = array();
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppBundle:Photo')->findAll();

        foreach ($entities as $entity) {
            $paths[$entity->getID()] = $entity->getWebPath();
        }
        return new JsonResponse($paths);
    }

    //return all latlons to display map markers
    public function getLatLonsAction()
    {
        $geos = array();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppBundle:Photo')->findAll();

        foreach ($entities as $entity) {
            $geos[$entity->getID()] = [$entity->getLatitude(), $entity->getLongitude()];
        }
        return new JsonResponse($geos);

    }

    //consumes a photo id and produces a JSON representation of the Photo object
    public function getArtAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $photo = $em->getRepository('AppBundle:Photo')->find($id);

        $photoInfo = array(
            "id" => $photo->getId(),
            "latitude" => $photo->getLatitude(),
            "longitude" => $photo->getLongitude(),
            "path" => $photo->getWebPath()
            );

        return new JsonResponse($photoInfo);
    }

    public function getLoggedInUser()
    {
        return $this->get('security.token_storage')->getToken()->getUser()->getId();
    }

}
