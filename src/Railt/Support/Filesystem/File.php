<?php
/**
 * This file is part of Railt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Railt\Support\Filesystem;

use Railt\Support\Exceptions\NotFoundException;
use Railt\Support\Exceptions\NotReadableException;

/**
 * Class File
 * @package Railt\Support
 */
class File extends \SplFileInfo implements ReadableInterface
{
    /**
     * Name of file when file name not defined
     */
    private const VIRTUAL_FILE_NAME = 'php://input';

    /**
     * @var string
     */
    private $sources;

    /**
     * File constructor.
     * @param string $sources
     * @param string $path
     */
    public function __construct(string $sources, ?string $path)
    {
        $this->sources = $sources;
        parent::__construct($path ?? self::VIRTUAL_FILE_NAME);
    }

    /**
     * @param string|\SplFileInfo $file
     * @return File
     * @throws \InvalidArgumentException
     * @throws NotReadableException
     */
    public static function new($file): File
    {
        if ($file instanceof \SplFileInfo) {
            return static::fromSplFileInfo($file);
        }

        if (! is_string($file)) {
            throw new \InvalidArgumentException('File name must be a string.');
        }

        if (is_file($file)) {
            return static::fromPathname($file);
        }

        return static::fromSources($file);
    }

    /**
     * @param \SplFileInfo $file
     * @return File
     * @throws NotReadableException
     */
    public static function fromSplFileInfo(\SplFileInfo $file): File
    {
        if (! is_file($file->getPathname())) {
            throw new NotFoundException($file->getPathname());
        }

        if (! $file->isReadable()) {
            throw new NotReadableException($file->getPathname());
        }

        $sources = @file_get_contents($file->getPathname());

        return new static($sources, $file->getPathname());
    }

    /**
     * @param string $path
     * @return File
     * @throws NotReadableException
     */
    public static function fromPathname(string $path): File
    {
        return static::fromSplFileInfo(new \SplFileInfo($path));
    }

    /**
     * @param string $sources
     * @param null|string $path
     * @return File
     */
    public static function fromSources(string $sources, string $path = null): File
    {
        return new static($sources, $path);
    }

    /**
     * @return string
     */
    public function read(): string
    {
        return $this->sources;
    }
}