<?php

namespace Rz\NewsBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use Rz\NewsBundle\Admin\PostAdmin;


class PostAdminController extends Controller
{

    /**
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        if ($collection = $this->get('request')->get('collectionId')) {
            $currentCollection = $this->getCollectiontManager()->find($collection);
        } else {
            $currentCollection = $this->getCollectiontManager()->findOneBy(array('slug'=>$this->get('rz_news.pool')->getDefaultCollection()));
        }

        $context = $this->getContextManager()->find(PostAdmin::POST_DEFAULT_CONTEXT);

        if (!$currentCollection) {
            $collections = $this->getCollectiontManager()->findBy(array('context'=>$context->getId()));
            $currentCollection = array_shift($collections);
        } else {
            $collections = $this->getCollectiontManager()->findAllExcept(array('id'=>$currentCollection,'context'=>$context->getId()));
        }

        $datagrid = $this->admin->getDatagrid();


        if ($this->admin->getPersistentParameter('collectionId')) {
            $datagrid->setValue('collection', ChoiceType::TYPE_EQUAL, $this->admin->getPersistentParameter('collectionId'));
        } else {
            $datagrid->setValue('collection', ChoiceType::TYPE_EQUAL, $currentCollection->getId());
        }

        $formView = $datagrid->getForm()->createView();

        // set the theme for the current Admin Form
        $this->get('twig')->getExtension('form')->renderer->setTheme($formView, $this->admin->getFilterTheme());

        return $this->render($this->admin->getTemplate('list'), array(
            'action'     => 'list',
            'form'       => $formView,
            'datagrid'   => $datagrid,
            'csrf_token' => $this->getCsrfToken('sonata.batch'),
            'current_collection'  => $currentCollection,
            'collections'         =>$collections,
        ));
    }


    public function getCollectiontManager() {
        return $this->get('sonata.classification.manager.collection');
    }

    public function getContextManager() {
        return $this->get('sonata.classification.manager.context');
    }
}