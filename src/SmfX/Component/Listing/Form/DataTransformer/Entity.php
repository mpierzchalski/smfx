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
     * @var array
     */
    private $_managersMapping = [];

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
            $class = get_class($data);
            foreach ($this->_doctrine->getManagers() as $name => $em) {
                /** @var \Doctrine\ORM\EntityManager $em */
                if ($em->getMetadataFactory()->hasMetadataFor($class)) {
                    $this->_managersMapping[$class] = $name;
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function transformForSnapshot($data)
    {
        $class = get_class($data);
        if (!array_key_exists($class, $this->_managersMapping)) {
            throw new \OutOfBoundsException('Undefined class in ManagersMapping index.');
        }
        $entityIdentifierValue = $this->_doctrine
            ->getManager($this->_managersMapping[$class])
            ->getClassMetadata($class)
            ->getIdentifierValues($data);

        return array(
            'manager'    => $this->_managersMapping[$class],
            'class_name' => $class,
            'id'         => $entityIdentifierValue,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function transformFromSnapshot($data)
    {
        $entityManagersName = $data['manager'];
        $class              = $data['class_name'];
        $id                 = $data['id'];
        return $this->_doctrine->getManager($entityManagersName)->find($class, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'entity';
    }

} 