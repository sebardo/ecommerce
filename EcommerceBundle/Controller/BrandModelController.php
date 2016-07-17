<?php

namespace EcommerceBundle\Controller;

use Doctrine\ORM\Query;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use EcommerceBundle\Entity\BrandModel;
use EcommerceBundle\Form\BrandModelType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use EcommerceBundle\Entity\Brand;

/**
 * BrandModel controller.
 *
 * @Route("/admin/models")
 */
class BrandModelController extends Controller
{
    /**
     * Lists all BrandModel entities.
     *
     * @return array
     *
     * @Route("/")
     * @Method("GET")
     * @Template("EcommerceBundle:BrandModel:index.html.twig")
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * Returns a list of BrandModel entities in JSON format.
     *
     * @return JsonResponse
     *
     * @Route("/list.{_format}", requirements={ "_format" = "json" }, defaults={ "_format" = "json" })
     * @Method("GET")
     */
    public function listJsonAction()
    {
        $em = $this->getDoctrine()->getManager();

        /** @var \AdminBundle\Services\DataTables\JsonList $jsonList */
        $jsonList = $this->get('json_list');
        $jsonList->setRepository($em->getRepository('EcommerceBundle:BrandModel'));

        $response = $jsonList->get();

        return new JsonResponse($response);
    }

    /**
     * Creates a new BrandModel entity.
     *
     * @param Request $request The request
     *
     * @return array|RedirectResponse
     *
     * @Route("/")
     * @Method("POST")
     * @Template("EcommerceBundle:BrandModel:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new BrandModel();
        $form = $this->createForm(new BrandModelType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'model.created');

            return $this->redirect($this->generateUrl('ecommerce_brandmodel_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new BrandModel entity.
     *
     * @return array
     *
     * @Route("/new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new BrandModel();
        $form = $this->createForm(new BrandModelType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a BrandModel entity.
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

        /** @var BrandModel $entity */
        $entity = $em->getRepository('EcommerceBundle:BrandModel')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find BrandModel entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing BrandModel entity.
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

        /** @var BrandModel $entity */
        $entity = $em->getRepository('EcommerceBundle:BrandModel')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find BrandModel entity.');
        }

        $editForm = $this->createForm(new BrandModelType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing BrandModel entity.
     *
     * @param Request $request The request
     * @param int     $id      The entity id
     *
     * @throws NotFoundHttpException
     * @return array|RedirectResponse
     *
     * @Route("/{id}")
     * @Method("PUT")
     * @Template("AdminBundle:BrandModel:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var BrandModel $entity */
        $entity = $em->getRepository('EcommerceBundle:BrandModel')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find BrandModel entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new BrandModelType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'model.edited');

            return $this->redirect($this->generateUrl('ecommerce_brandmodel_show', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a BrandModel entity.
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
            /** @var BrandModel $entity */
            $entity = $em->getRepository('EcommerceBundle:BrandModel')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find BrandModel entity.');
            }

            $em->remove($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add('info', 'model.deleted');
        }

        return $this->redirect($this->generateUrl('ecommerce_brandmodel_index'));
    }

    /**
     * Creates a form to delete a BrandModel entity by id.
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
    
    /**
     * Returns a list of BrandModel entities in JSON format.
     *
     * @return JsonResponse
     *
     * @Route("/model/{id}", requirements={ "_format" = "json" }, defaults={ "_format" = "json", "id" = "" })
     * @Method("GET")
     */
    public function modelJsonAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        
        $brand = $em->getRepository('EcommerceBundle:Brand')->find($id);

        if (!$brand instanceof Brand){
            throw $this->createNotFoundException('Unable to find Brand entity.');
        }
            
        $entities = $em->getRepository('EcommerceBundle:BrandModel')->findByBrand($brand);

        $returnValues = array();
        foreach ($entities as $entity) {
            $returnValues[$entity->getId()] =  $entity->getName() ;
        }
        return new JsonResponse($returnValues);
    }
}
