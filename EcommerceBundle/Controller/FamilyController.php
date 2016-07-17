<?php

namespace EcommerceBundle\Controller;

use EcommerceBundle\Entity\Family;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use EcommerceBundle\Form\FamilyType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Family controller.
 *
 * @Route("/admin/families")
 */
class FamilyController extends Controller
{
    /**
     * Lists all Family entities.
     *
     * @return array
     *
     * @Route("/")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * Returns a list of Family entities in JSON format.
     *
     * @return JsonResponse
     *
     * @Route("/list.{_format}", requirements={ "_format" = "json" }, defaults={ "_format" = "json" })
     * @Method("GET")
     */
    public function listJsonAction()
    {
        $em = $this->getDoctrine()->getManager();

        /** @var \Kitchenit\AdminBundle\Services\DataTables\JsonList $jsonList */
        $jsonList = $this->get('json_list');
        $jsonList->setRepository($em->getRepository('EcommerceBundle:Family'));

        $response = $jsonList->get();

        return new JsonResponse($response);
    }

    /**
     * Creates a new Family entity.
     *
     * @param Request $request The request
     *
     * @return array|RedirectResponse
     *
     * @Route("/")
     * @Method("POST")
     * @Template("AdminBundle:Family:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new Family();
        $form = $this->createForm(new FamilyType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'family.created');

            return $this->redirect($this->generateUrl('ecommerce_family_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new Family entity.
     *
     * @return array
     *
     * @Route("/new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Family();
        $form   = $this->createForm(new FamilyType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Family entity.
     *
     * @param int $id The entity id
     *
     * @throws NotFoundHttpException
     * @return array
     *
     * @Route("/{id}", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Family $entity */
        $entity = $em->getRepository('EcommerceBundle:Family')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Family entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Family entity.
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

        /** @var Family $entity */
        $entity = $em->getRepository('EcommerceBundle:Family')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Family entity.');
        }

        $editForm = $this->createForm(new FamilyType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Family entity.
     *
     * @param Request $request The request
     * @param int     $id      The entity id
     *
     * @throws NotFoundHttpException
     * @return array|RedirectResponse
     *
     * @Route("/{id}", requirements={"id" = "\d+"})
     * @Method("PUT")
     * @Template("AdminBundle:Family:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Family $entity */
        $entity = $em->getRepository('EcommerceBundle:Family')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Family entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new FamilyType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'family.edited');

            return $this->redirect($this->generateUrl('ecommerce_family_show', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Family entity.
     *
     * @param Request $request The request
     * @param int     $id      The entity id
     *
     * @throws NotFoundHttpException
     * @return RedirectResponse
     *
     * @Route("/{id}", requirements={"id" = "\d+"})
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            
            $em = $this->getDoctrine()->getManager();
            /** @var Family $entity */
            $entity = $em->getRepository('EcommerceBundle:Family')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Family entity.');
            }

            $em->remove($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add('info', 'family.deleted');
        }

        return $this->redirect($this->generateUrl('ecommerce_family_index'));
    }

    /**
     * Sorts a list of families.
     *
     * @param Request $request
     *
     * @return array|Response
     *
     * @Route("/sort")
     * @Method({"GET", "POST"})
     * @Template
     */
    public function sortAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $this->get('admin_manager')->sort('EcommerceBundle:Family', $request->get('values'));

            return new Response(0, 200);
        }

        $em = $this->getDoctrine()->getManager();

        $families = $em->getRepository('EcommerceBundle:Family')->findBy(array(), array('order' => 'asc'));

        return array(
            'families' => $families
        );
    }

    /**
     * Creates a form to delete a Family entity by id.
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
