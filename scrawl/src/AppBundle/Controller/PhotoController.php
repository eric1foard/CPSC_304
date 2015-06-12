<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Photo;
use AppBundle\Form\PhotoType;
use AppBundle\Entity\Geolocation;
use AppBundle\Form\GeolocationType;
use AppBundle\Controller\GeolocationController;

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
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('AppBundle:Photo')->findAll();

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

            $entity->setUser($this->get('security.token_storage')->getToken()->getUser());
            $entity->upload();
            $entity->setUploadDate(date('Y-m-d'));

            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

                        $this->get('session')->getFlashBag()
            ->add('notice','photo successfully uploaded!');

            return $this->redirect($this->generateUrl('photo_show', array('id' => $entity->getId())));
        }

        $this->get('session')->getFlashBag()
        ->add('error','oops! something went wrong. Try again!');

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
        $photoEntity = new Photo();
        $photoForm   = $this->createCreateForm($photoEntity);

        $geolocationEntity = new Photo();
//        $geolocationForm   = $this->get('geolocation_service')->createCreateForm($geolocationEntity);
        $geolocationForm  = $this->createCreateForm($photoEntity);

        return $this->render('AppBundle:Photo:new.html.twig', array(
            'entity' => $photoEntity,
            'form'   => $photoForm->createView(),
            'geolocationEntity' => $geolocationEntity,
            'geolocationForm' => $geolocationForm->createView()
            ));
    }

    /**
     * Finds and displays a Photo entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppBundle:Photo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Photo entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('AppBundle:Photo:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
            ));
    }

    /**
     * Displays a form to edit an existing Photo entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppBundle:Photo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Photo entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('AppBundle:Photo:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            ));
    }

    /**
    * Creates a form to edit a Photo entity.
    *
    * @param Photo $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Photo $entity)
    {
        $form = $this->createForm(new PhotoType(), $entity, array(
            'action' => $this->generateUrl('photo_update', array('id' => $entity->getId())),
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
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppBundle:Photo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Photo entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('photo_edit', array('id' => $id)));
        }

        return $this->render('AppBundle:Photo:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            ));
    }
    /**
     * Deletes a Photo entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('AppBundle:Photo')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Photo entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('photo'));
    }

    /**
     * Creates a form to delete a Photo entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
        ->setAction($this->generateUrl('photo_delete', array('id' => $id)))
        ->setMethod('DELETE')
        ->add('submit', 'submit', array('label' => 'Delete'))
        ->getForm()
        ;
    }

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





}
