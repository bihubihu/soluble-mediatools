<?php

declare(strict_types=1);

namespace Soluble\MediaTools\Video;

use Soluble\MediaTools\Common\Process\ProcessParamsInterface;
use Soluble\MediaTools\Video\Exception\ConverterExceptionInterface;
use Symfony\Component\Process\Process;

interface VideoConverterInterface
{
    /**
     * Return ready-to-run symfony process object that you can use
     * to `run()` or `start()` programmatically. Useful if you want to make
     * things your way...
     *
     * @see https://symfony.com/doc/current/components/process.html
     */
    public function getSymfonyProcess(string $inputFile, string $outputFile, VideoConvertParamsInterface $convertParams, ?ProcessParamsInterface $processParams = null): Process;

    /**
     * Run a conversion, throw exception on error.
     *
     * @param callable|null $callback A PHP callback to run whenever there is some
     *                                tmp available on STDOUT or STDERR
     *
     * @throws ConverterExceptionInterface When inputFile does not exists
     */
    public function convert(string $inputFile, string $outputFile, VideoConvertParamsInterface $convertParams, ?callable $callback = null, ?ProcessParamsInterface $processParams = null): void;
}