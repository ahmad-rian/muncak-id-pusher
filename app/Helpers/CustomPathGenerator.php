<?php

namespace App\Helpers;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator as BasePathGenerator;

class CustomPathGenerator implements BasePathGenerator
{
	/**
	 * Get the path for the given media, relative to the root storage path.
	 *
	 * @param \Spatie\MediaLibrary\MediaCollections\Models\Media $media
	 *
	 * @return string
	 */
	public function getPath(Media $media): string
	{
		return $media->uuid . '/';
	}

	/**
	 * Get the path for conversions of the given media, relative to the root storage path.
	 *
	 * @param \Spatie\MediaLibrary\MediaCollections\Models\Media $media
	 *
	 * @return string
	 */
	public function getPathForConversions(Media $media): string
	{
		return $media->uuid . '/conversions/';
	}

	/**
	 * Get the path for responsive images of the given media, relative to the root storage path.
	 *
	 * @param \Spatie\MediaLibrary\MediaCollections\Models\Media $media
	 *
	 * @return string
	 */
	public function getPathForResponsiveImages(Media $media): string
	{
		return $media->uuid . '/responsive-images/';
	}
}
