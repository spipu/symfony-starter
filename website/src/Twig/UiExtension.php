<?php

declare(strict_types=1);

namespace App\Twig;

use App\Service\FileService;
use App\Service\HostService;
use Spipu\CoreBundle\Service\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class UiExtension extends AbstractExtension
{
    private Environment $environment;
    private FileService $fileService;
    private HostService $hostService;
    private string $projectDir;

    public function __construct(
        Environment $environment,
        FileService $fileService,
        HostService $hostService,
        string $projectDir
    ) {
        $this->environment = $environment;
        $this->fileService = $fileService;
        $this->hostService = $hostService;
        $this->projectDir = $projectDir;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('convertFileSize', [$this, 'convertFileSize']),
            new TwigFunction('asset_public', [$this, 'getAssetUrlPublic']),
            new TwigFunction('asset_absolute_path', [$this, 'getAssetAbsolutePath']),
            new TwigFunction('getEnvironmentPrefix', [$this, 'getEnvironmentPrefix']),
        ];
    }

    public function convertFileSize(int $size): string
    {
        return $this->fileService->getHumanFileSize($size);
    }

    public function getAssetUrlPublic(string $path): string
    {
        return 'https://' . $this->hostService->getHost() . '/' . ltrim($path, '/');
    }

    public function getAssetAbsolutePath(string $path): string
    {
        return $this->projectDir . '/public/' . ltrim($path, '/');
    }

    public function getEnvironmentPrefix(): string
    {
        return $this->environment->getEnvironmentSuffix();
    }
}
