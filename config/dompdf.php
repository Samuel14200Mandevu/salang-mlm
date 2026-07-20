<?php

return [
    'enable' => true,
    'default_paper_size' => 'a4',
    'default_font' => 'DejaVu Sans', // Police compatible UTF-8
    
    // Options pour mobile
    'is_remote_enabled' => true,
    'is_html5_parser_enabled' => true,
    'is_php_enabled' => false,
    
    // Marges pour mobile
    'default_paper_orientation' => 'portrait',
    
    // Police par défaut pour mobile
    'default_font_size' => 12,
    
    // Encodage
    'font_cache' => storage_path('fonts'),
];