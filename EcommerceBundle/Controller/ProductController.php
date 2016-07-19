<?php

namespace EcommerceBundle\Controller;

use EcommerceBundle\Form\ProductRelatedType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use EcommerceBundle\Entity\Product;
use EcommerceBundle\Form\ProductType;
use EcommerceBundle\Form\ProductAttributesType;
use EcommerceBundle\Form\ProductFeaturesType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use CoreBundle\Entity\Image;

/**
 * Product controller.
 *
 * @Route("/admin/products")
 */
class ProductController extends Controller
{
    /**
     * Lists all Product entities.
     *
     * @return array
     *
     * @Route("/")
     * @Method("GET")
     * @Template("EcommerceBundle:Product:index.html.twig")
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * Returns a list of Product entities in JSON format.
     *
     * @return JsonResponse
     *
     * @Route("/list.{_format}", requirements={ "_format" = "json" }, defaults={ "_format" = "json" })
     * @Method("GET")
     */
    public function listJsonAction()
    {
        $em = $this->getDoctrine()->getManager();
        $adminManager = $this->get('admin_manager');
        /** @var \AdminBundle\Services\DataTables\JsonList $jsonList */
        $jsonList = $this->get('json_list');
        $jsonList->setRepository($em->getRepository('EcommerceBundle:Product'));
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        if ($user->isGranted('ROLE_USER')) {
            $jsonList->setActor($user);
        }
        
        $response = $jsonList->get();

        return new JsonResponse($response);
    }

    /**
     * Returns a list of Actor entities in JSON format.
     *
     * @return JsonResponse
     *
     * @Route("/stats/{id}/{from}/{to}", requirements={ "_format" = "json" }, defaults={ "_format" = "json" })
     * @Method("GET")
     */
    public function statsAction($id, $from, $to)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Actor $entity */
        $entity = $em->getRepository('EcommerceBundle:Product')->find($id);
        
        /** @var FrontManager $frontManager */
        $adminManager =  $this->container->get('admin_manager');
        $stats = $adminManager->getProductStats($entity, $from, $to);

        $label = array();
        $visits = array();
        foreach ($stats as  $stat) {
            $label[] = $stat['day'];
            $visits[] = $stat['visits'];
        }
        
        $returnValues = new \stdClass();
        $returnValues->count = count($stats);
        $returnValues->labels = implode(',', $label);
        $returnValues->visits = implode(',', $visits);

