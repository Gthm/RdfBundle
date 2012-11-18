<?php
namespace Gthm\RdfBundle\Mapper;

use ReflectionClass;

class JsonLdMapper implements MapperInterface
{
    /**
     * @var \Doctrine\Common\Annotations\Reader
     */
    protected $reader;

    /**
     * @var object
     */
    protected $entity;

    /**
     * @var \ReflectionClass
     */
    protected $reflection;

    /**
     * @var \Symfony\Component\Routing\Router
     */
    protected $router;

    public function __construct(\Doctrine\Common\Annotations\Reader $reader, \Symfony\Component\Routing\Router $router)
    {
        $this->reader = $reader;
        $this->router = $router;
    }

    public function applyRepresentation($entity, $representation)
    {
        $this->setEntity($entity);
        $array = json_decode($representation, true);
        return $this->applyData($array);
    }

    public function getRepresentation($entity)
    {
        $this->setEntity($entity);
        $obj = $this->getJsonLdObject();
        return new \Symfony\Component\HttpFoundation\JsonResponse($obj);
    }


    protected function setEntity($entity)
    {
        if($this->entity === $entity) {
            return;
        }

        $this->reflection = null;
        $this->entity = $entity;
    }

    protected function applyData($jsonLD)
    {
        $reflClass = $this->getEntityReflection();

        $type = $this->getEntityType($reflClass);

        $properties = $reflClass->getProperties();
        foreach ($properties as $oneProperty) {
            $rdfProp = $this->reader->getPropertyAnnotation($oneProperty, 'Gthm\RdfBundle\Annotations\Property');
            if ($rdfProp instanceof \Gthm\RdfBundle\Annotations\Property) {
                $propTurtle = $this->getTurtleUri($rdfProp->name);

                if (!isset($jsonLD[$propTurtle])) {
                    throw new \Exception('No data for: ' . $oneProperty->getName());
                }

                /** @var $Column \Doctrine\ORM\Mapping\Column */
                $Column = $this->reader->getPropertyAnnotation($oneProperty, 'Doctrine\ORM\Mapping\Column');
                if (strncmp($Column->type, 'date', 4)==0) {
                    $tmp = new \DateTime($jsonLD[$propTurtle]);
                    $jsonLD[$propTurtle] = $tmp;
                }

                $this->setEntityData($oneProperty->getName(), $jsonLD[$propTurtle]);
            }
        }

        return $this->getEntity();
    }

    /**
     * @param \ReflectionClass $reflClass
     * @return \Gthm\RdfBundle\Annotations\Subject
     * @throws \Exception
     */
    protected function getEntitySubject()
    {
        $reflClass = $this->getEntityReflection();
        $subject = $this->reader->getClassAnnotation($reflClass, 'Gthm\RdfBundle\Annotations\Subject');
        if (!($subject instanceof \Gthm\RdfBundle\Annotations\Subject)) {
            throw new \Exception('No RDF Subject set!');
        }

        $params = array();

        if(is_array($subject->params)) {
            foreach($subject->params as $key => $field) {
                $params[$key] = $this->getEntityData($field);
            }
        }

        $uri = $this->router->generate($subject->route, $params, true);

        return $this->getTurtleUri($uri);
    }

    /**
     * @param string $field
     * @return mixed
     */
    protected function getEntityData($field)
    {
        $getMethod = 'get' . ucfirst($field);
        if (!method_exists($this->getEntity(), $getMethod)) {
            throw new \Exception('No getter for: ' . $field);
        } else {
            return call_user_func(array($this->getEntity(), $getMethod));
        }
    }

    /**
     * @param string $field
     * @param $value
     */
    protected function setEntityData($field, $value) {
        $setMethod = 'set' . ucfirst($field);
        if (!method_exists($this->getEntity(), $setMethod)) {
            throw new \Exception('No setter for: ' . $field);
        } else {
            return call_user_func(array($this->getEntity(), $setMethod), $value);
        }
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function getEntityType()
    {
        $reflClass = $this->getEntityReflection();
        $type = $this->reader->getClassAnnotation($reflClass, 'Gthm\RdfBundle\Annotations\Type');
        if (!($type instanceof \Gthm\RdfBundle\Annotations\Type)) {
            throw new \Exception('No RDF Type set!');
        }
        return $type->name;
    }

    /**
     * @return \ReflectionClass
     */
    protected function getEntityReflection()
    {
        if(!($this->reflection instanceof \ReflectionClass)) {
            $this->reflection = new ReflectionClass(get_class($this->getEntity()));
        }

        return $this->reflection;
    }

    protected function getEntity()
    {
        if(!is_object($this->entity)) {
            throw new \Exception('No Entity set.');
        }

        return $this->entity;
    }


    protected function getJsonLdObject()
    {
        $object = array();
        $reflClass = $this->getEntityReflection();

        $object['@type'] = $this->getEntityType();
        $object['@subject'] = $this->getEntitySubject();

        $properties = $reflClass->getProperties();
        foreach ($properties as $oneProperty) {
            $rdfProp = $this->reader->getPropertyAnnotation($oneProperty, 'Gthm\RdfBundle\Annotations\Property');
            if ($rdfProp instanceof \Gthm\RdfBundle\Annotations\Property) {
                $data = $this->getEntityData($oneProperty->getName());

                /** @var $Column \Doctrine\ORM\Mapping\Column */
                $Column = $this->reader->getPropertyAnnotation($oneProperty, 'Doctrine\ORM\Mapping\Column');
                if (strncmp($Column->type, 'date', 4)==0 && $data instanceof \DateTime) {
                    $data = $data->format('F j, Y H:i');
                }

                $object[ $this->getTurtleUri($rdfProp->name) ] = $data;
            }
        }

        return $object;
    }

    /**
     * @param $uri
     * @return string
     */
    protected function getTurtleUri($uri) {
        return '<' . $uri . '>';
    }
}
