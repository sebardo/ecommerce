<?php

namespace EcommerceBundle\Controller;

use EcommerceBundle\Entity\Category;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use EcommerceBundle\Entity\Attribute;
use EcommerceBundle\Form\AttributeType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Attribute controller.
 *
 * @Route("/admin/attributes")
 */
class AttributeController extends Controller
{
    /**
     * Lists all Attribute entities.
     *
     * @return array
     *
     * @Route("/")
     * @Method("GET")
     * @Template("EcommerceBundle:Attribute:index.html.twig")
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * Returns a list of Attribute entities in JSON format.
     *
     * @param int $categoryId The category id
     *
     * @return JsonResponse
     *
     * @Route("/list.{_format}/{categoryId}", requirements={ "_format" = "json" }, defaults={ "_format" = "json" })
     * @Method("GET")
     */
    public function listJsonAction($categoryId = null)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var \AdminBundle\Services\DataTables\JsonList $jsonList */
        $jsonList = $this->get('json_list');
        $jsonList->setRepository($em->getRepository('EcommerceBundle:Attribute'));

        if (!is_null($categoryId)) {
            $jsonList->setCategory($categoryId);
        }

        $response = $jsonList->get();

        return new JsonResponse($response);
    }

    /**
     * Creates a new Attribute entity.
     *
     * @param Request $request The request
     *
     * @return array|RedirectResponse
     *
     * @Route("/")
     * @Method("POST")
     * @Template("EcommerceBundle:Attribute:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Attribute();
        $form = $this->createForm(new AttributeType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'attribute.created');

            return $this->redirect($this->generateUrl('ecommerce_attribute_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new Attribute entity.
     *
     * @return array
     *
     * @Route("/new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Attribute();

        if ($this->getRequest()->query->has('category')) {
            $em = $this->getDoctrine()->getManager();

            $category = $em->getRepository('EcommerceBundle:Category')->
                find($this->getRequest()->query->get('category'));

            $entity->setCategory($category);
        }

        $form = $this->createForm(new AttributeType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Attribute entity.
     *
     * @param int $id The entity id
     *
     * @throws NotFoundHttpException
     * @return array
     *
     * @Route("/{id}")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Attribute $entity */
        $entity = $em->getRepository('EcommerceBundle:Attribute')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Attribute entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Attribute entity.
     *
     * @param int $id The entity id
     *
     * @throws NotFoundHttpException
     * @return array
     *
     * @Route("/{id}/edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Attribute $entity */
        $entity = $em->getRepository('EcommerceBundle:Attribute')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Attribute entity.');
        }

        $editForm = $this->createForm(new AttributeType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Attribute entity.
     *
     * @param Request $request The request
     * @param int     $id      The entity id
     *
     * @throws NotFoundHttpException
     * @return array|RedirectResponse
     *
     * @Route("/{id}")
     * @Method("PUT")
     * @Template("EcommerceBundle:Attribute:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Attribute $entity */
        $entity = $em->getRepository('EcommerceBundle:Attribute')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Attribute entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new AttributeType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'attribute.edited');

            return $this->redirect($this->generateUrl('ecommerce_attribute_show', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Attribute entity.
     *
     * @param Request $request The request
     * @param int     $id      The entity id
     *
     * @throws NotFoundHttpException
     * @return RedirectResponse
     *
     * @Route("/{id}")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            /** @var Attribute $entity */
            $entity = $em->getRepository('EcommerceBundle:Attribute')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Attribute entity.');
            }

            $em->remove($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add('info', 'attribute.deleted');
        }

        return $this->redirect($this->generateUrl('ecommerce_attribute_index'));
    }

    /**
     * Sorts a list of attributes.
     *
     * @param Request $request
     * @param int     $categoryId
     *
     * @throws NotFoundHttpException
     * @return array|Response
     *
     * @Route("/category/{categoryId}/sort")
     * @Method({"GET", "POST"})
     * @Template
     */
    public function sortAction(Request $request, $categoryId)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Category $category */
        $category = $em->getRepository('EcommerceBundle:Category')->find($categoryId);

        if (!$category) {
            throw $this->createNotFoundException('Unable to find Category entity.');
        }

        if ($request->isXmlHttpRequest()) {
            $this->get('admin_manager')->sort('EcommerceBundle:Attribute', $request->get('values'));

            return new Response(0, 200);
        }

        $attributes = $em->getRepository('EcommerceBundle:Attribute')->findBy(
            array('category' => $category),
            array('order' => 'asc')
        );

        return array(
            'attributes' => $attributes,
            'category' => $category
        );
    }

    /**
     * Set a list of attributes as filtrable.
     *
     * @param Request $request
     * @param int     $id
     *
     * @throws NotFoundHttpException
     * @return Response
     *
     * @Route("/{id}/toggle-filtrable")
     * @Method("POST")
     * @Template
     */
    public function toggleFiltrableAction(Request $request, $id)
    {
        if (false === $request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $isFiltrable = $this->get('admin_manager')->toggleFiltrable('EcommerceBundle:Attribute', $id);

        return new Response(intval($isFiltrable), 200);
    }

    /**
     * Creates a form to delete a Attribute entity by id.
     *
     * @param int $id The entity id
     *
     * @return Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm();
    }
}
