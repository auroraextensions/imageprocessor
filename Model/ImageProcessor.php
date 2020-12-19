<?php
/**
 * ImageProcessor.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/imageprocessor/LICENSE.txt
 *
 * @package       AuroraExtensions\ImageProcessor\Model
 * @copyright     Copyright (C) 2020 Aurora Extensions <support@auroraextensions.com>
 * @license       MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\ImageProcessor\Model;

use AuroraExtensions\ImageProcessor\Api\ImageManagementInterface;
use Magento\Framework\{
    App\Filesystem\DirectoryList,
    Filesystem,
    Filesystem\Directory\ReadInterface,
    Filesystem\Directory\WriteInterface,
    Image\AdapterFactory,
    UrlInterface
};

use const DIRECTORY_SEPARATOR;
use function basename;
use function implode;
use function ltrim;
use function trim;

class ImageProcessor implements ImageManagementInterface
{
    /** @var Filesystem $filesystem */
    private $filesystem;

    /** @var AdapterFactory $imageFactory */
    private $imageFactory;

    /** @var string $subdirectory */
    private $subdirectory;

    /**
     * @param Filesystem $filesystem
     * @param AdapterFactory $imageFactory
     * @param string $subdirectory
     * @return void
     */
    public function __construct(
        Filesystem $filesystem,
        AdapterFactory $imageFactory,
        string $subdirectory = self::MEDIA_PATH
    ) {
        $this->filesystem = $filesystem;
        $this->imageFactory = $imageFactory;
        $this->subdirectory = $subdirectory;
    }

    /**
     * @param int $width
     * @param int $height
     * @return string
     */
    private function getResizedMediaPath(int $width, int $height): string
    {
        /** @var string $w */
        $w = (string) $width;

        /** @var string $h */
        $h = (string) $height;

        /** @var ReadInterface $reader */
        $reader = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);

        /** @var string $savePath */
        $savePath = implode(DIRECTORY_SEPARATOR, [
            trim($this->subdirectory, DIRECTORY_SEPARATOR),
            $w,
            'x',
            $h,
        ]);
        return $reader->getAbsolutePath($savePath);
    }

    /**
     * {@inheritdoc}
     */
    public function resize(
        string $path,
        int $width = self::WIDTH,
        int $height = self::HEIGHT
    ): ?string
    {
        /** @var ReadInterface $reader */
        $reader = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);

        /** @var WriteInterface $writer */
        $writer = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);

        /** @var string $filePath */
        $filePath = $reader->getAbsolutePath($path);

        if (!$reader->isFile($filePath)) {
            return null;
        }

        /** @var string $resizedMediaPath */
        $resizedMediaPath = $this->getResizedMediaPath($width, $height);

        /** @var string $relativeMediaPath */
        $relativeMediaPath = $reader->getRelativePath($resizedMediaPath);

        if (!$reader->isDirectory($relativeMediaPath)) {
            $writer->create($resizedMediaPath);
        }

        if (!$reader->isDirectory($relativeMediaPath)) {
            return null;
        }

        /** @var Magento\Framework\Image\Adapter\AdapterInterface $image */
        $image = $this->imageFactory->create();
        $image->open($filePath);
        $image->keepAspectRatio(true);
        $image->resize($width, $height);

        /** @var string $resizedFile */
        $resizedFile = implode(DIRECTORY_SEPARATOR, [
            $resizedMediaPath,
            basename($filePath),
        ]);
        $image->save($resizedFile);

        /** @var string $relativeResizedFile */
        $relativeResizedFile = $reader->getRelativePath($resizedFile);

        if (!$reader->isFile($relativeResizedFile)) {
            return null;
        }

        /** @var string $resizedFilePath */
        $resizedFilePath = implode(DIRECTORY_SEPARATOR, [
            '',
            ltrim($relativeResizedFile, DIRECTORY_SEPARATOR),
        ]);
        return $resizedFilePath;
    }
}
