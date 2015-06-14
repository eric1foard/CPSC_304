<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Photo;
use AppBundle\Form\PhotoType;
use AppBundle\Entity\Geolocation;

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

            $sql = 'INSERT INTO scrawl_photos value(:id, :user_id, :path, :uploadDate, :latitude, :longitude)';

            $stmt = $this->getDoctrine()->getManager()
            ->getConnection()->prepare($sql);

            $stmt->bindValue('id', 127);
            $stmt->bindValue('user_id', $this->getLoggedInUser());
            //set path of photo to be username_somephoto
            $stmt->bindValue('path', $entity->getPath());
            $stmt->bindValue('uploadDate', date('Y-m-d'));
            $stmt->bindValue('latitude', $form["latitude"]->getData());
            $stmt->bindValue('longitude', $form["longitude"]->getData());

            //execute query
            $stmt->execute();

            $this->get('session')->getFlashBag()
            ->add('notice','photo successfully uploaded!');

            return $this->redirect($this->generateUrl('photo_show', array('id' => 127)));
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
        $geolocation = new Geolocation();
        
        $geolocation->setLatitude($entity->getLatitude());
        $geolocation->setLongitude($entity->getLongitude());
        // make call to Google API to populate other geolocation fields given lat, long
        // $curl = new \Ivory\HttpAdapter\CurlHttpAdapter();
        // $geocoder = new \Geocoder\Provider\GoogleMaps($curl);
        // $geocoder->reverse($entity->getLatitude(), $entity->getLongitude());
        //TODO create the rest of the geocoder entity
        $em = $this->getDoctrine()->getManager();
        $em->persist($geolocation);
        $em->flush();
        $this->get('session')->getFlashBag()
        ->add('notice','photo geolocation successfully saved!');
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

        $sql = 'SELECT * FROM scrawl_photos s WHERE s.id=?';

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
        $sql = 'SELECT * FROM scrawl_photos s WHERE s.id=?';
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
        var_dump($request->get('latitude'));die;

        

        return $this->render('AppBundle:Photo:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            ));
    }
    /**
     * Deletes a Photo entity.
     *
     */
    public function deleteAction($id)
    {
        $sql = 'DELETE FROM scrawl_photos WHERE id=?';

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
