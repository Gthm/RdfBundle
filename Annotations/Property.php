<?php
namespace Gthm\RdfBundle\Annotations;

use Doctrine\ORM\Mapping\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class Property implements Annotation
{
    /** @var string */
    public $name;
}
