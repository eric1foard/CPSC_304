<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use AppBundle\Entity\Locations2;
use AppBundle\Form\Locations2Type;

/**
 * Locations2 controller.
 *
 */
class Locations2Controller extends Controller
{

    /**
     * Lists all Locations2 entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('AppBundle:Locations2')->findAll();

        return $this->render('AppBundle:Locations2:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new Locations2 entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new Locations2();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('locations2_show', array('id' => $entity->getId())));
        }

        return $this->render('AppBundle:Locations2:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Locations2 entity.
     *
     * @param Locations2 $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Locations2 $entity)
    {
        $form = $this->createForm(new Locations2Type(), $entity, array(
            'action' => $this->generateUrl('locations2_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Locations2 entity.
     *
     */
    public function newAction()
    {
        $entity = new Locations2();
        $form   = $this->createCreateForm($entity);

        return $this->render('AppBundle:Locations2:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Locations2 entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppBundle:Locations2')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Locations2 entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('AppBundle:Locations2:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Locations2 entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppBundle:Locations2')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Locations2 entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('AppBundle:Locations2:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Locations2 entity.
    *
    * @param Locations2 $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Locations2 $entity)
    {
        $form = $this->createForm(new Locations2Type(), $entity, array(
            'action' => $this->generateUrl('locations2_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Locations2 entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppBundle:Locations2')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Locations2 entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('locations2_edit', array('id' => $id)));
        }

        return $this->render('AppBundle:Locations2:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a Locations2 entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('AppBundle:Locations2')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Locations2 entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('locations2'));
    }

    /**
     * Creates a form to delete a Locations2 entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('locations2_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
