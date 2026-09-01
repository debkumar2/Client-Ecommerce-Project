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

if (!function_exists('uploadToCloudinary')) {
    /**
     * Upload a local file path or image URL to Cloudinary CDN using environment API credentials.
     *
     * @param string $fileOrUrl Absolute local path to file OR remote HTTP URL
     * @param string $folder Destination folder in Cloudinary
     * @return array Result containing ['success' => bool, 'url' => string, 'error' => string]
     */
    function uploadToCloudinary(string $fileOrUrl, string $folder = 'products'): array {
        $cloudName = trim(env('CLOUDINARY_CLOUD_NAME', ''));
        $apiKey    = trim(env('CLOUDINARY_API_KEY', ''));
        $apiSecret = trim(env('CLOUDINARY_API_SECRET', ''));

        if (empty($cloudName) || empty($apiKey) || empty($apiSecret)) {
            return ['success' => false, 'error' => 'Cloudinary credentials missing in .env configuration.'];
        }

        $timestamp = time();
        $paramsToSign = [
            'folder' => $folder,
            'timestamp' => $timestamp,
        ];
        ksort($paramsToSign);

        $stringToSign = '';
        foreach ($paramsToSign as $k => $v) {
            $stringToSign .= "{$k}={$v}&";
        }
        $stringToSign = rtrim($stringToSign, '&') . $apiSecret;
        $signature = sha1($stringToSign);

        $postData = [
            'api_key'   => $apiKey,
            'timestamp' => $timestamp,
            'folder'    => $folder,
            'signature' => $signature
        ];

        if (file_exists($fileOrUrl)) {
            $postData['file'] = new \CURLFile($fileOrUrl);
        } else {
            $postData['file'] = $fileOrUrl;
        }

        $ch = curl_init("https://api.cloudinary.com/v1_1/{$cloudName}/image/upload");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return ['success' => false, 'error' => 'cURL Error: ' . $err];
        }

        $result = json_decode($response, true);
        if (!empty($result['secure_url'])) {
            return [
                'success'   => true,
                'url'       => $result['secure_url'],
                'public_id' => $result['public_id'] ?? '',
                'data'      => $result
            ];
        } elseif (!empty($result['error']['message'])) {
            return ['success' => false, 'error' => 'Cloudinary Error: ' . $result['error']['message']];
        }

        return ['success' => false, 'error' => 'Unknown Cloudinary upload error'];
    }
}
