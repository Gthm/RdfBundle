<?php
namespace Gthm\RdfBundle\Annotations;

use Doctrine\ORM\Mapping\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
class Subject implements Annotation
{
    /** @var string */
    public $route;

    /** @var array */
    public $params;
}
