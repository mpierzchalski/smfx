<?php
/** 
 * @package   SmfX
 * @author    Michał Pierzchalski <michal.pierzchalski@gmail.com>
 * @license   MIT
 */

namespace SmfX\Component\Listing\Filter;


use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Registry;
use SmfX\Component\Fixture\Doctrine\Paginate\NativeQueryPaginate;
use SmfX\Component\Fixture\Doctrine\NativeQuery;

class DoctrineFilter extends AdapterAbstract implements AdapterInterface
{
    /**
     * @var Registry
     */
    private $_doctrine;

    /**
     * Constructor
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
    public function getCollectionName()
    {
        return 'smfx_collection.filtered_doctrine';
    }

    /**
     * {@inheritdoc}
     */
    public function setParams(array $params)
    {
        if (!empty($params)) {
            foreach ($params as $param) {
                if (!$param instanceof self) {
                    $this->setParam($param);
                }
            }
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function executeTotalQueryResult($query)
    {
        // TODO: Implement executeTotalQueryResult() method.
    }

    /**
     * {@inheritdoc}
     */
    public function executePaginateQueryResult($query, $hydrator = null)
    {
        if (null === $hydrator) {
            $hydrator = Query::HYDRATE_OBJECT;
        }
        if ($this->getLimit() > 0 && $this->getOffset() >= 0) {
            if ($query instanceof \Doctrine\ORM\NativeQuery) {
                if (null === $this->getTotalQueryResult()) {
                    $this->setTotalQueryResult(
                        NativeQueryPaginate::getTotalQueryResults($query, $this->getCountDistinction())
                    );
                }
                $query = NativeQueryPaginate::getLimitedQuery($query, $this->getLimit(), $this->getOffset());
                return NativeQuery::doExecute($query, $hydrator, $this->getResultMapping());
            } else {
                /** @var \Doctrine\ORM\Query $query */
                $query
                    ->setFirstResult($this->getOffset())
                    ->setMaxResults($this->getLimit());

                $paginator = new Paginator($query);
                if (null === $this->getTotalQueryResult()) {
                    $this->setTotalQueryResult(count($paginator));
                }
                return $paginator->getIterator();
            }
        }
        return $query->getResult($hydrator);
    }

} 