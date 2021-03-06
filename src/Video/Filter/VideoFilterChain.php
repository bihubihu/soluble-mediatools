<?php

declare(strict_types=1);

namespace Soluble\MediaTools\Video\Filter;

use Soluble\MediaTools\Common\Exception\UnsupportedParamValueException;
use Soluble\MediaTools\Video\Exception\InvalidArgumentException;
use Soluble\MediaTools\Video\Filter\Type\FFMpegVideoFilterInterface;
use Soluble\MediaTools\Video\Filter\Type\VideoFilterInterface;

class VideoFilterChain implements FFMpegVideoFilterInterface, \Countable
{
    /** @var VideoFilterInterface[] */
    private $filters = [];

    /**
     * @param VideoFilterInterface[] $filters
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $filters = [])
    {
        if ($filters === []) {
            return;
        }

        $this->addFilters($filters);
    }

    /**
     * @return VideoFilterInterface[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Will append the filter, if the filter is a VideoFilterChain
     * it will be merged at the end.
     *
     * @param VideoFilterInterface $filter filter to add
     */
    public function addFilter(VideoFilterInterface $filter): void
    {
        if ($filter instanceof self) {
            if ($filter->count() > 0) {
                $this->filters = array_merge($this->filters, $filter->getFilters());
            }
        } else {
            $this->filters[] = $filter;
        }
    }

    public function count(): int
    {
        return count($this->filters);
    }

    /**
     * @param VideoFilterInterface[] $filters
     *
     * @throws InvalidArgumentException
     */
    public function addFilters(array $filters): void
    {
        foreach ($filters as $filter) {
            if (!$filter instanceof VideoFilterInterface) {
                throw new InvalidArgumentException(sprintf(
                    'Cannot add filter \'%s\', it must not implement %s',
                    is_object($filter) ? get_class($filter) : gettype($filter),
                    VideoFilterInterface::class
                ));
            }
            $this->addFilter($filter);
        }
    }

    /**
     * @throws UnsupportedParamValueException
     */
    public function getFFmpegCLIValue(): ?string
    {
        $values = [];
        foreach ($this->filters as $filter) {
            if (!$filter instanceof FFMpegVideoFilterInterface) {
                throw new UnsupportedParamValueException(
                    sprintf(
                        'Filter \'%s\' have not been made compatible with FFMpeg',
                        get_class($filter)
                    )
                );
            }
            $val = $filter->getFFmpegCLIValue();
            if ($val === '') {
                continue;
            }

            $values[] = $val;
        }

        if (count($values) === 0) {
            return null;
        }

        return implode(',', $values);
    }
}
