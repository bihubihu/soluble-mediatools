<?php

declare(strict_types=1);

namespace MediaToolsTest\Video;

use MediaToolsTest\Util\ServicesProviderTrait;
use PHPUnit\Framework\TestCase;
use Soluble\MediaTools\Video\Filter\Hqdn3DVideoFilter;
use Soluble\MediaTools\Video\Filter\NlmeansVideoFilter;
use Soluble\MediaTools\Video\Filter\VideoFilterChain;
use Soluble\MediaTools\Video\VideoThumbGeneratorInterface;
use Soluble\MediaTools\Video\VideoThumbParams;

class VideoThumbGeneratorTest extends TestCase
{
    use ServicesProviderTrait;

    /** @var VideoThumbGeneratorInterface */
    protected $thumbGenerator;

    public function setUp(): void
    {
        $this->thumbGenerator = $this->getVideoThumbService();
    }

    public function testGetSymfonyProcessWithFrame(): void
    {
        $thumbParams = (new VideoThumbParams())
            ->withFrame(2);

        $process = $this->thumbGenerator->getSymfonyProcess(
            __FILE__,
            '/path/output.jpg',
            $thumbParams
        );

        $cmdLine = $process->getCommandLine();

        // Note that we did 2 -1
        self::assertContains(' -filter:v "select=eq(n\,1)" ', $cmdLine);
        self::assertContains(escapeshellarg('/path/output.jpg'), $cmdLine);
    }

    public function testGetSymfonyProcessWithFrame0(): void
    {
        $thumbParams = (new VideoThumbParams())
            ->withFrame(0);

        $process = $this->thumbGenerator->getSymfonyProcess(
            __FILE__,
            '/path/output.jpg',
            $thumbParams
        );

        $cmdLine = $process->getCommandLine();

        self::assertContains(' -filter:v "select=eq(n\,0)" ', $cmdLine);
        self::assertContains(escapeshellarg('/path/output.jpg'), $cmdLine);
    }

    public function testGetSymfonyProcessWithTime(): void
    {
        $thumbParams = (new VideoThumbParams())
            ->withTime(1.234);

        $process = $this->thumbGenerator->getSymfonyProcess(
            __FILE__,
            '/path/output.jpg',
            $thumbParams
        );

        $cmdLine = $process->getCommandLine();

        self::assertContains(' -ss 0:00:01.234 ', $cmdLine);
        self::assertContains(escapeshellarg('/path/output.jpg'), $cmdLine);
    }

    public function testGetSymfonyProcessWithFrameAndFilters(): void
    {
        $videoFilterChain = new VideoFilterChain();
        $videoFilterChain->addFilter(new Hqdn3DVideoFilter());
        $videoFilterChain->addFilter(new NlmeansVideoFilter());

        $thumbParams = (new VideoThumbParams())
            ->withFrame(2)
            ->withVideoFilter($videoFilterChain);

        $process = $this->thumbGenerator->getSymfonyProcess(
            __FILE__,
            '/path/output.jpg',
            $thumbParams
        );

        $cmdLine = $process->getCommandLine();

        self::assertContains(' -filter:v "select=eq(n\,1)",hqdn3d,nlmeans ', $cmdLine);
        self::assertContains(escapeshellarg('/path/output.jpg'), $cmdLine);
    }

    public function testGetSymfonyProcessWithEmptyFilterChain(): void
    {
        $videoFilterChain = new VideoFilterChain();

        $thumbParams = (new VideoThumbParams())
            ->withTime(2)
            ->withVideoFilter($videoFilterChain);

        $process = $this->thumbGenerator->getSymfonyProcess(
            __FILE__,
            '/path/output.jpg',
            $thumbParams
        );

        $cmdLine = $process->getCommandLine();

        self::assertNotContains(' -filter:v ', $cmdLine);
        self::assertContains(escapeshellarg('/path/output.jpg'), $cmdLine);
    }
}
