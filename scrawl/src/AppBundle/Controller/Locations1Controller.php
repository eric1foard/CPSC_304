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
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('AppBundle:Locations1')->findAll();

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
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

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
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppBundle:Locations1')->find($id);

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
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppBundle:Locations1')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Locations1 entity.');
        }

        $editForm = $this->createEditForm($entity);
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
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppBundle:Locations1')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Locations1 entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('locations1_edit', array('id' => $id)));
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
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('AppBundle:Locations1')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Locations1 entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

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
