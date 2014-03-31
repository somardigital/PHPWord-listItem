<?php
/**
 * PHPWord
 *
 * @link        https://github.com/PHPOffice/PHPWord
 * @copyright   2014 PHPWord
 * @license     http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt LGPL
 */

namespace PhpOffice\PhpWord;

use PhpOffice\PhpWord\Exception\Exception;

/**
 * IO factory
 */
abstract class IOFactory
{
    /**
     * Create new writer
     *
     * @param \PhpOffice\PhpWord\PhpWord $phpWord
     * @param string $name
     * @return \PhpOffice\PhpWord\Writer\IWriter
     * @throws \PhpOffice\PhpWord\Exception\Exception
     */
    public static function createWriter(PhpWord $phpWord, $name)
    {
        if ($name !== 'IWriter' && $name !== 'ODText' && $name !== 'RTF' && $name !== 'Word2007') {
            throw new Exception("\"{$name}\" is not a valid writer.");
        }

        $fqName = "PhpOffice\\PhpWord\\Writer\\{$name}";
        return new $fqName($phpWord);
    }

    /**
     * Create new reader
     *
     * @param string $name
     * @return \PhpOffice\PhpWord\Reader\IReader
     * @throws \PhpOffice\PhpWord\Exception\Exception
     */
    public static function createReader($name)
    {
        if ($name !== 'IReader' && $name !== 'Word2007') {
            throw new Exception("\"{$name}\" is not a valid reader.");
        }

        $fqName = "PhpOffice\\PhpWord\\Reader\\{$name}";
        return new $fqName();
    }

    /**
     * Loads PhpWord from file
     *
     * @param string $filename The name of the file
     * @param string $readerName
     * @return \PhpOffice\PhpWord
     */
    public static function load($filename, $readerName = 'Word2007')
    {
        $reader = self::createReader($readerName);
        return $reader->load($filename);
    }
}