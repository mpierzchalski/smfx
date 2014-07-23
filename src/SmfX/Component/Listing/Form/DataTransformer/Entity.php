<?php
/**
 * @package   SmfX
 * @author    Michał Pierzchalski <michal.pierzchalski@gmail.com>
 * @license   MIT
 */

namespace SmfX\Component\Listing\Form\DataTransformer;


use Doctrine\Bundle\DoctrineBundle\Registry;
use SmfX\Component\Listing\Form\DataTransformerInterface;

class Entity implements DataTransformerInterface
{
    /**
     * @var Registry
     */
    private $_doctrine;

    /**
     * Construct
     *
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->_doctrine = $doctrine;
    }

    /**
     * {@inheritdoc}
     */
    public function valid($data)
    {
        if (is_object($data)) {
            foreach ($this->_doctrine->getManagers() as $em) {
                /** @var \Doctrine\ORM\EntityManager $em */
                var_dump($em->getClassMetadata($data));
            }
        }
        exit;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($data)
    {
        // TODO: Implement transform() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'entity';
    }

} 