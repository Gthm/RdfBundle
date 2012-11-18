<?php
namespace Gthm\RdfBundle\Annotations;

use Doctrine\ORM\Mapping\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
class Type implements Annotation
{
    /** @var string */
    public $name;
}