        return new JsonResponse($returnValues);
    }
    
    /**
     * Creates a new Category entity.
     *
     * @Route("/new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction(Request $request)
    {
        $formConfig = array();
        $user = $this->container->get('security.token_storage')->getToken()->getUser();  
        if ($user->isGranted('ROLE_USER')) { 
            $formConfig['actor'] = $user; 
            //disable notification
            $admin = $em->getRepository('CoreBundle:Actor')->find(1);//admin
            $notificationManager = $this->container->get('notification_manager');
            $notificationManager->disableNotificationByDetail(
                    $admin, //admin
                    $user,//current user
                    'add_product', 
                    '"actor":'.$user->getId().'}'
                );
        }
        $entity = new Product();
        $form = $this->createForm('EcommerceBundle\Form\ProductType', $entity, $formConfig);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            
            if($entity->getActor() instanceof Actor){
                //send emails to admin and actor
                $this->get('core.mailer')->sendActorNewProduct($entity);
            }
            $this->get('session')->getFlashBag()->add('success', 'product.created');

            return $this->redirectToRoute('ecommerce_product_show', array('id' => $entity->getId()));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }
    
     /**
     * Finds and displays a Category entity.
     *
     * @Route("/{id}")
     * @Method("GET")
     * @Template()
     */
    public function showAction(Product $product)
    {
        $deleteForm = $this->createDeleteForm($product);

        //disable notification
        $em = $this->getDoctrine()->getManager();
        $admin = $em->getRepository('CoreBundle:Actor')->find(1);//admin
        $notificationManager = $this->container->get('notification_manager');
        $notificationManager->disableNotificationByDetail(
                $admin, //current user
                $admin,//admin
                'new_product', 
                '"product":'.$product->getId().'}'
                );
        
        return array(
            'entity' => $product,
            'delete_form' => $deleteForm->createView(),
        );
    }
    
     /**
     * Displays a form to edit an existing Product entity.
     *
     * @Route("/{id}/edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction(Request $request, Product $product)
    {
        //access control
        $user = $this->container->get('security.token_storage')->getToken()->getUser();  
        if ($user->isGranted('ROLE_USER') && $product->getActor()->getId() != $user->getId()) {
            return $this->redirect($this->generateUrl('ecommerce_product_index'));
        }
       
        $formConfig = array();
        if ($user->isGranted('ROLE_USER')) { $formConfig['actor'] = $user; }
        $deleteForm = $this->createDeleteForm($product);

        $editForm = $this->createForm('EcommerceBundle\Form\ProductType', $product, $formConfig);
        $attributesForm = $this->createForm('EcommerceBundle\Form\ProductAttributesType', $product, array('category' => $product->getCategory()->getId()));
        $featuresForm = $this->createForm('EcommerceBundle\Form\ProductFeaturesType', $product, array('category' => $product->getCategory()->getId()));
        $relatedProductsForm = $this->createForm('EcommerceBundle\Form\ProductRelatedType', $product);

        if($request->getMethod('POST')){
            $redirectParams = array('id' => $product->getId());

            if ($request->request->has('product_attributes')) {
                // attributes were submitted
                $editForm = $attributesForm;
                $redirectParams = array_merge($redirectParams, array('attributes' => 1));
            } else if ($request->request->has('product_features')) {
                // features were submitted
                $editForm = $featuresForm;
                $redirectParams = array_merge($redirectParams, array('features' => 1));
            } else if ($request->request->has('product_related')) {
                // related products were submitted
                $editForm = $relatedProductsForm;
                $redirectParams = array_merge($redirectParams, array('related' => 1));
            }
            $editForm->handleRequest($request);

            if ($editForm->isSubmitted() && $editForm->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($product);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', 'product.edited');
                
                return $this->redirectToRoute('ecommerce_product_show', $redirectParams);
            }
        }
        
        return array(
            'entity'          => $product,
            'edit_form'       => $editForm->createView(),
            'attributes_form' => $attributesForm->createView(),
            'features_form'   => $featuresForm->createView(),
            'related_form'    => $relatedProductsForm->createView(),
            'delete_form'     => $deleteForm->createView(),
        );
    }
    
    /**
     * @Route("/dashboard-product")
     * @Template()
     */
    public function dashboardAction()
    {
        $em = $this->getDoctrine()->getManager();
        $totalVisits = $em->getRepository('EcommerceBundle:Product')->countTotalStatsVisits();
        $totalActorVisited = $em->getRepository('EcommerceBundle:Product')->countProductVisited();
        $totalActors = $em->getRepository('EcommerceBundle:Product')->countTotal();
        $bounceRate = $em->getRepository('EcommerceBundle:Product')->bounceRate();
        $stats = $em->getRepository('EcommerceBundle:Product')->countProductStatsLine();
        $report = $em->getRepository('EcommerceBundle:Product')->countVisitsByProduct();
        $cuponCount = $em->getRepository('EcommerceBundle:Transaction')->getCountCupon();
        $bestSeller = $em->getRepository('EcommerceBundle:Transaction')->getBestSellerProducts(1);
        $totalAmount = $em->getRepository('EcommerceBundle:Transaction')->totalAmountCharged();
        $totalTransactions = $em->getRepository('EcommerceBundle:Transaction')->countTotal();
        
        
        return array(
            'totalVisits' => $totalVisits,
            'totalProductVisited' => $totalActorVisited,
            'totalProducts' => $totalActors,
            'bounceRate' => $bounceRate,
            'stats' => $stats,
            'report' => $report,
            'cuponCount' => $cuponCount,
            'bestSeller' => $bestSeller,
            'totalAmount' => $totalAmount,
            'totalTransactions' => $totalTransactions
        );
   }
 
   /**
     * Deletes a Product entity.
     *
     * @Route("/{id}")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Product $product)
    {
        $form = $this->createDeleteForm($product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $em = $this->getDoctrine()->getManager();
            $em->remove($product);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('info', 'product.deleted');
        }

        return $this->redirectToRoute('ecommerce_product_index');
    }

   /**
     * Creates a form to delete a Product entity.
     *
     * @param Product $product The Product entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Product $product)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('ecommerce_product_delete', array('id' => $product->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
    
    
    /**
     * Manages a product image
     *
     * @return array
     *
     * @Route("/{id}-{slug}/image")
     */
    public function manageImage()
    {
        $this->get('upload_handler');

        return new Response();
    }

    /**
     * Manages a product image
     *
     * @return array
     *
     * @Route("/update/image")
     */
    public function updateImage(Request $request)
    {
        $fileName = $request->query->get('file');
        $type = $request->query->get('type');
        $value = $request->query->get('value');
      
        /** @var Image $entity */
        $qb = $this->getDoctrine()->getManager()->getRepository('CoreBundle:Image')
                ->createQueryBuilder('i');
        $image = $qb->where($qb->expr()->like('i.path', ':path'))
            ->setParameter('path','%'.$fileName.'%')
            ->getQuery()
            ->getOneOrNullResult();

//        $image = $this->getDoctrine()->getManager()->getRepository('CoreBundle:Image')->findOneBy(array('path' => $fileName));
       
        if (!$image) {
            throw new NotFoundHttpException('Unable to find Image entity.');
        }
        

        if($type == 'title') $image->setTitle($value);
        if($type == 'alt') $image->setAlt($value);
        
        $this->getDoctrine()->getManager()->flush();
            

        return new JsonResponse(array('success' => true));
    }
    
    
}
