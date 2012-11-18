<?php
namespace Gthm\RdfBundle\Mapper;

/**
 * @author Martin Holzhauer
 */
class MapperFactory
{
    public function get(\Symfony\Component\Routing\Router $router) {

        $cache = new \Doctrine\Common\Cache\ApcCache();
        $reader = new \Doctrine\Common\Annotations\AnnotationReader();
        $debug = true;

        $cachedReader = new \Doctrine\Common\Annotations\CachedReader(
            $reader,
            $cache,
            $debug
        );

        return new JsonLdMapper($cachedReader, $router);
    }
}
