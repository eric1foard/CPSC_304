<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use AppBundle\Entity\Geolocation;
use AppBundle\Form\GeolocationType;

/**
 * Geolocation controller.
 *
 */
class GeolocationController extends Controller
{

    /**
     * Lists all Geolocation entities.
     *
     */
    public function indexAction()
    {
        $sql = 'SELECT * FROM scrawl_geolocation';

        $stmt = $this->getDoctrine()->getManager()
        ->getConnection()->prepare($sql);

        //execute query
        $stmt->execute();

        //get all rows of results 
        $entities = $stmt->fetchAll();

        return $this->render('AppBundle:Geolocation:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new Geolocation entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new Geolocation();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $sql = 'INSERT INTO scrawl_geolocation 
                    value(:id, :postalCode, :country, :region, :city, :latitude, :longitude, :streetAddress)';

            $stmt = $this->getDoctrine()->getManager()
            ->getConnection()->prepare($sql);

            $stmt->bindValue('id', 310);
            $stmt->bindValue('postalCode', $form["postalCode"]->getData());
            $stmt->bindValue('country', $form["country"]->getData());
            $stmt->bindValue('region', $form["region"]->getData());
            $stmt->bindValue('region', $form["city"]->getData());
            $stmt->bindValue('latitude', $form["latitude"]->getData());
            $stmt->bindValue('longitude', $form["longitude"]->getData());
            $stmt->bindValue('region', $form["streetAddress"]->getData());

            //execute query
            $stmt->execute();

            $this->get('session')->getFlashBag()
            ->add('notice','photo location successfully saved!');

            return $this->redirect($this->generateUrl('geolocation_show', array('id' => 310)));
        }

        return $this->render('AppBundle:Geolocation:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Geolocation entity.
     *
     * @param Geolocation $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Geolocation $entity)
    {
        $form = $this->createForm(new GeolocationType(), $entity, array(
            'action' => $this->generateUrl('geolocation_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Geolocation entity.
     *
     */
    public function newAction()
    {
        $entity = new Geolocation();
        $form   = $this->createCreateForm($entity);

        return $this->render('AppBundle:Geolocation:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Geolocation entity.
     *
     */
    public function showAction($id)
    {
        $sql = 'SELECT * FROM scrawl_photos s WHERE s.id=?';

        $stmt = $this->getDoctrine()->getManager()
        ->getConnection()->prepare($sql);

        //replace ? in query with $id
        $stmt->bindValue(1, $id);

        $stmt->execute();

        //get only row of result
        $entity = $stmt->fetch();

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Geolocation entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('AppBundle:Geolocation:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Geolocation entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppBundle:Geolocation')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Geolocation entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('AppBundle:Geolocation:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Geolocation entity.
    *
    * @param Geolocation $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Geolocation $entity)
    {
        $form = $this->createForm(new GeolocationType(), $entity, array(
            'action' => $this->generateUrl('geolocation_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Geolocation entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppBundle:Geolocation')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Geolocation entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('geolocation_edit', array('id' => $id)));
        }

        return $this->render('AppBundle:Geolocation:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a Geolocation entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('AppBundle:Geolocation')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Geolocation entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('geolocation'));
    }

    /**
     * Creates a form to delete a Geolocation entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('geolocation_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
