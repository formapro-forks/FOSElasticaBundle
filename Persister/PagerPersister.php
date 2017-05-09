<?php

namespace FOS\ElasticaBundle\Persister;

use FOS\ElasticaBundle\Provider\Indexable;
use Pagerfanta\Pagerfanta;
use Elastica\Exception\Bulk\ResponseException as BulkResponseException;

class PagerPersister implements PagerPersisterInterface
{
    /**
     * @var Indexable
     */
    private $indexable;

    /**
     * @param Indexable $indexable
     */
    public function __construct(Indexable $indexable)
    {
        $this->indexable = $indexable;
    }

    /**
     * {@inheritdoc}
     */
    public function insert(Pagerfanta $pager, ObjectPersisterInterface $objectPersister, \Closure $loggerClosure = null, array $options = array())
    {
        $nbObjects = $pager->getNbResults();

        $page = $pager->getCurrentPage();
        for(;$page <= $pager->getNbPages(); $page++) {
            $pager->setCurrentPage($page);

            $sliceSize = $options['batch_size'];

            try {
                $objects = $pager->getCurrentPageResults();
                $sliceSize = count($objects);
                $objects = $this->filterObjects($options, $objects);

                if (!empty($objects)) {
                    $objectPersister->insertMany($objects);
                }
            } catch (BulkResponseException $e) {
                if (!$options['ignore_errors']) {
                    throw $e;
                }

                if (null !== $loggerClosure) {
                    $loggerClosure(
                        $options['batch_size'],
                        $nbObjects,
                        sprintf('<error>%s</error>', $e->getMessage())
                    );
                }
            }

            if (null !== $loggerClosure) {
                $loggerClosure($sliceSize, $nbObjects);
            }

            usleep($options['sleep']);
        }
    }

    /**
     * Filters objects away if they are not indexable.
     *
     * @param array $options
     * @param array $objects
     * @return array
     */
    protected function filterObjects(array $options, array $objects)
    {
        if ($options['skip_indexable_check']) {
            return $objects;
        }

        $index = $options['indexName'];
        $type = $options['typeName'];

        $return = array();
        foreach ($objects as $object) {
            if (!$this->indexable->isObjectIndexable($index, $type, $object)) {
                continue;
            }

            $return[] = $object;
        }

        return $return;
    }
}
