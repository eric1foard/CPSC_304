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

            $sql = 'INSERT INTO scrawl_photos value(:path, :device, :viewCount, :uploadDate, :latitude, :longitude)';

            $stmt = $this->getDoctrine()->getManager()
            ->getConnection()->prepare($sql);

            //set path of photo to be username_somephoto
            $stmt->bindValue('path', $entity->getPath());
            $stmt->bindValue('device', $form["device"]->getData());
            //new photo only has 1 view
            $stmt->bindValue('viewCount', 1);
            $stmt->bindValue('uploadDate', date('Y-m-d'));
            $stmt->bindValue('latitude', $form["latitude"]->getData());
            $stmt->bindValue('longitude', $form["longitude"]->getData());

            //execute query
            $stmt->execute();


            $tags = $request->request->get('appbundle_photo', 'does not exist!')['tags'];

            //create relationships between photo and tags
            $this->createHasTagRecord($entity->getPath(), $tags);

            $this->saveUploadUser($entity);


            $this->get('session')->getFlashBag()
            ->add('notice','photo successfully uploaded!');

            return $this->redirect($this->generateUrl('photo_show', array('id' => $entity->getPath())));
        }

        $this->get('session')->getFlashBag()
        ->add('error','oops! something went wrong. Try again!');

        return $this->redirectToRoute('homepage');
    }

    //consume a string representing the PK of the photo
    //and an array of tags and create a record in the
    //has_tag for each tag
    private function createHasTagRecord($photoKey, $tags)
    {
        
        foreach ($tags as $tag) {
            $sql = 'INSERT INTO has_tag value(:path, :tagName)';

            $stmt = $this->getDoctrine()->getManager()
            ->getConnection()->prepare($sql);

            //set path of photo to be username_somephoto
            $stmt->bindValue('path', $photoKey);
            $stmt->bindValue('tagName', $tag);

            //execute query
            $stmt->execute();
        }
        return;
    }

    /**
     * Save to uploaded_by table when user saves a photo
     */
    private function saveUploadUser($photo){
        $sql = 'INSERT INTO uploaded_by value(:path, :username, :uploadDate)';

        $stmt = $this->getDoctrine()->getManager()
        ->getConnection()->prepare($sql);

        //set path of photo to be username_somephoto
        $stmt->bindValue('path', $photo->getPath());
        $stmt->bindValue('username', $this->getLoggedInUser());
        $stmt->bindValue('uploadDate', $photo->getUploadDate());

        //execute query
        $stmt->execute();
    }


    /**
    * Helper to save geolocation based on lat/long entry in Photo form
    **/
    private function persistGeolocationForPhoto($entity)
    {
        try
        {
            $location = $this->reverseGeocode($entity->getLatitude(), $entity->getLongitude());

        }
        catch(\Exception $e){

            return;

        }

        try
        {
            // Insert into Locations1 table
            $sql = 'INSERT INTO scrawl_locations1
            value(:postalCode, :country, :region, :city)';

            $stmt = $this->getDoctrine()->getManager()
            ->getConnection()->prepare($sql);

            $stmt->bindValue('postalCode', $location['postalCode']);
            $stmt->bindValue('country', $location['country']);
            $stmt->bindValue('region', $location["region"]);
            $stmt->bindValue('city', $location["city"]);

            //execute query
            $stmt->execute();

            // Insert into Locations2 tables
            $sql2 = 'INSERT INTO scrawl_locations2
            value(:latitude, :longitude, :postalCode, :streetAddress)';

            $stmt2 = $this->getDoctrine()->getManager()
            ->getConnection()->prepare($sql2);

            $stmt2->bindValue('latitude', $entity->getLatitude());
            $stmt2->bindValue('longitude', $entity->getLongitude());
            $stmt2->bindValue('postalCode', $location['postalCode']);
            $stmt2->bindValue('streetAddress', $location["streetAddress"]);

            //execute query
            $stmt2->execute();
        }
        catch (\Doctrine\DBAL\DBALException $e) 
        { // Should check for more specific exception
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

        $location = array(
            'postalCode' => $this->geolocationJSONParser($addressComponents, 'postal_code'),
            'streetAddress' => $this->geolocationJSONParser($addressComponents, 'street_number') . " " . $this->geolocationJSONParser($addressComponents, 'route'),
            'city' => $this->geolocationJSONParser($addressComponents, 'locality'),
            'region' => $this->geolocationJSONParser($addressComponents, 'administrative_area_level_1'),
            'country' => $this->geolocationJSONParser($addressComponents, 'country')
            ); 

        return $location;
    }

    // int would be the ith array it loops through
    // type would be the keyword of the location that it looks through
    private function geolocationJSONParser($sourcearray, $keyword)
    {
        if(stristr($sourcearray[$i]['types'][0], $keyword) != FALSE){
            $val = '';
            for($i = 0; $i < count($sourcearray); $i++){
                if(strpos($sourcearray[$i]['types'][0], $keyword)>0){
                    $val = $sourcearray[$i]['long_name'];
                }
            }
        }
        return $val;
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

        //update view statistics
        $this->updateViewData($id);

        // if (!$entity) {
        //     throw $this->createNotFoundException('Unable to find Photo entity.');
        // }

        return $this->render('AppBundle:Photo:show.html.twig', array(
            'entity'         => $entity,
            'uploadLocation' => $uploadLocation,
            ));
    }

    // public function getPhotoTags($photoPK)
    // {
    //     $sql = 'SELECT tagName FROM has_tag WHERE path=:photoPK';

    //     $stmt = $this->getDoctrine()->getManager()
    //     ->getConnection()->prepare($sql);

    //     $stmt->bindValue('photoPK', $photoPK);

    //     $stmt->execute();

    //     //get all results
    //     $tags = $stmt->fetchAll();

    //     $result = [];
    //     foreach ($tags as $key => $value) {
    //         array_push($result, $value);

    //     }

    //     return $result;

    // }

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
        $editForm->get('device')->setData($result['device']);


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
            $lng = $postParamsHash['device'];

            $sql = 'UPDATE scrawl_photos SET device=:device, latitude=:lat, longitude=:lng WHERE path=:path';

            $stmt = $this->getDoctrine()->getManager()
            ->getConnection()->prepare($sql);

     //bind variables
            $stmt->bindValue('path', $id);
            $stmt->bindValue('lat', $lat);
            $stmt->bindValue('lng', $lng);
            $stmt->bindValue('device', $device);


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

    public function getLoggedInUser()
    {
        return $this->get('security.token_storage')->getToken()->getUser()->getId();
    }

    //increment the number of times a photo has been viewed and
    //update has_viewed relation to show that user has viewed 
    //tags associated with this photo
    public function updateViewData($photoPK)
    {
        $username = $this->getLoggedInUser();


        $this->incrementViewCount($photoPK);
        $this->updateHasViewed($photoPK, $username);
        return;
    }

    //increment the number of times a photo has been viewed
    //in scrawl_photos view count
    public function incrementViewCount($photoPK)
    {
        $sql = 'UPDATE scrawl_photos SET viewCount=viewCount+1 WHERE path=:photoPK';

        $stmt = $this->getDoctrine()->getManager()
        ->getConnection()->prepare($sql);

        $stmt->bindValue('photoPK', $photoPK);

        $stmt->execute();

        return;
    }

    public function updateHasViewed($photoPK, $username)
    {
        //get all tags for this photo
        $sql = 'SELECT tagName FROM has_tag WHERE path=:photoPK';

        $stmt = $this->getDoctrine()->getManager()
        ->getConnection()->prepare($sql);

        $stmt->bindValue('photoPK', $photoPK);
        $stmt->execute();
        $tags = $stmt->fetchAll();


        //create entries in has_viewed for each tag
        foreach ($tags as $tag) {

            $sql = 'INSERT INTO has_viewed(username, tagName, count) 
            value(:username, :tag, 1)
            ON DUPLICATE KEY UPDATE count=count+1';

            $stmt = $this->getDoctrine()->getManager()
            ->getConnection()->prepare($sql);

            
            $stmt->bindValue('tag', $tag['tagName']);
            $stmt->bindValue('username', $username);

            $stmt->execute();
        }

    }
}
