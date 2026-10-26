<?php

namespace App\Twig;

/**
 * Lightweight replacement for the "image" Twig helper that was provided by
 * the abandoned gregwar/image-bundle. Images are cropped/resized with GD and
 * written to public/cache on first use.
 */
class ImageReference
{
    private ?int $width = null;
    private ?int $height = null;

    public function __construct(
        private string $publicDir,
        private string $cacheDir,
        private string $path,
    ) {
    }

    public function cropResize(int $width, int $height): self
    {
        $reference = clone $this;
        $reference->width = $width;
        $reference->height = $height;

        return $reference;
    }

    public function __toString(): string
    {
        $publicPath = '/'.ltrim($this->path, '/');

        if ($this->width === null || $this->height === null) {
            return $publicPath;
        }

        $source = rtrim($this->publicDir, '/').'/'.ltrim($this->path, '/');
        if (!is_file($source)) {
            return $publicPath;
        }

        $stat = stat($source);
        $fileName = sha1(implode('|', [
            $source,
            $stat['mtime'] ?? 0,
            $stat['size'] ?? 0,
            $this->width,
            $this->height,
        ])).'.jpg';
        $target = rtrim($this->cacheDir, '/').'/'.$fileName;

        if (!is_file($target)) {
            $this->generateThumbnail($source, $target);
        }

        return '/cache/'.$fileName;
    }

    private function generateThumbnail(string $source, string $target): void
    {
        $contents = file_get_contents($source);
        if ($contents === false) {
            return;
        }

        $image = imagecreatefromstring($contents);
        if ($image === false) {
            return;
        }

        $sourceWidth = imagesx($image);
        $sourceHeight = imagesy($image);
        $targetRatio = $this->width / $this->height;
        $sourceRatio = $sourceWidth / $sourceHeight;

        if ($sourceRatio > $targetRatio) {
            $cropHeight = $sourceHeight;
            $cropWidth = (int) round($sourceHeight * $targetRatio);
            $sourceX = (int) round(($sourceWidth - $cropWidth) / 2);
            $sourceY = 0;
        } else {
            $cropWidth = $sourceWidth;
            $cropHeight = (int) round($sourceWidth / $targetRatio);
            $sourceX = 0;
            $sourceY = (int) round(($sourceHeight - $cropHeight) / 2);
        }

        $thumbnail = imagecreatetruecolor($this->width, $this->height);
        $white = imagecolorallocate($thumbnail, 255, 255, 255);
        imagefilledrectangle($thumbnail, 0, 0, $this->width, $this->height, $white);

        imagecopyresampled(
            $thumbnail,
            $image,
            0,
            0,
            $sourceX,
            $sourceY,
            $this->width,
            $this->height,
            $cropWidth,
            $cropHeight
        );

        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }

        imagejpeg($thumbnail, $target, 85);
    }
}
