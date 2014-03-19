<?php

namespace DavidBadura\SimpleCsv;

/**
 *
 * @author David Badura <d.badura@gmx.de>
 */
class CsvParser implements \Iterator
{

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
     */
    public function __construct($file, $delimiter = ',', $charset = null)
    {
        $this->file = $file;
        $this->delimiter = $delimiter;
        $this->charset = $charset;
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

        $this->header = fgetcsv($this->handle, 0, $this->delimiter);

        if($this->header) {
            $this->header = $this->convertArray($this->header);
        }

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
        return ($this->row() !== false);
    }

    /**
     *
     * @return array
     */
    protected function row()
    {
        $this->load();
        if (!$this->row) {
            $this->row = fgetcsv($this->handle, 0, $this->delimiter);
            if ($this->row !== false) {
                $this->row = $this->convertArray($this->row);
                $this->row = array_combine($this->header, $this->row);
            }
        }
        return $this->row;
    }

    /**
     *
     * @param array $array
     * @return array 
     */
    protected function convertArray(array $array)
    {
        if(!$this->charset) {
            return $array;
        }

        foreach($array as $key => $value) {
            $array[$key] = iconv($this->charset, 'UTF-8', $value);
        }

        return $array;
    }

}

