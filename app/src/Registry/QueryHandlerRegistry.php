<?php

declare(strict_types=1);

namespace App\Registry;

use App\Exception\QueryHandlerLocatorException;
use App\Query\Handler\QueryHandlerInterface;
use App\Query\QueryInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;

class QueryHandlerRegistry
{
    public function __construct(
        #[AutowireLocator('app.query_handler', indexAttribute: 'query')]
        private readonly ContainerInterface $container,
    ) {
    }

    /**
     * @param class-string<QueryInterface> $className
     */
    public function get(string $className): QueryHandlerInterface
    {
        if ($this->container->has($className)) {
            /** @var QueryHandlerInterface $queryHandler */
            $queryHandler = $this->container->get($className);

            return $queryHandler;
        }

        throw new QueryHandlerLocatorException("No Query handler registered for {$className}");
    }

    /**
     * @throws QueryHandlerLocatorException
     */
    public function getByQueryObject(QueryInterface $query): QueryHandlerInterface
    {
        return $this->get($query::class);
    }
}
