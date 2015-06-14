<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;

use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * User controller.
 *
 */
class UserController extends Controller
{

    public function homepageAction()
    {

        $entity = new User();
        $form = $this->createCreateForm($entity);

        return $this->render('AppBundle:Default:index.html.twig', array(
            'form' => $form->createView(),
            ));
    }

    /**
     * Lists all User entities.
     *
     */
    public function indexAction()
    {

        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('AppBundle:User')->findAll();

        return $this->render('AppBundle:User:index.html.twig', array(
            'entities' => $entities,
            ));
    }
    /**
     * Creates a new User entity.
     *
     */
    public function createAction(Request $request)
    {
        if (($this->get('security.token_storage')->getToken()->getUser())
            != 'anon.')
        {
            $this->get('session')->getFlashBag()
            ->add('error','you cannot create a new account since you are already logged in!');
            return $this->redirect($this->generateUrl('homepage'));
        }


        $user = new User();
        $form = $this->createCreateForm($user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            //encode password
            $encoderFactory = $this->get('security.encoder_factory');
            $encoder = $encoderFactory->getEncoder($user);
            $password = $encoder->encodePassword($user->getPassword(), $user->getSalt());
            $user->setPassword($password);

            //persist user to DB
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            //save users's location in appropriate table
            $this->persistGeolocationForUser($user);

            return $this->redirect($this->generateUrl('user_show', array('id' => $user->getId())));
        }

        return $this->render('AppBundle:User:new.html.twig', array(
            'entity' => $user,
            'form'   => $form->createView(),
            ));
    }

        /**
    * Helper to save geolocation based on lat/long entry in User form
    **/
    private function persistGeolocationForUser($entity)
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
        ->add('notice','user location successfully saved!');
    }

    private function reverseGeocode($lat, $lon){
        $url = "http://maps.googleapis.com/maps/api/geocode/json?latlng=" . $lat . "," . $lon;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        $json = json_decode(curl_exec($ch), true);

        if ($json['status'] == 'ZERO_RESULTS'){
            throw new Exception("Issues decoding specified user location", 1);
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

    /**
     * Creates a form to create a User entity.
     *
     * @param User $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(User $entity)
    {
        $form = $this->createForm(new UserType(), $entity, array(
            'action' => $this->generateUrl('user_create'),
            'method' => 'POST',
            ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new User entity.
     *
     */
    public function newAction()
    {
        $entity = new User();
        $form   = $this->createCreateForm($entity);

        return $this->render('AppBundle:User:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
            ));
    }

    /**
     * Finds and displays a User entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('AppBundle:User:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
            ));
    }

    /**
     * Displays a form to edit an existing User entity.
     *
     */
    public function editAction($id)
    {
        $loggedIn = $this->get('security.token_storage')->getToken()->getUser();
        
        if (!$this->canUpdateOrDelete($loggedIn, $id))
        {
            $this->get('session')->getFlashBag()
            ->add('error','you do not have permission to edit another user profile!!');
            return $this->redirect($this->generateUrl('homepage'));
        }

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('AppBundle:User:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            ));
    }

    /**
    * Creates a form to edit a User entity.
    *
    * @param User $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(User $entity)
    {
        $form = $this->createForm(new UserType(), $entity, array(
            'action' => $this->generateUrl('user_update', array('id' => $entity->getId())),
            'method' => 'PUT',
            ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing User entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('user_edit', array('id' => $id)));
        }

        return $this->render('AppBundle:User:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            ));
    }
    /**
     * Deletes a User entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $loggedIn = $this->get('security.token_storage')->getToken()->getUser();

        if (!$this->canUpdateOrDelete($loggedIn, $id))
        {
            $this->get('session')->getFlashBag()
            ->add('error','you do not have permission to delete another user profile!!');
            return $this->redirect($this->generateUrl('homepage'));
        }

        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('AppBundle:User')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find User entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('user'));
    }

    /**
     * Creates a form to delete a User entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
        ->setAction($this->generateUrl('user_delete', array('id' => $id)))
        ->setMethod('DELETE')
        ->add('submit', 'submit', array('label' => 'Delete'))
        ->getForm()
        ;
    }

    //$user, $id --> boolean
    //return true if user is Admin, or
    //user is accessing own page, false otherwise
    private function canUpdateOrDelete($user, $id)
    {
        if ((in_array('ROLE_ADMIN', $user->getRoles())) ||
            $user->getID() == $id)
        {
            return true;
        }
        return false;
    }
}
