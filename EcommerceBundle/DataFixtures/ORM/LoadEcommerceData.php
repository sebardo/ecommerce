<?php
namespace EcommerceBundle\DataFixtures\ORM;

use CoreBundle\DataFixtures\SqlScriptFixture;
use EcommerceBundle\Entity\Family;
use EcommerceBundle\Entity\Category;
use EcommerceBundle\Entity\Attribute;
use EcommerceBundle\Entity\Feature;
use EcommerceBundle\Entity\AttributeValue;
use EcommerceBundle\Entity\FeatureValue;
use EcommerceBundle\Entity\Brand;
use EcommerceBundle\Entity\Product;
use EcommerceBundle\Entity\Transaction;
use CoreBundle\Entity\Actor;
use EcommerceBundle\Entity\Invoice;
use EcommerceBundle\Entity\ProductPurchase;
use EcommerceBundle\Entity\Address;
use EcommerceBundle\Entity\Delivery;
use EcommerceBundle\Entity\BrandModel;

class LoadEcommerceData extends SqlScriptFixture
{
    public function createFixtures()
    {
        $core = $this->container->getParameter('core');
        if($core['fixture_data'])
        {
            //Create Families
            $family = new Family();
            $family->setName('Family test');
            $family->setDescription('Description family test');
            $family->setMetaTitle('Meta titlte family test 1');
            $family->setMetaDescription('Meta description family test 1');

            //Create Categories
            $category = new Category();
            $category->setFamily($family);
            $category->setName('Category test');
            $category->setDescription('Description category test');
            $category->setMetaTitle('Meta titlte category test 1');
            $category->setMetaDescription('Meta description category test 1');

            $category2 = new Category();
            $category2->setFamily($family);
            $category2->setName('Category test 2');
            $category2->setDescription('Description category test 2');
            $category2->setMetaTitle('Meta titlte category test 2');
            $category2->setMetaDescription('Meta description category test 2');

            //Create Attributes
            $attr = new Attribute();
            $attr->setCategory($category2);
            $attr->setName('Attribute value');

            $attrValue = new AttributeValue();
            $attrValue->setAttribute($attr);
            $attrValue->setName('Yes');

            $attrValue2 = new AttributeValue();
            $attrValue2->setAttribute($attr);
            $attrValue2->setName('No');

            //Creates Features
            $feature = new Feature();
            $feature->setCategory($category2);
            $feature->setName('Feature value');

            $featureValue = new FeatureValue();
            $featureValue->setFeature($feature);
            $featureValue->setName('Yes');

            $featureValue2 = new FeatureValue();
            $featureValue2->setFeature($feature);
            $featureValue2->setName('No');

            //Create Brands
            $brand = new Brand();
            $brand->setName('Brand test');
            $brand->setAvailable(true);

            $brand2 = new Brand();
            $brand2->setName('Brand test 2');
            $brand2->setAvailable(true);

            $brand3 = new Brand();
            $brand3->setName('Brand test 3');
            $brand3->setAvailable(false);
            
            
            //Create Brands
            $brandModel = new BrandModel();
            $brandModel->setName('Model test');
            $brandModel->setAvailable(true);
            $brandModel->setBrand($brand);

            $brandModel2 = new BrandModel();
            $brandModel2->setName('Model test 2');
            $brandModel2->setAvailable(true);
            $brandModel->setBrand($brand2);


            //Create Products
            $product = new Product();
            $product->setName('Product test 1');
            $product->setPrice(0.84);
            $product->setBrand($brand);
            $product->setModel($brandModel);
            $product->setCategory($category);
            $product->setActive(true);
            $product->setAvailable(true);
            $product->setHighlighted(true);
            $product->setDescription('Description product test 1 for testing.');
            $product->setExcerpt('Excerpt product test 1 for testing.');
            $product->setReference(uniqid());
            $product->setMetaTitle('Meta titlte test 1');
            $product->setMetaDescription('Meta description test 1');
            $product->setStock(7);
            $product->addAttributeValue($attrValue2);
            $product->addFeatureValue($featureValue);
            $product->setFreeTransport(true);
            $product->setFinalShot(true);
            $product->setTechnicalDetails('Technical details test.');

            $product2 = new Product();
            $product2->setName('Product test 2');
            $product2->setPrice(1.14);
            $product2->setBrand($brand2);
            $product->setModel($brandModel2);
            $product2->setCategory($category2);
            $product2->setActive(true);
            $product2->setAvailable(true);
            $product2->setHighlighted(true);
            $product2->setDescription('Description product test 2 for testing.');
            $product2->setExcerpt('Excerpt product test 2 for testing.');
            $product2->setReference(uniqid());
            $product2->setMetaTitle('Meta titlte test 2');
            $product2->setMetaDescription('Meta description test 2');
            $product2->setStock(12);
            $product2->addAttributeValue($attrValue);
            $product2->addFeatureValue($featureValue2);
            $product2->addRelatedProduct($product);
            $product2->setFreeTransport(true);
            $product2->setFinalShot(true);
            $product2->setTechnicalDetails('Technical details test 2.');

            $product3 = new Product();
            $product3->setName('Product test 3');
            $product3->setPrice(0.14);
            $product3->setBrand($brand);
            $product->setModel($brandModel);
            $product3->setCategory($category);
            $product3->setActive(true);
            $product3->setAvailable(true);
            $product3->setHighlighted(true);
            $product3->setDescription('Description product test 3 for testing.');
            $product3->setExcerpt('Excerpt product test 3 for testing.');
            $product3->setReference(uniqid());
            $product3->setMetaTitle('Meta titlte test 3');
            $product3->setMetaDescription('Meta description test 3');
            $product3->setStock(12);
            $product3->addAttributeValue($attrValue);
            $product3->addFeatureValue($featureValue);
            $product3->addRelatedProduct($product);
            $product3->addRelatedProduct($product2);
            $product3->setFreeTransport(true);
            $product3->setFinalShot(true);
            $product3->setTechnicalDetails('Technical details test 3.');

            //Create a sale (create all entities needed)
            $actor = $this->getManager()->getRepository('CoreBundle:Actor')->findOneByUsername('test');
            $actor2 = $this->getManager()->getRepository('CoreBundle:Actor')->findOneByUsername('test2');
            $country = $this->getManager()->getRepository('CoreBundle:Country')->find('es');
            $state = $this->getManager()->getRepository('CoreBundle:State')->findOneByName('Barcelona');

            $core = $this->container->getParameter('core');

            $transaction = new Transaction();
            $transaction->setTransactionKey(uniqid());
            $transaction->setStatus(Transaction::STATUS_PENDING);
            $transaction->setTotalPrice(2.12);
            $transaction->setVat($core['company']['vat']);
            $transaction->setPaymentMethod(Transaction::PAYMENT_METHOD_PAYPAL);
            $transaction->setActor($actor);
            $transaction->setCreated(new \DateTime('now'));

            $productPurchase = new ProductPurchase();
            $productPurchase->setProduct($product);
            $productPurchase->setQuantity(1);
            $productPurchase->setBasePrice(2.12);
            $productPurchase->setTotalPrice(2.12);
            $productPurchase->setTransaction($transaction);
            $productPurchase->setCreated(new \DateTime('now'));
            $productPurchase->setReturned(false);
            $productPurchase->setAssembly(false);

            
            $address = new Address();
            $address->setAddress('Test address 113');
            $address->setPostalCode('08349');
            $address->setCity('Cabrera de Mar');
            $address->setState($state);
            $address->setCountry($country);
            $address->setPhone('123123123');
            $address->setPreferredSchedule(1);
            $address->setContactPerson('Testo Ramon');
            $address->setForBilling(true);
            $address->setDni('33956669K');
            $address->setActor($actor);

            $address2 = new Address();
            $address2->setAddress('Test address 112');
            $address2->setPostalCode('08349');
            $address2->setCity('Cabrera de Mar');
            $address2->setState($state);
            $address2->setCountry($country);
            $address2->setPhone('123123121');
            $address2->setPreferredSchedule(1);
            $address2->setContactPerson('Test User');
            $address2->setForBilling(true);
            $address2->setDni('30110048N');
            $address2->setActor($actor2);

            $invoice = new Invoice();
            $invoice->setInvoiceNumber(rand(1600, 2000));
            $invoice->setTransaction($transaction);
            $invoice->setFullName('full name test invoice');
            $invoice->setCreated(new \DateTime('now'));
            $invoice->setDni('33956669K');
            $invoice->setAddressInfo($address);

            $delivery = new Delivery();
            $delivery->setFullName('Tes full name');
            $delivery->setPhone('123123123');
            $delivery->setPreferredSchedule(1);
            $delivery->setExpenses(5.5);
            $delivery->setExpensesType('by_percentage');
            $delivery->setTransaction($transaction);
            $delivery->setDni('33956669K');
            $delivery->setAddressInfo($address);
            $delivery->setDeliveryPhone('123123123');
            $delivery->setDeliveryPreferredSchedule(1);
            $delivery->setDeliveryDni('33956669K');
            $delivery->setDeliveryAddress('Address test 113');
            $delivery->setDeliveryPostalCode('08349');
            $delivery->setDeliveryCity('Cabrera de Mar');
            $delivery->setDeliveryProvince(10);
            $delivery->setDeliveryCountry(1);

            $this->getManager()->persist($family);
            $this->getManager()->persist($category);
            $this->getManager()->persist($category2);
            $this->getManager()->persist($attr);
            $this->getManager()->persist($attrValue);
            $this->getManager()->persist($attrValue2);
            $this->getManager()->persist($feature);
            $this->getManager()->persist($featureValue);
            $this->getManager()->persist($featureValue2);
            $this->getManager()->persist($brand);
            $this->getManager()->persist($brand2);
            $this->getManager()->persist($brand3);
            $this->getManager()->persist($brandModel);
            $this->getManager()->persist($brandModel2);
            $this->getManager()->persist($product);
            $this->getManager()->persist($product2);
            $this->getManager()->persist($product3);

            $this->getManager()->persist($transaction);
            $this->getManager()->persist($productPurchase);
            $this->getManager()->persist($address);
            $this->getManager()->persist($address2);
            $this->getManager()->persist($invoice);
            $this->getManager()->persist($delivery);

            $this->getManager()->flush();
        }
        
        
    }

    public function getOrder()
    {
        return 2; // the order in which fixtures will be loaded
    }
}
