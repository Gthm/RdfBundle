<?php
namespace Gthm\RdfBundle\Mapper;

interface MapperInterface
{
    public function applyRepresentation($entity, $representation);

    public function getRepresentation($entity);
}
