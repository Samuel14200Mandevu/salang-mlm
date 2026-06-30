<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageUploadService
{
    /**
     * Uploader une image de produit
     */
    public function uploadProductImage($file, $name = null)
    {
        if (!$file || !$file->isValid()) {
            return null;
        }

        // Générer un nom unique
        $extension = $file->getClientOriginalExtension();
        $filename = time() . '_' . Str::slug($name ?? 'product') . '.' . $extension;

        // Sauvegarder l'image
        try {
            // Sauvegarder dans storage/app/public/products
            $path = $file->storeAs('public/products', $filename);
            
            // Vérifier si l'image a été sauvegardée
            if ($path && Storage::disk('public')->exists('products/' . $filename)) {
                return $filename;
            }
            
            return null;
        } catch (\Exception $e) {
            \Log::error('Erreur upload image: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Supprimer une image
     */
    public function deleteProductImage($filename)
    {
        if ($filename && Storage::disk('public')->exists('products/' . $filename)) {
            Storage::disk('public')->delete('products/' . $filename);
            return true;
        }
        return false;
    }

    /**
     * Vérifier si une image existe
     */
    public function imageExists($filename)
    {
        return $filename && Storage::disk('public')->exists('products/' . $filename);
    }

    /**
     * Obtenir l'URL d'une image
     */
    public function getImageUrl($filename)
    {
        if ($filename && $this->imageExists($filename)) {
            return asset('storage/products/' . $filename);
        }
        return asset('images/no-image.png');
    }
}
