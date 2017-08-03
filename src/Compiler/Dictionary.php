<?php
/**
 * This file is part of Railgun package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Serafim\Railgun\Compiler;

use Serafim\Railgun\Compiler\Exceptions\SemanticException;
use Serafim\Railgun\Compiler\Exceptions\TypeNotFoundException;
use Serafim\Railgun\Reflection\Abstraction\DefinitionInterface;
use Serafim\Railgun\Reflection\Abstraction\DocumentTypeInterface;
use Serafim\Railgun\Reflection\Abstraction\NamedDefinitionInterface;

/**
 * Class Dictionary
 * @package Serafim\Railgun\Compiler
 */
class Dictionary implements \Countable, \IteratorAggregate
{
    /**
     * @var array|DefinitionInterface[]
     */
    private $definitions = [];

    /**
     * @var array|NamedDefinitionInterface[]
     */
    private $namedDefinitions = [];

    /**
     * @var Autoloader
     */
    private $loader;

    /**
     * @var array
     */
    private $context = [];

    /**
     * Dictionary constructor.
     * @param Autoloader $loader
     */
    public function __construct(Autoloader $loader)
    {
        $this->loader = $loader;
    }

    /**
     * @param DefinitionInterface $definition
     * @param bool $force Allows types redefinition for already defined type with same name
     * @return Dictionary
     * @throws SemanticException
     */
    public function register(DefinitionInterface $definition, bool $force = false): Dictionary
    {
        if ($definition instanceof NamedDefinitionInterface) {
            $this->registerNamedDefinition($definition, $force);
        } else {
            $this->registerAnonymousDefinition($definition);
        }

        $this->registerCached($definition);

        return $this;
    }

    /**
     * @param DefinitionInterface $definition
     */
    private function registerCached(DefinitionInterface $definition): void
    {
        // Add hash map for fast type resolving from Document
        $documentId = $definition->getDocument()->getId();

        if (!array_key_exists($documentId, $this->context)) {
            $this->context[$documentId] = [];
        }

        $this->context[$documentId][] = $definition;
    }

    /**
     * @param NamedDefinitionInterface $definition
     * @param bool $force
     * @throws SemanticException
     */
    private function registerNamedDefinition(NamedDefinitionInterface $definition, bool $force = false): void
    {
        if (!$force && array_key_exists($definition->getName(), $this->namedDefinitions)) {
            $sourceError = 'Can not register type named "%s" as %s.';
            $targetError = 'Type "%s" already registered as %s';

            $target = $this->namedDefinitions[$definition->getName()];

            throw new SemanticException(
                sprintf($sourceError, $definition->getName(), $definition->getTypeName()) . ' ' .
                sprintf($targetError, $target->getName(), $target->getTypeName())
            );
        }

        $this->namedDefinitions[$definition->getName()] = $definition;
    }

    /**
     * @param DefinitionInterface $definition
     */
    private function registerAnonymousDefinition(DefinitionInterface $definition): void
    {
        $this->definitions[] = $definition;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->definitions) + count($this->namedDefinitions);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->namedDefinitions);
    }

    /**
     * @param string $name
     * @return NamedDefinitionInterface
     * @throws TypeNotFoundException
     */
    public function find(string $name): NamedDefinitionInterface
    {
        try {
            /**
             * Load existing type
             */
            return $this->get($name);

        } catch (TypeNotFoundException $error) {
            /**
             * Otherwise start autoloader and try to resolve it from external files
             */
            $parent = null;
            $error  = 'Type "%s" not found and could not be loaded';
            try {
                if ($result = $this->loader->load($name)) {
                    return $result;
                }
            } catch (\Exception $e) {
                [$error, $parent] = ['"%s" type found and was be loaded but has an error while loading.', $e];
            }
        }

        throw new TypeNotFoundException(sprintf($error, $name), 0, $parent);
    }

    /**
     * @param string $name
     * @return NamedDefinitionInterface
     * @throws TypeNotFoundException
     */
    public function get(string $name): NamedDefinitionInterface
    {
        if ($this->has($name)) {
            return $this->namedDefinitions[$name];
        }

        throw new TypeNotFoundException(sprintf('Type "%s" not found', $name));
    }

    /**
     * @param DocumentTypeInterface $document
     * @return array
     */
    public function definitions(DocumentTypeInterface $document): array
    {
        return $this->context[$document->getId()] ?? [];
    }

    /**
     * @param DocumentTypeInterface $document
     * @param string $name
     * @return null|NamedDefinitionInterface
     * @throws TypeNotFoundException
     */
    public function definition(DocumentTypeInterface $document, string $name): ?NamedDefinitionInterface
    {
        return array_first($this->definitions($document), function (DefinitionInterface $definition) use ($name) {
            return $definition instanceof NamedDefinitionInterface && $definition->getName() === $name;
        });
    }

    /**
     * @return \Generator|\Traversable
     */
    public function getIterator(): \Traversable
    {
        yield from $this->named();
        yield from $this->anonymous();
    }

    /**
     * @return \Traversable
     */
    public function all(): \Traversable
    {
        yield from $this->getIterator();
    }

    /**
     * @return \Traversable
     */
    public function named(): \Traversable
    {
        foreach ($this->namedDefinitions as $definition) {
            yield $definition->getDocument() => $definition;
        }
    }

    /**
     * @return \Traversable
     */
    public function anonymous(): \Traversable
    {
        foreach ($this->definitions as $definition) {
            yield $definition->getDocument() => $definition;
        }
    }
}