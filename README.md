RdfBundle
=========

A Symfony2 Bundle for an easy setup of create.js endpoints.


This Bundle is a very simple helper for setting up a simple create.js backend.
No need for createphp or symfony-cmf.



## Instalation


1. Add to your composer.json

        "require": { 
            //...
            "gthm/rdf-bundle": "dev-master"
        }


2. Enable Bundle

        <?php
        // app/AppKernel.php

        public function registerBundles()
        {
            $bundles = array(
                //...
                new Gthm\RfdBundle\GthmRdfBundle(),
            );
        }

3. Include Config

        // app/config/config.yml

        imports:
            // ..
            - { resource: @GthmRdfBundle/Resources/config/services.yml }


## Usage

1. add the RDF Annotations to your Doctrine Entities


        <?php
        namespace Acme\ContentBundle\Entity;
        
        use Doctrine\ORM\Mapping as ORM;
        use Gthm\RdfBundle\Annotations as RDF;
        
        /**
         * Acme\ContentBundle\Entity\Article
         *
         * @RDF\Type(name="http://schema.org/Article")
         * @RDF\Subject(route="content.article.update", params={"slug":"slug"})
         * @ORM\Table()
         * @ORM\Entity(repositoryClass="Acme\ContentBundle\Entity\ArticleRepository")
         */
        class Article
        {
            /**
             * @var string $title
             *
             * @RDF\Property(name="http://schema.org/headline")
             * @ORM\Column(name="title", type="string", length=255)
             */
            private $title;
        
            /**
             * @var string $introText
             *
             * @RDF\Property(name="http://schema.org/alternativeHeadline")
             * @ORM\Column(name="introText", type="text")
             */
            private $introText;
        
            /**
             * @var string $mainText
             *
             * @RDF\Property(name="http://schema.org/text")
             * @ORM\Column(name="mainText", type="text")
             */
            private $mainText;
        
            /**
             * @var \DateTime $publishDate
             *
             * @RDF\Property(name="http://schema.org/datePublished")
             * @ORM\Column(name="publishDate", type="datetime")
             */
            private $publishDate;
        }


2. add the PUT action


        /**
         * @Route("/article/{slug}", name="content.article.update")
         * @Method({"PUT"})
         */
        public function updateAction($slug)
        {
            /** @var $repo \Gthm\ContentBundle\Entity\ArticleRepository */
            $repo = $this->getDoctrine()->getRepository('\Acme\ContentBundle\Entity\Article');

            /** @var $news \Gthm\NewsBundle\Entity\NewsPost */
            $article = $repo->findOneBySlug($slug);

            if(!$article instanceof \Acme\ContentBundle\Entity\Article) {
                throw $this->createNotFoundException('This article does not exist');
            }


            try {
                /** @var $mapper \Gthm\RdfBundle\Mapper\MapperInterface */
                $mapper = $this->get('gthm.rdfbundle.mapper');

                $json = $this->getRequest()->getContent();
                $mapper->applyRepresentation($article, $json);

                $manager = $this->getDoctrine()->getManagerForClass(get_class($article));
                $manager->persist($article);
                $manager->flush();

                return $mapper->getRepresentation( $article );
            } catch( \Exception $e ) {
                echo $e->getMessage();
                exit;
            }
        }


3. Add create.js to your site

Add the RDF Annotation and the create.js Javascript.


4. Profit

Because of the timesaving effect of using create.js for editing instead of writing admin areas,
and the SEO effect for the content because of the usage of the RDF Annotation our content will
be found so extreamly easy that nothing can go wrong and we will become ridiculously rich :D



## TODO

 * add Tests
 * better type handling (DateTime handling at the moment is in "works for me" Status)
 * command for simple creation of html views based on the annotations
