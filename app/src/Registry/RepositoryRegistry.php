<?php

declare(strict_types=1);

namespace App\Registry;

use App\Enum\ServiceOrderSubject;
use App\Exception\RepositoryLocatorException;
use App\Repository\EntityRepositoryInterface;
use Psr\Container\ContainerInterface;

class RepositoryRegistry
{
    public function __construct(
        #[AutowireLocator('app.repository', indexAttribute: 'type')]
        private readonly ContainerInterface $container,
    ) {
    }

    /**
     * @param string $subjectTypeString Value from ServiceOrderSubject enum
     */
    public function get(string $subjectTypeString): EntityRepositoryInterface
    {
        if ($this->container->has($subjectTypeString)) {
            /** @var EntityRepositoryInterface $entityRepository */
            $entityRepository = $this->container->get($subjectTypeString);

            return $entityRepository;
        }

        throw new RepositoryLocatorException("No repository registered for subject type {$subjectTypeString}");
    }

    /**
     * @throws RepositoryLocatorException
     */
    public function getBySubjectType(ServiceOrderSubject $subjectType): EntityRepositoryInterface
    {
        return $this->get($subjectType->value);
    }
}
