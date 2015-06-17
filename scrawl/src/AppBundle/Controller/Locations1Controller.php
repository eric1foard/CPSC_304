<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use AppBundle\Entity\Locations1;
use AppBundle\Form\Locations1Type;

/**
 * Locations1 controller.
 *
 */
class Locations1Controller extends Controller
{

    /**
     * Lists all Locations1 entities.
     *
     */
    public function indexAction()
    {
        $sql = 'SELECT * FROM scrawl_locations1';

        $stmt = $this->getDoctrine()->getManager()
        ->getConnection()->prepare($sql);
        
        //execute query
        $stmt->execute();

        $entities = $stmt->fetchAll();

        return $this->render('AppBundle:Locations1:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new Locations1 entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new Locations1();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $sql = 'INSERT INTO scrawl_locations1 value(:postalCode, :country, :region, :city)';

            $stmt = $this->getDoctrine()->getManager()
            ->getConnection()->prepare($sql);

            $stmt->bindValue('postalCode', $form["postalCode"]->getData());
            $stmt->bindValue('country', $form["country"]->getData());
            $stmt->bindValue('region', $form["region"]->getData());
            $stmt->bindValue('city', $form["city"]->getData());

            //execute query
            $stmt->execute();

            return $this->redirect($this->generateUrl('locations1_show', array('id' => $entity->getId())));
        }

        return $this->render('AppBundle:Locations1:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Locations1 entity.
     *
     * @param Locations1 $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Locations1 $entity)
    {
        $form = $this->createForm(new Locations1Type(), $entity, array(
            'action' => $this->generateUrl('locations1_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Locations1 entity.
     *
     */
    public function newAction()
    {
        $entity = new Locations1();
        $form   = $this->createCreateForm($entity);

        return $this->render('AppBundle:Locations1:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Locations1 entity.
     *
     */
    public function showAction($id)
    {
        $sql = 'SELECT * FROM scrawl_locations1 WHERE postalCode=?';

        $stmt = $this->getDoctrine()->getManager()
        ->getConnection()->prepare($sql);

        //replace ? in query with $id
        $stmt->bindValue(1, $id);

        //execute query
        $stmt->execute();

        //get only row of result
        $entity = $stmt->fetch();

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Locations1 entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('AppBundle:Locations1:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Locations1 entity.
     *
     */
    public function editAction($id)
    {
        $sql = 'SELECT * FROM scrawl_locations1 WHERE postalCode=?';

        $stmt = $this->getDoctrine()->getManager()
        ->getConnection()->prepare($sql);
        //replace ? in query with $id
        $stmt->bindValue(1, $id);
        //execute query
        $stmt->execute();
        //get only row of result
        $result = $stmt->fetch();

        //render edit form
        $entity = new Locations1();
        $editForm = $this->createEditForm($entity, $id);

        //set edit form fields to data from query
        $editForm->get('postalCode')->setData($result['postalCode']);
        $editForm->get('country')->setData($result['country']);
        $editForm->get('region')->setData($result['region']);
        $editForm->get('city')->setData($result['city']);


        $deleteForm = $this->createDeleteForm($id);

        return $this->render('AppBundle:Locations1:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Locations1 entity.
    *
    * @param Locations1 $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Locations1 $entity)
    {
        $form = $this->createForm(new Locations1Type(), $entity, array(
            'action' => $this->generateUrl('locations1_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Locations1 entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
     //retrieve the hash of form variables passed to the http request
     //return error message if parameters do not exist
        try
        {
            $postParamsHash = $request->request->get('appbundle_locations1', 'does not exist!');

            $postalCode = $postParamsHash['postalCode'];
            $country = $postParamsHash['country'];
            $region = $postParamsHash['region'];
            $city = $postParamsHash['city'];


            $sql = 'UPDATE scrawl_locations1 SET postalCode=:postalCode, country=:country, region=:region, city=:city WHERE postalCode=:postalCode';

            $stmt = $this->getDoctrine()->getManager()
            ->getConnection()->prepare($sql);

     //bind variables
            $stmt->bindValue('postalCode', $id);
            $stmt->bindValue('country', $country);
            $stmt->bindValue('region', $region);
            $stmt->bindValue('city', $city);


     //execute query
            $stmt->execute();
        }
        catch (\Doctrine\DBAL\DBALException $e) {

            $this->get('session')->getFlashBag()
            ->add('error','there was a problem updating the location! Please try again.');

            return $this->redirect($this->generateUrl('homepage'));
        }

        return $this->render('AppBundle:Locations1:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a Locations1 entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $sql = 'DELETE FROM scrawl_locations1 WHERE postalCode=?';

        $stmt = $this->getDoctrine()->getManager()
        ->getConnection()->prepare($sql);

        //replace ? in query with $id
        $stmt->bindValue(1, $id);

        //execute query
        $stmt->execute();

        return $this->redirect($this->generateUrl('locations1'));
    }

    /**
     * Creates a form to delete a Locations1 entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('locations1_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
