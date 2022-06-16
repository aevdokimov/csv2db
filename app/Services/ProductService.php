<?php

namespace App\Services;

use App\DataTransferObjects\UploadCsvData;
use App\Repositories\ProductRepositoryInterface;
use phpDocumentor\Reflection\Types\Boolean;

class ProductService
{
    /**
     * Сколько продуктов за раз уходит в репозиторий
     */
    const PRODUCTS_PER_BATCH = 50;

    /**
     * Кодировка, в которой должны быть данные
     */
    const TARGET_ENCODING = 'UTF-8';
    
    /**
     * Карта полей в таблице по номеру поля в CSV
     */
    const CSV_DB_MAP = ['sku', 'title', 'level_1', 'level_2', 'level_3',
        'price', 'price_sp', 'count', 'properties', 'joint_purchases', 'units',
        'img', 'on_homepage', 'description'];

    public function __construct(
        private ProductRepositoryInterface $repository
    ) {}

    /**
     * Удаляет все данные в хранилище
     */
    public function erase(): void
    {
        $this->repository->erase();
    }

    public function importCsv(UploadCsvData $dto): void
    {
        $fh = fopen($dto->csvFilePath, 'r');
        
        // Пропускаем первую строку-заголовок
        $headLine = fgets($fh);

        $sourceEncoding = $dto->detectEncoding
            ? mb_detect_encoding(
                $headLine,
                ['UTF-8', 'Windows-1251', 'ISO-8859-1']
            )
            : self::TARGET_ENCODING;

        $batch = [];
        // Не используем fgetcsv() т.к. файл явно не валидный CSV
        while ($line = fgets($fh)) {
            
            if (!$this->_validateEncoding($sourceEncoding, $line)) {
                continue;
            }

            $fields = explode(
                $dto->separator,
                trim($line, "\", \t\n\r\0\x0B")
            );

            // Невалидные строки скипаем
            if ($fields = $this->_validProductFields($fields, $dto->separator)) {
                $batch[] = $fields;

                if (count($batch) >= self::PRODUCTS_PER_BATCH) {
                    $this->repository->save($batch);
                    $batch = [];
                }
            }
        }
        
        $this->repository->save($batch);

        fclose($fh);
    }

    /**
     * Перекодирует строку в целевую кодировку если она отличается от исходной,
     * или проверяет на соответствие строки заявленной кодировке
     *
     * @param string $sourceEncoding исходная кодировка строки
     * @param string $line строка
     * @return boolean соответствует ли $sourceEncoding нужной кодировке
     */
    private function _validateEncoding(string $sourceEncoding, string &$line): bool
    {
        if (self::TARGET_ENCODING !== $sourceEncoding) {
            $line = iconv($sourceEncoding, self::TARGET_ENCODING, $line);
        } else {
            if (!mb_check_encoding($line, self::TARGET_ENCODING)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Приводит поля к валидному состоянию с ключами из self::CSV_DB_MAP
     * или возвращает false
     *
     * @param array $fields поля прочитанные из строки CSV-файла
     * @param string $separator разделитель CSV
     * @return array|false массив полей продукта либо false
     */
    private function _validProductFields(array $fields, string $separator): array|false
    {
        if (count($fields) < 14) {
            return false;
        }

        // Если полей > 14, значит в описании был символ-разделитель
        for ($i = 14; $i < count($fields); $i++) {
            $fields[13] = $fields[13].$separator.' '.$fields[$i];
        }
        $fields = array_slice($fields, 0, 14);

        foreach ($fields as &$field) {
            $field = trim($field, '"');
            $field = str_replace('\\""', '"', $field);
            $field = preg_replace(
                ['@(\d)(?:",|,")(\d)@', '@",([^ ])@', '@\s\s+@'],
                ['$1,$2', ', $1', ' '],
                $field
            );
        }

        // Меняем ключи
        $fields = array_combine(self::CSV_DB_MAP, $fields);
        
        $fields['price'] = floatval($fields['price']);
        $fields['price_sp'] = floatval($fields['price_sp']);
        $fields['count'] = intval($fields['count']);
        
        if ($fields['joint_purchases'] !== '1') {
            $fields['joint_purchases'] = '0';
        }

        if ($fields['on_homepage'] !== '1') {
            $fields['on_homepage'] = '0';
        }

        return $fields;
    } 
}
