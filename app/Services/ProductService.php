<?php

namespace App\Services;

use App\DataTransferObjects\UploadCsvData;
use App\Exceptions\UnprocessableEncodingException;
use App\Repositories\ProductRepositoryInterface;

class ProductService
{
    /**
     * Сколько столбцов должно быть. Если меньше - строка не валидна,
     * если больше - в последнем поле был разделитель CSV и надо его склеить
     */
    public const REQ_COLUMNS_COUNT = 14;

    /**
     * Сколько продуктов за раз уходит в репозиторий
     */
    public const PRODUCTS_PER_BATCH = 50;

    /**
     * Кодировка, в которой должны быть данные
     */
    public const TARGET_ENCODING = 'UTF-8';
    
    /**
     * Карта полей в таблице по номеру поля в CSV
     */
    public const CSV_DB_MAP = ['sku', 'title', 'level_1', 'level_2', 'level_3',
        'price', 'price_sp', 'count', 'properties', 'joint_purchases', 'units',
        'img', 'on_homepage', 'description'];

    public function __construct(
        private ProductRepositoryInterface $repository
    ) {}

    /**
     * Удаляет все данные в хранилище
     */
    public function truncate(): void
    {
        $this->repository->truncate();
    }

    public function importCsv(UploadCsvData $uploadCsvData): void
    {
        $fh = fopen($uploadCsvData->csvFilePath, 'r');
        
        // Пропускаем первую строку-заголовок
        $headLine = fgets($fh);

        // Определяем кодировку по первой строке, если надо
        $sourceEncoding = $uploadCsvData->detectEncoding
            ? mb_detect_encoding(
                $headLine,
                ['UTF-8', 'Windows-1251', 'ISO-8859-1']
            )
            : self::TARGET_ENCODING;

        $batch = [];
        
        // Не используем fgetcsv() т.к. файл явно не валидный CSV
        while ($line = fgets($fh)) {
            try {
                $line = $this->normalizeEncoding($line, $sourceEncoding);
            } catch (UnprocessableEncodingException $exception) {
                continue;
            }

            $fields = explode(
                $uploadCsvData->separator,
                // К стандартным символам trim() добавляем кавычку и запятую
                trim($line, "\", \t\n\r\0\x0B")
            );

            // Неполные строки скипаем
            if (count($fields) < self::REQ_COLUMNS_COUNT) {
                continue;
            }

            // Склеиваем колонку-описание, если она разделилось
            $fields = $this->fitToReqColumns($fields, $uploadCsvData->separator);

            $fields = $this->prettifyProductFields($fields);

            // Меняем численные ключи на нужные нам
            $batch[] = array_combine(self::CSV_DB_MAP, $fields);	

            if (count($batch) >= self::PRODUCTS_PER_BATCH) {
                $this->repository->save($batch);
                $batch = [];
            }
        }
        
        $this->repository->save($batch);

        fclose($fh);
    }

    /**
     * Перекодирует строку в целевую кодировку если она отличается от исходной
     *
     * @param string $sourceEncoding исходная кодировка строки
     * @param string $line строка
     * @return string строка в нужной кодировке
     */
    private function normalizeEncoding(string $line, string $sourceEncoding): string
    {
        if (self::TARGET_ENCODING !== $sourceEncoding) {
            $line = iconv($sourceEncoding, self::TARGET_ENCODING, $line);
        }
        
        if (!mb_check_encoding($line, self::TARGET_ENCODING)) {
            throw new UnprocessableEncodingException('Unknown encoding', 422);
        }        

        return $line;
    }

    /**
     * Поля, идущие после последнего, склеиваются в него. Лишние поля появляются
     * из-за символа-разделителя внутри поля
     *
     * @param array $fields
     * @return array
     */
    private function fitToReqColumns(array $fields, string $separator): array
    {
        for ($i = self::REQ_COLUMNS_COUNT; $i < count($fields); $i++) {
            $fields[self::REQ_COLUMNS_COUNT - 1] = $fields[self::REQ_COLUMNS_COUNT - 1] . $separator . ' ' . $fields[$i];
        }
        $fields = array_slice($fields, 0, self::REQ_COLUMNS_COUNT);

        return $fields;
    }

    /**
     * Приводит поля к валидному состоянию: удаляет лишние символы, ставит 0
     * в пустых boolean полях
     *
     * @param array $fields поля прочитанные из строки CSV-файла
     * @param string $separator разделитель CSV
     * @return array массив полей продукта
     */
    private function prettifyProductFields(array $fields): array
    {
        foreach ($fields as &$field) {
            $field = trim($field, '"');
            $field = str_replace('\\""', '"', $field);
            $field = preg_replace(
                ['@(\d)(?:",|,")(\d)@', '@",([^ ])@', '@\s\s+@'],
                ['$1,$2', ', $1', ' '],
                $field
            );
        }

        // price
        $fields[5] = floatval($fields[5]);
        // price_sp
        $fields[6] = floatval($fields[6]);
        // count
        $fields[7] = intval($fields[7]);
        
        // joint_purchases
        if ($fields[9] !== '1') {
            $fields[9] = '0';
        }

        // on_homepage
        if ($fields[12] !== '1') {
            $fields[12] = '0';
        }

        return $fields;
    } 
}
