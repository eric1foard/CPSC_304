<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use AppBundle\Entity\Tag;
use AppBundle\Form\TagType;

/**
 * Tag controller.
 *
 */
class TagController extends Controller
{

    /**
     * Lists all Tag entities.
     *
     */
    public function indexAction()
    {
        //$em = $this->getDoctrine()->getManager();

        $sql = 'SELECT * FROM scrawl_tags';

        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($sql);

        //execute query
        $stmt->execute();

        //get all rows of results 
        $entities = $stmt->fetchAll();

        //original generated code
        //$entities = $em->getRepository('AppBundle:Tag')->findAll();

        return $this->render('AppBundle:Tag:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new Tag entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new Tag();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            //original generated code
            //$em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            $sql = 'INSERT INTO scrawl_tags value(:id, :tagName)';
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($sql);

            $stmt->bindValue('id', 104);
            $stmt->bindValue('tagName', $form["tags"]->getData());

            $stmt->execute();            

            $this->get('session')->getFlashBag()->add('notice','tag successfully added');
            return $this->redirect($this->generateUrl('tag_show', array('id' => $entity->getId())));
        }

        $this->get('session')->getFlashBag()->add('notice','tag addition error, please try again');
        return $this->render('AppBundle:Tag:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Tag entity.
     *
     * @param Tag $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Tag $entity)
    {
        $form = $this->createForm(new TagType(), $entity, array(
            'action' => $this->generateUrl('tag_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Tag entity.
     *
     */
    public function newAction()
    {
        $entity = new Tag();
        $form   = $this->createCreateForm($entity);

        return $this->render('AppBundle:Tag:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Tag entity.
     *
     */
    public function showAction($id)
    {
        //original generated code
        //$em = $this->getDoctrine()->getManager();

        //original generated code
        //$entity = $em->getRepository('AppBundle:Tag')->find($id);

        $sql = 'SELECT * FROM scrawl_tags WHERE id=?';
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($sql);

        $stmt->bindValue(1, $id);

        $stmt->execute();

        $entity = $stmt->fetch();

        

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tag entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('AppBundle:Tag:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Tag entity.
     *
     */
    public function editAction($id)
    {
        //original generated code
        //$em = $this->getDoctrine()->getManager();

        //original generated code
        //$entity = $em->getRepository('AppBundle:Tag')->find($id);

        $sql = 'SELECT * FROM scrawl_tags WHERE id=?';

        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($sql);

        $stmt->bindValue(1, $id);
        $stmt->execute();

        $result = $stmt->fetch();

        $entity = new Tag();
        $editForm = $this->createEditForm($entity, $id);

        $editForm->get('id')->setData($result['id']);
        $editForm->get('tagName')->setData($result['tagName']);



        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tag entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('AppBundle:Tag:edit.html.twig', array(
            'entity'      => $entity,
            'id'          => $id,
            'edit_form'   => $editForm->createView(),
            //'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Tag entity.
    *
    * @param Tag $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Tag $entity)
    {
        $form = $this->createForm(new TagType(), $entity, array(
            'action' => $this->generateUrl('tag_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Tag entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        //original generated code
        //$em = $this->getDoctrine()->getManager();

        //original generated code
        //$entity = $em->getRepository('AppBundle:Tag')->find($id);

        //original generated code
        //if (!$entity) {
        //    throw $this->createNotFoundException('Unable to find Tag entity.'); }

        //$deleteForm = $this->createDeleteForm($id);
        //$editForm = $this->createEditForm($entity);
        //$editForm->handleRequest($request);

        //if ($editForm->isValid()) {
        //    $em->flush();

        try
        {
            $postParamsHash = $request->request->get('appbundle_tag', 'does not exist!');

            $tags = $postParamsHash['tag'];

            $sql = 'UPDATE scrawl_photos SET longitude=:tags WHERE id=:id';

            $stmt = $this->getDoctrine()->getManager()
            ->getConnection()->prepare($sql);

     //bind variables
            $stmt->bindValue('id', $id);
            $stmt->bindValue('tags', $tags);

     //execute query
            $stmt->execute();
        }
        catch (\Doctrine\DBAL\DBALException $e) {

            $this->get('session')->getFlashBag()
            ->add('error','there was a problem updating the tag! Please try again.');

            return $this->redirect($this->generateUrl('homepage'));
        }


        $this->get('session')->getFlashBag()->add('notice', 'tag successfully updated')
        return $this->redirect($this->generateUrl('tag_edit', array('id' => $id)));
        //}

        //return $this->render('AppBundle:Tag:edit.html.twig', array(
        //    'entity'      => $entity,
        //    'edit_form'   => $editForm->createView(),
        //    'delete_form' => $deleteForm->createView(),));
    }
    /**
     * Deletes a Tag entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        //$form = $this->createDeleteForm($id);
        //$form->handleRequest($request);

        //if ($form->isValid()) {
        //    $em = $this->getDoctrine()->getManager();
        //    $entity = $em->getRepository('AppBundle:Tag')->find($id);

        //    if (!$entity) {
        //        throw $this->createNotFoundException('Unable to find Tag entity.');}

        //    $em->remove($entity);
        //    $em->flush();}

        $sql = 'DELETE FROM scrawl_tags WHERE id=?';

        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($sql);

        //replace ? in query with $id
        $stmt->bindValue(1, $id);

        //execute query
        $stmt->execute();

        return $this->redirect($this->generateUrl('tag'));
    }
}
