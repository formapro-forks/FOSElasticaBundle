<?php
namespace FOS\ElasticaBundle\Provider;

use Pagerfanta\Pagerfanta;

interface ProviderV2Interface
{
    /**
     * @param array    $options
     *
     * @return Pagerfanta
     */
    public function provide(array $options = array());
}
