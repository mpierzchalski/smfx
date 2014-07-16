<?php
/**
 * @package   SmfX
 * @author    Michał Pierzchalski <michal.pierzchalski@gmail.com>
 * @license   MIT
 */

namespace SmfX\Bundle\ListingBundle\Twig;


use SmfX\Component\Listing\ListingView;
use Symfony\Component\Form\FormFactory;
use Twig_Environment;

class PaginationHelperExtension extends \Twig_Extension
{
    /**
     * @var Twig_Environment
     */
    private $_environment;

    /**
     * @var FormFactory
     */
    private $_formFactory;

    /**
     * @param FormFactory $formFactory
     */
    public function __construct(FormFactory $formFactory)
    {
        $this->_formFactory = $formFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function initRuntime(Twig_Environment $environment)
    {
        $this->_environment = $environment;
    }


    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'smfx_pagination_bar',
                array($this, 'render'),
                array('is_safe' => array('html'))
            )
        );
    }

    /**
     * Renders pagination bar
     *
     * @param ListingView   $listingView
     * @param boolean       $printLimiter[optional] - default: true
     * @return string
     */
    public function render(ListingView $listingView, $printLimiter = true)
    {
        $limiterView = null;
        if (true === $printLimiter) {
            $limiter = $this->_formFactory->createBuilder('form', null, array(
                'render_fieldset'   => false,
                'attr'              => array(
                    'name'  => 'limiter',
                    'class' => 'form-horizontal',
                )
            ));
            $limiter->add('pageLimit', 'choice', array(
                'label'    => ' ',
                'required' => false,
                'choices'  => $listingView->getLimiterChoices(),
                'horizontal_input_wrapper_class' => 'col-md-5'
            ));
            $limiterForm = $limiter->getForm();
            $limiterForm->get('pageLimit')->setData($listingView->getPageLimit());
            $limiterView = $limiterForm->createView();
        }

        return $this->_environment->render('SmfxListingBundle:Extension:paginationHelper.html.twig', array(
            'limiter'       => $limiterView,
            'firstPage'     => 1,
            'lastPage'      => $listingView->getLastPage(),
            'currentPage'   => $listingView->getCurrentPage(),
            'rowsAmount'    => $listingView->getRowsAmount()
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pagination_helper_extension';
    }

} 