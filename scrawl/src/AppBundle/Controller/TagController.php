<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Tag;
use AppBundle\Form\TagType;

/**
 * Tag controller.
 *
 */
class TagController extends Controller
{


    public function ajaxGetAllTagsAction()
    {
        $sql = 'SELECT id From scrawl_tags';

        $stmt = $this->getDoctrine()->getManager()
        ->getConnection()->prepare($sql);

        //execute query
        $stmt->execute();

        //get all rows of results 
        $entities = $stmt->fetchAll();

        $tags = array();

        foreach ($entities as $entity) {
            array_push($tags, $entity['id']);
        }

        return new JsonResponse($tags);
    }

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
        //instantiate Tag so we can create form
        $entity = new Tag();
        $form = $this->createCreateForm($entity);

        //performs type checking
        $form->handleRequest($request);

        if ($form->isValid()) {

            $sql = 'INSERT INTO scrawl_tags value(:tagName, :user)';

            //pass sql to DBMS
            $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($sql);

            //bind variables to query
            $stmt->bindValue('tagName', $form['tagName']->getData());
            $stmt->bindValue('user', $this->get('security.token_storage')->getToken()->getUsername());

            $stmt->execute();            

            $this->get('session')->getFlashBag()->add('notice','tag successfully added');
            return $this->redirect($this->generateUrl('tag_show', array('id' => $entity->getId())));
        }

        $this->get('session')->getFlashBag()->add('error','problem creating tag, please try again');
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
        $sql = 'SELECT * FROM scrawl_tags WHERE id=?';
        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($sql);

        //remember, the ID is a tag name
        $stmt->bindValue(1, $id);

        $stmt->execute();

        $entity = $stmt->fetch();

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tag entity.');
        }

        return $this->render('AppBundle:Tag:show.html.twig', array(
            'entity'      => $entity
            ));
    }

    /**
     * Displays a form to edit an existing Tag entity.
     *
     */
    public function editAction($id)
    {

        $sql = 'SELECT * FROM scrawl_tags WHERE id=?';

        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($sql);

        $stmt->bindValue(1, $id);
        $stmt->execute();

        $result = $stmt->fetch();

        $entity = new Tag();
        $editForm = $this->createEditForm($entity, $id);

        //setting result[id] because that's the name of the 
        //tagname column in DB
        $editForm->get('tagName')->setData($result['id']);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tag entity.');
        }

        $editForm = $this->createEditForm($entity, $id);

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
    private function createEditForm(Tag $entity, $id)
    {
        $form = $this->createForm(new TagType(), $entity, array(
            'action' => $this->generateUrl('tag_update', array('id' => $id)),
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
        try
        {
            $postParamsHash = $request->request->get('appbundle_tag', 'does not exist!');

            $tag = $postParamsHash['tagName'];

            $sql = 'UPDATE scrawl_tags SET id=:newID WHERE id=:oldID';

            $stmt = $this->getDoctrine()->getManager()
            ->getConnection()->prepare($sql);

     //bind variables
            $stmt->bindValue('oldID', $id);
            $stmt->bindValue('newID', $tag);

     //execute query
            $stmt->execute();
        }
        catch (\Doctrine\DBAL\DBALException $e) {

            var_dump($postParamsHash);die;

            $this->get('session')->getFlashBag()
            ->add('error','there was a problem updating the tag! Please try again.');

            return $this->redirect($this->generateUrl('homepage'));
        }


        $this->get('session')->getFlashBag()->add('notice', 'tag successfully updated');
        return $this->redirect($this->generateUrl('tag'));
    }
    /**
     * Deletes a Tag entity.
     *
     */
    public function deleteAction($id)
    {
        $sql = 'DELETE FROM scrawl_tags WHERE id=?';

        $stmt = $this->getDoctrine()->getManager()->getConnection()->prepare($sql);

        //replace ? in query with $id
        $stmt->bindValue(1, $id);

        //execute query
        $stmt->execute();

        return $this->redirect($this->generateUrl('tag'));
    }
}
