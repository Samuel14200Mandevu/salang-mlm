<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class ImageHelper
{
    /**
     * Obtenir l'URL d'une image de produit
     */
    public static function getProductImage($image, $default = null)
    {
        if (empty($image)) {
            return self::getDefaultImage();
        }

        // Vérifier si l'image existe dans storage
        if (Storage::disk('public')->exists('products/' . $image)) {
            return asset('storage/products/' . $image);
        }

        // Vérifier si l'image existe dans public
        if (file_exists(public_path('storage/products/' . $image))) {
            return asset('storage/products/' . $image);
        }

        return self::getDefaultImage();
    }

    /**
     * Obtenir l'image par défaut
     */
    public static function getDefaultImage()
    {
        return asset('images/no-image.png');
    }

    /**
     * Obtenir l'extension d'une image
     */
    public static function getImageExtension($image)
    {
        if (empty($image)) {
            return 'png';
        }
        $ext = pathinfo($image, PATHINFO_EXTENSION);
        return $ext ?: 'png';
    }

    /**
     * Vérifier si l'image est un SVG
     */
    public static function isSvg($image)
    {
        return strtolower(self::getImageExtension($image)) === 'svg';
    }

    /**
     * Obtenir les classes CSS pour l'image
     */
    public static function getImageClasses($additional = '')
    {
        return 'w-full h-full object-cover transition-transform duration-300 group-hover:scale-105 ' . $additional;
    }
}
