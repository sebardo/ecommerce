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
use EcommerceBundle\Entity\Category;
use EcommerceBundle\Form\SubcategoryType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Subcategory controller.
 *
 * @Route("/admin/categories/{categoryId}/subcategories")
 */
class SubcategoryController extends Controller
{
    /**
     * Lists all subcategories from a Category entity.
     *
     * @param int $categoryId The category id
     *
     * @throws NotFoundHttpException
     * @return array
     *
     * @Route("/")
     * @Method("GET")
     * @Template("EcommerceBundle:Subcategory:index.html.twig")
     */
    public function indexAction($categoryId)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Category $entity */
        $entity = $em->getRepository('EcommerceBundle:Category')->find($categoryId);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Category entity.');
        }

        return array(
            'category' => $entity,
        );
    }

    /**
     * Returns a list of subcategories from a Category entity in JSON format.
     *
     * @param int $categoryId The category id
     *
     * @return JsonResponse
     *
     * @Route("/list.{_format}", requirements={ "_format" = "json" }, defaults={ "_format" = "json" })
     * @Method("GET")
     */
    public function listJsonAction($categoryId)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var \Kitchenit\AdminBundle\Services\DataTables\JsonList $jsonList */
        $jsonList = $this->get('json_list');
        $jsonList->setRepository($em->getRepository('EcommerceBundle:Category'));
        $jsonList->setCategory($categoryId);

        $response = $jsonList->get();

        return new JsonResponse($response);
    }

    /**
     * Creates a new Category entity.
     *
     * @param Request $request    The request
     * @param int     $categoryId The category id
     *
     * @throws NotFoundHttpException
     * @return array|RedirectResponse
     *
     * @Route("/")
     * @Method("POST")
     * @Template("EcommerceBundle:Category:new.html.twig")
     */
    public function createAction(Request $request, $categoryId)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Category $category */
        $category = $em->getRepository('EcommerceBundle:Category')->find($categoryId);

        if (!$category) {
            throw $this->createNotFoundException('Unable to find Category entity.');
        }

        $entity  = new Category();
        $form = $this->createForm(new SubcategoryType(), $entity);
        $entity->setParentCategory($category);

        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'category.created');

            return $this->redirect($this->generateUrl('ecommerce_category_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'category' => $category,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new Category entity.
     *
     * @param int $categoryId The category id
     *
     * @throws NotFoundHttpException
     * @return array
     *
     * @Route("/new")
     * @Method("GET")
     * @Template("EcommerceBundle:Subcategory:new.html.twig")
     */
    public function newAction($categoryId)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Category $category */
        $category = $em->getRepository('EcommerceBundle:Category')->find($categoryId);

        if (!$category) {
            throw $this->createNotFoundException('Unable to find Category entity.');
        }

        $entity = new Category();
        $form   = $this->createForm(new SubcategoryType(), $entity);

        return array(
            'entity' => $entity,
            'category' => $category,
            'form'   => $form->createView(),
        );
    }
}
