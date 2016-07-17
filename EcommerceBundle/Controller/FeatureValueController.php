<?php

namespace EcommerceBundle\Controller;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use EcommerceBundle\Entity\Feature;
use EcommerceBundle\Entity\FeatureValue;
use EcommerceBundle\Form\FeatureValueType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * FeatureValue controller.
 *
 * @Route("/admin/features/{featureId}/values")
 */
class FeatureValueController extends Controller
{
    /**
     * Lists all FeatureValue entities.
     *
     * @param int $featureId The feature id
     *
     * @throws NotFoundHttpException
     * @return array
     *
     * @Route("/")
     * @Method("GET")
     * @Template("EcommerceBundle:FeatureValue:index.html.twig")
     */
    public function indexAction($featureId)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Feature $feature */
        $feature = $em->getRepository('EcommerceBundle:Feature')->find($featureId);

        if (!$feature) {
            throw $this->createNotFoundException('Unable to find Feature entity.');
        }

        return array(
            'feature'    => $feature,
        );
    }

    /**
     * Returns a list of FeatureValue entities in JSON format.
     *
     * @param int $featureId The feature id
     *
     * @return JsonResponse
     *
     * @Route("/list.{_format}", requirements={ "_format" = "json" }, defaults={ "_format" = "json" })
     * @Method("GET")
     */
    public function listJsonAction($featureId)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var \Kitchenit\AdminBundle\Services\DataTables\JsonList $jsonList */
        $jsonList = $this->get('json_list');
        $jsonList->setRepository($em->getRepository('EcommerceBundle:FeatureValue'));
        $jsonList->setEntityId($featureId);

        $response = $jsonList->get();

        return new JsonResponse($response);
    }

    /**
     * Creates a new FeatureValue entity.
     *
     * @param Request $request   The request
     * @param int     $featureId The feature id
     *
     * @throws NotFoundHttpException
     * @return array|RedirectResponse
     *
     * @Route("/")
     * @Method("POST")
     * @Template("EcommerceBundle:FeatureValue:new.html.twig")
     */
    public function createAction(Request $request, $featureId)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Feature $feature */
        $feature = $em->getRepository('EcommerceBundle:Feature')->find($featureId);

        if (!$feature) {
            throw $this->createNotFoundException('Unable to find Feature entity.');
        }

        $entity  = new FeatureValue();
        $form = $this->createForm(new FeatureValueType(), $entity);
        $entity->setFeature($feature);

        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'value.created');

            return $this->redirect($this->generateUrl('ecommerce_featurevalue_show', array('featureId' => $featureId, 'id' => $entity->getId())));
        }

        return array(
            'entity'    => $entity,
            'feature' => $feature,
            'form'      => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new FeatureValue entity.
     *
     * @param int $featureId The feature id
     *
     * @throws NotFoundHttpException
     * @return array
     *
     * @Route("/new")
     * @Method("GET")
     * @Template()
     */
    public function newAction($featureId)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Feature $feature */
        $feature = $em->getRepository('EcommerceBundle:Feature')->find($featureId);

        if (!$feature) {
            throw $this->createNotFoundException('Unable to find Feature entity.');
        }

        $entity = new FeatureValue();
        $form   = $this->createForm(new FeatureValueType(), $entity);

        return array(
            'entity'      => $entity,
            'feature'   => $feature,
            'form'        => $form->createView(),
        );
    }

    /**
     * Finds and displays a FeatureValue entity.
     *
     * @param int $featureId The feature id
     * @param int $id        The entity id
     *
     * @throws NotFoundHttpException
     * @return array
     *
     * @Route("/{id}")
     * @Method("GET")
     * @Template()
     */
    public function showAction($featureId, $id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Feature $feature */
        $feature = $em->getRepository('EcommerceBundle:Feature')->find($featureId);

        if (!$feature) {
            throw $this->createNotFoundException('Unable to find Feature entity.');
        }

        /** @var FeatureValue $entity */
        $entity = $em->getRepository('EcommerceBundle:FeatureValue')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find FeatureValue entity.');
        }

        $deleteForm = $this->createDeleteForm($featureId, $id);

        return array(
            'entity'      => $entity,
            'feature'   => $feature,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing FeatureValue entity.
     *
     * @param int $featureId The feature id
     * @param int $id        The entity id
     *
     * @throws NotFoundHttpException
     * @return array
     *
     * @Route("/{id}/edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($featureId, $id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Feature $feature */
        $feature = $em->getRepository('EcommerceBundle:Feature')->find($featureId);

        if (!$feature) {
            throw $this->createNotFoundException('Unable to find Feature entity.');
        }

        /** @var FeatureValue $entity */
        $entity = $em->getRepository('EcommerceBundle:FeatureValue')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find FeatureValue entity.');
        }

        $editForm = $this->createForm(new FeatureValueType(), $entity);
        $deleteForm = $this->createDeleteForm($featureId, $id);

        return array(
            'entity'      => $entity,
            'feature'   => $feature,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing FeatureValue entity.
     *
     * @param Request $request   The request
     * @param int     $featureId The feature id
     * @param int     $id        The entity id
     *
     * @throws NotFoundHttpException
     * @return array|RedirectResponse
     *
     * @Route("/{id}")
     * @Method("PUT")
     * @Template("EcommerceBundle:Feature:edit.html.twig")
     */
    public function updateAction(Request $request, $featureId, $id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Feature $feature */
        $feature = $em->getRepository('EcommerceBundle:Feature')->find($featureId);

        if (!$feature) {
            throw $this->createNotFoundException('Unable to find Feature entity.');
        }

        /** @var FeatureValue $entity */
        $entity = $em->getRepository('EcommerceBundle:FeatureValue')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find FeatureValue entity.');
        }

        $deleteForm = $this->createDeleteForm($featureId, $id);
        $editForm = $this->createForm(new FeatureValueType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'value.edited');

            return $this->redirect($this->generateUrl('ecommerce_featurevalue_show', array('featureId' => $featureId, 'id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'feature'   => $feature,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a FeatureValue entity.
     *
     * @param Request $request   The request
     * @param int     $featureId The feature id
     * @param int     $id        The entity id
     *
     * @throws NotFoundHttpException
     * @return RedirectResponse
     *
     * @Route("/{id}")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $featureId, $id)
    {
        $form = $this->createDeleteForm($featureId, $id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            /** @var Feature $feature */
            $feature = $em->getRepository('EcommerceBundle:Feature')->find($featureId);

            if (!$feature) {
                throw $this->createNotFoundException('Unable to find Feature entity.');
            }

            /** @var FeatureValue $entity */
            $entity = $em->getRepository('EcommerceBundle:FeatureValue')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find FeatureValue entity.');
            }

            $em->remove($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add('info', 'value.deleted');
        }

        return $this->redirect($this->generateUrl('ecommerce_featurevalue_index', array('featureId' => $featureId)));
    }

    /**
     * Creates a form to delete a FeatureValue entity by id.
     *
     * @param int $featureId The feature id
     * @param int $id        The entity id
     *
     * @return Form The form
     */
    private function createDeleteForm($featureId, $id)
    {
        return $this->createFormBuilder(array('featureId' => $featureId, 'id' => $id))
            ->add('featureId', 'hidden')
            ->add('id', 'hidden')
            ->getForm();
    }
}
