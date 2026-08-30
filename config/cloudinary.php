<?php
/**
 * Cloudinary Image Helper Configuration
 * 
 * Provides secure frontend image URL generation using Cloudinary CDN.
 * IMPORTANT: NEVER expose CLOUDINARY_API_SECRET to the frontend.
 */

if (!function_exists('cloudinary_url')) {
    /**
     * Generate an optimized Cloudinary image URL or fallback URL.
     *
     * @param string $publicIdOrUrl Public ID or full URL of the image
     * @param array $options Transformation options: width, height, crop, format, quality
     * @return string Secure optimized image URL
     */
    function cloudinary_url(string $publicIdOrUrl = '', array $options = []): string {
        if (empty($publicIdOrUrl)) {
            // Default placeholder image
            $width = $options['width'] ?? 800;
            $height = $options['height'] ?? 600;
            return "https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w={$width}&h={$height}&q=80";
        }

        // If it's already a full HTTP/HTTPS URL, check if Cloudinary Cloud Name is set to wrap it or return as-is
        if (str_starts_with($publicIdOrUrl, 'http://') || str_starts_with($publicIdOrUrl, 'https://')) {
            $cloudName = env('CLOUDINARY_CLOUD_NAME');
            if (empty($cloudName)) {
                return $publicIdOrUrl;
            }

            // Build Cloudinary fetch URL
            $transforms = [];
            if (!empty($options['width'])) $transforms[] = 'w_' . (int)$options['width'];
            if (!empty($options['height'])) $transforms[] = 'h_' . (int)$options['height'];
            if (!empty($options['crop'])) $transforms[] = 'c_' . $options['crop'];
            $transforms[] = 'f_' . ($options['format'] ?? 'auto');
            $transforms[] = 'q_' . ($options['quality'] ?? 'auto');

            $transformStr = implode(',', $transforms);
            return "https://res.cloudinary.com/{$cloudName}/image/fetch/{$transformStr}/" . urlencode($publicIdOrUrl);
        }

        // Otherwise, construct from Cloudinary public ID
        $cloudName = env('CLOUDINARY_CLOUD_NAME', 'biswas-enterprise');
        $transforms = [];
        if (!empty($options['width'])) $transforms[] = 'w_' . (int)$options['width'];
        if (!empty($options['height'])) $transforms[] = 'h_' . (int)$options['height'];
        if (!empty($options['crop'])) $transforms[] = 'c_' . ($options['crop'] ?? 'fill');
        $transforms[] = 'f_' . ($options['format'] ?? 'auto');
        $transforms[] = 'q_' . ($options['quality'] ?? 'auto');

        $transformStr = implode(',', $transforms);
        return "https://res.cloudinary.com/{$cloudName}/image/upload/{$transformStr}/" . ltrim($publicIdOrUrl, '/');
    }
}
