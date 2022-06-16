<?php

namespace App\DataTransferObjects;

use Spatie\DataTransferObject\DataTransferObject;

class UploadCsvData extends DataTransferObject
{
    public function __construct(
        #[Required]
        public string $csvFilePath,    
        #[Required, Max(1)]
        public string $separator,    
        public bool $detectEncoding = false
    ) {}
}