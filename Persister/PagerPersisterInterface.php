<?php
namespace FOS\ElasticaBundle\Persister;

use Pagerfanta\Pagerfanta;

interface PagerPersisterInterface
{
    /**
     * @param Pagerfanta $pager
     * @param \Closure|null $loggerClosure
     * @param array $options
     *
     * @return void
     */
    public function insert(Pagerfanta $pager, \Closure $loggerClosure = null, array $options = array());
}
