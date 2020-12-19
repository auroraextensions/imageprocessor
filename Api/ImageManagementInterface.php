<?php
/**
 * ImageManagementInterface.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/imageprocessor/LICENSE.txt
 *
 * @package       AuroraExtensions\ImageProcessor\Api
 * @copyright     Copyright (C) 2020 Aurora Extensions <support@auroraextensions.com>
 * @license       MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\ImageProcessor\Api;

interface ImageManagementInterface
{
    /** @constant string MEDIA_PATH */
    public const MEDIA_PATH = '/resized/';

    /** @constant int WIDTH */
    public const WIDTH = 150;

    /** @constant int HEIGHT */
    public const HEIGHT = 150;

    /**
     * @param string $path
     * @param int $width
     * @param int $height
     * @return string|null
     */
    public function resize(string $path, int $width, int $height): ?string;
}
