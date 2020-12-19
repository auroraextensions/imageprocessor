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
use Magento\Store\Model\StoreManagerInterface;

class ImageProcessor implements ImageManagementInterface
{
    /** @property Filesystem $filesystem */
    protected $filesystem;

    /** @property AdapterFactory $imageFactory */
    protected $imageFactory;

    /** @property StoreManagerInterface $storeManager */
    protected $storeManager;

    /** @property string $subdirectory */
    protected $subdirectory;

    /**
     * @param Filesystem $filesystem
     * @param AdapterFactory $imageFactory
     * @param StoreManagerInterface $storeManager
     * @param string $subdirectory
     * @return void
     */
    public function __construct(
        Filesystem $filesystem,
        AdapterFactory $imageFactory,
        StoreManagerInterface $storeManager,
        string $subdirectory = self::MEDIA_PATH
    ) {
        $this->filesystem = $filesystem;
        $this->imageFactory = $imageFactory;
        $this->storeManager = $storeManager;
        $this->subdirectory = $subdirectory;
    }

    /**
     * @return ReadInterface
     */
    public function getMediaReader(): ReadInterface
    {
        return $this->filesystem
            ->getDirectoryRead(DirectoryList::MEDIA);
    }

    /**
     * @return WriteInterface
     */
    public function getMediaWriter(): WriteInterface
    {
        return $this->filesystem
            ->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * @param int $width
     * @param int $height
     * @return string
     */
    public function getResizedMediaPath(int $width, int $height): string
    {
        /** @var string $w */
        $w = (string) $width;

        /** @var string $h */
        $h = (string) $height;

        /** @var string $savePath */
        $savePath = trim($this->subdirectory, '/') . '/' . $w . 'x' . $h;

        return $this->getMediaReader()
            ->getAbsolutePath($savePath);
    }

    /**
     * @param string $filePath
     * @return string|null
     */
    public function getFilename(string $filePath): ?string
    {
        return basename($filePath);
    }

    /**
     * @param string $path Image path, relative to media base.
     * @param int $width
     * @param int $height
     * @return string|null
     */
    public function resize(
        string $path,
        int $width = self::WIDTH,
        int $height = self::HEIGHT
    ): ?string
    {
        /** @var ReadInterface $mediaReader */
        $mediaReader = $this->getMediaReader();

        /** @var WriteInterface $mediaWriter */
        $mediaWriter = $this->getMediaWriter();

        /** @var string $filePath */
        $filePath = $mediaReader->getAbsolutePath($path);

        if (!$mediaReader->isFile($filePath)) {
            return null;
        }

        /** @var string $resizedMediaPath */
        $resizedMediaPath = $this->getResizedMediaPath($width, $height);

        /** @var string $relativeMediaPath */
        $relativeMediaPath = $mediaReader->getRelativePath($resizedMediaPath);

        if (!$mediaReader->isDirectory($relativeMediaPath)) {
            $mediaWriter->create($resizedMediaPath);
        }

        if (!$mediaReader->isDirectory($relativeMediaPath)) {
            return null;
        }

        /** @var Magento\Framework\Image\Adapter\AdapterInterface $image */
        $image = $this->imageFactory->create();
        $image->open($filePath);
        $image->keepAspectRatio(true);
        $image->resize($width, $height);

        /** @var string $resizedFile */
        $resizedFile = $resizedMediaPath . '/' . $this->getFilename($filePath);
        $image->save($resizedFile);

        /** @var string $relativeResizedFile */
        $relativeResizedFile = $mediaReader->getRelativePath($resizedFile);

        if (!$mediaReader->isFile($relativeResizedFile)) {
            return null;
        }

        /** @var string $resizedFilePath */
        $resizedFilePath = '/' . ltrim($relativeResizedFile, '/');

        return $resizedFilePath;
    }
}
