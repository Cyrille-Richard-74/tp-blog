<?php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class SlugService
{
    private $em;
    private $glue = "-";
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function setRepository($entity): self
    {
        if (class_exists($entity))
        {
            $this->repository = $this->em->getRepository( $entity );
        }
        
        return $this;
    }

    public function setGlue(string $glue): self
    {
        $this->glue = $glue;

        return $this;
    }

    // Hello World
    // hello-world
    // hello-world-2
    // hello-world-3
    public function slugify(string $text)
    {
        // replace non letter or digits by -
        $text = preg_replace('#[^\\pL\d]+#u', $this->glue, $text);

        // trim
        $text = trim($text, $this->glue);

        // transliterate
        if (function_exists('iconv'))
        {
            $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        }

        // lowercase
        $text = strtolower($text);

        // remove unwanted characters
        $text = preg_replace('#[^'.$this->glue.'\w]+#', '', $text);

        if (empty($text))
        {
            return 'n-a';
        }

        $text = $this->increment($text);

        return $text;
    }


    private function increment(string $slug)
    {
        $similar = $this->findSimilar($slug);
        $max = 0;

        if (null != $similar)
        {
            foreach($similar as $item)
            {
                $similarSlug = $item->getSlug();

                $x = explode($this->glue, $similarSlug);

                if (is_numeric( end($x) )) 
                {
                    $end = intval(end($x));

                    if ($end > $max)
                    {
                        $max = $end;
                    }
                }
            }

            if ($max > 0)
            {
                $max++;
                return "{$slug}-{$max}";
            }

            return "{$slug}-2";
        }

        return $slug;
    }

    private function findSimilar(string $slug): ?array
    {
        if ($this->repository)
        {
            return $this->repository->createQueryBuilder('o')
                ->where('o.slug LIKE :slug')
                ->setParameter('slug', "{$slug}%")
                ->getQuery()
                ->getResult();
        }

        return null;
    }
}