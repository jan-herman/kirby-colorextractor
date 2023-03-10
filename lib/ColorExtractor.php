<?php

namespace JanHerman\ColorExtractor;

use ColorThief\ColorThief;

use Kirby\Cms\Files;

class ColorExtractor {

	public static function extractColor($image, $mode, $fallbackColor) {
        $colors = [];

		if($image->isResizable()) {
            if(in_array($mode, ['dominant', 'both'])) {
                $colors[] = static::processImage($image, 300, $fallbackColor);
            }
            // Size below 10 seems to fail / timeout with ColorThief...
            if(in_array($mode, ['average', 'both'])) {
                $colors[] = static::processImage($image, 10, $fallbackColor);
            }

			$image->update(['color' => implode(',', $colors)]);
		}
	}

    public static function processImage($image, $size, $fallbackColor) {
        $thumb     = $image->width() > $image->height() ? $image->resize(null, $size) : $image->resize($size);
        $thumb     = $thumb->save();
        $root      = $thumb->root();

        // ColorThief::getColor($sourceImage[, $quality=10, $area=null, $outputFormat='array', $adapter = null])
        $dominantColor = ColorThief::getColor($root, 10, null, 'hex', null);

        return $dominantColor;
    }

    public static function getFilesIndex() {
        $published = site()->index()->images();
        $drafts    = site()->drafts()->images();
        $images    = new Files(array($published, $drafts));
        $images    = $images->filterBy('extension', 'in', ['png', 'jpg', 'jpeg', 'gif', 'webp']);

        return $images;
    }

}
