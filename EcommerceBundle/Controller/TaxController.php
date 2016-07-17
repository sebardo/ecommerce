<?php

namespace EcommerceBundle\Controller;

use Doctrine\ORM\Query;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use EcommerceBundle\Entity\Tax;
use EcommerceBundle\Form\TaxType;

/**
 * Tax controller.
 *
 * @Route("/admin/taxes")
 */
class TaxController extends Controller
{
    /**
     * Lists all Tax entities.
     *
     * @return array
     *
     * @Route("/")
     * @Method("GET")
     * @Template("EcommerceBundle:Tax:index.html.twig")
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * Returns a list of Tax entities in JSON format.
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
        $jsonList->setRepository($em->getRepository('EcommerceBundle:Tax'));

        $response = $jsonList->get();

        return new JsonResponse($response);
    }

    /**
     * Creates a new Tax entity.
     *
     * @param Request $request The request
     *
     * @return array|RedirectResponse
     *
     * @Route("/")
     * @Method("POST")
     * @Template("EcommerceBundle:Tax:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new Tax();
        $form = $this->createForm(new TaxType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'tax.created');

            return $this->redirect($this->generateUrl('ecommerce_tax_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new Category entity.
     *
     * @return array
     *
     * @Route("/new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Tax();
        $form   = $this->createForm(new TaxType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }
    
    /**
     * Finds and displays an Tax entity.
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
        /** @var Tax $entity */
        $entity = $em->getRepository('EcommerceBundle:Tax')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tax entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        
        return array(
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }
    
    
    /**
     * Displays a form to edit an existing Feature entity.
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

        /** @var Feature $entity */
        $entity = $em->getRepository('EcommerceBundle:Tax')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tax entity.');
        }

        $editForm = $this->createForm(new TaxType(), $entity);
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
     * @Template("AdminBundle:Tax:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Tax $entity */
        $entity = $em->getRepository('EcommerceBundle:Tax')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tax entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new TaxType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'tax.edited');

            return $this->redirect($this->generateUrl('ecommerce_tax_show', array('id' => $id)));
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
            $entity = $em->getRepository('EcommerceBundle:Tax')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Tax entity.');
            }

            $em->remove($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add('info', 'tax.deleted');
        }

        return $this->redirect($this->generateUrl('ecommerce_tax_index'));
    }
    
    /**
     * Creates a form to delete a Feature entity by id.
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
