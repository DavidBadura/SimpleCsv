<?php

namespace DavidBadura\SimpleCsv;

/**
 *
 * @author David Badura <d.badura@gmx.de>
 */
class CsvParser implements \Iterator
{
    /**
     * @var string
     */
    static private $bom = "\xef\xbb\xbf";

    /**
     *
     * @var array
     */
    private $charsets = array(
        'ISO-8859-1' => 'de_DE.iso885915@euro'
    );

    /**
     *
     * @var string
     */
    protected $file;

    /**
     *
     * @var string
     */
    protected $delimiter;

    /**
     *
     * @var array
     */
    protected $header;

    /**
     *
     * @var int
     */
    protected $pointer;

    /**
     *
     * @var array
     */
    protected $row;

    /**
     *
     * @var resource
     */
    protected $handle;

    /**
     *
     * @var boolean
     */
    protected $loaded = false;

    /**
     *
     * @var string|null
     */
    protected $charset = null;

    /**
     *
     * @param string $file
     * @param string $delimiter
     * @param string $charset
     * @throws \Exception
     */
    public function __construct($file, $delimiter = ',', $charset = null)
    {
        $this->file      = $file;
        $this->delimiter = $delimiter;
        $this->charset   = $charset == 'UTF-8' ? null : $charset ;

        if ($this->charset && !isset($this->charsets[$this->charset])) {
            throw new \Exception(sprintf('charset "%s" is not supported', $this->charset));
        }
    }

    /**
     *
     * @return array
     */
    public function getHeader()
    {
        $this->load();
        return $this->header;
    }

    /**
     *
     * @throws \Exception
     */
    public function load()
    {
        if ($this->loaded) {
            return;
        }

        $this->pointer = 0;

        if (!file_exists($this->file)) {
            throw new \Exception(sprintf('file "%s" not exists', $this->file));
        }

        $this->handle = fopen($this->file, 'r');

        if (!$this->handle) {
            throw new \Exception(sprintf('file "%s" can not read', $this->file));
        }

        $this->header = $this->stripBom($this->getNextCsvRow());

        $this->loaded = true;
    }

    /**
     *
     * @return array
     */
    public function current()
    {
        return $this->row();
    }

    /**
     *
     * @return int
     */
    public function key()
    {
        return $this->pointer;
    }

    /**
     *
     */
    public function next()
    {
        $this->row = null;
        $this->pointer++;
    }

    /**
     *
     */
    public function rewind()
    {
        $this->loaded = false;
    }

    /**
     *
     * @return boolean
     */
    public function valid()
    {
        return ($this->row() !== false) && ($this->row() !== null);
    }

    /**
     *
     * @return int
     */
    public function getLine()
    {
        return $this->pointer + 2;
    }

    /**
     *
     * @return array
     */
    protected function row()
    {
        $this->load();

        if (!$this->row) {
            $row = $this->getNextCsvRow();

            if ($row !== false) {
                if (count($this->header) != count($row)) {
                    throw new \RuntimeException('Die Anzahl der Zeilen Felder stimmen nicht mit der Anzahl der Spaltenfelder Überein');
                }

                $this->row = array_combine($this->header, $row);
            }
        }

        return $this->row;
    }

    /**
     *
     * @return array
     */
    protected function getNextCsvRow()
    {
        if ($this->charset) {
            $oldLang = $this->getLang();
            $this->setLangByCharset($this->charset);
        }

        $row = fgetcsv($this->handle, 0, $this->delimiter);

        if ($this->charset) {
            $this->setLang($oldLang);

            if (!is_array($row)) {
                return $row;
            }

            foreach ($row as $key => $value) {
                $row[$key] = iconv($this->charset, 'UTF-8', $value);
            }
        }

        return $row;
    }

    /**
     *
     * @return string
     */
    protected function getLang()
    {
        return getenv("LANG");
    }

    /**
     *
     * @param string $lang
     */
    protected function setLang($lang)
    {
        putenv("LANG=" . $lang);
    }

    /**
     *
     * @param string $charset
     */
    protected function setLangByCharset($charset)
    {
        $this->setLang($this->charsets[$charset]);
    }

    /**
     * @param array $row
     * @return array
     */
    protected function stripBom(array $row)
    {
        if (strpos($row[0], self::$bom) === 0) {
            $row[0] = str_replace(self::$bom, '', $row[0]);
        }

        return $row;
    }

}
