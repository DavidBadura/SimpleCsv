<?php

namespace DavidBadura\SimpleCsv;

/**
 *
 * @author David Badura <d.badura@gmx.de>
 */
class CsvParser implements \Iterator
{

    protected $file;
    protected $delimiter;
    protected $header;
    protected $pointer;
    protected $row;
    protected $handle;
    protected $loaded = false;
    protected $charset = null;

    public function __construct($file, $delimiter = ',', $charset = null)
    {
        $this->file = $file;
        $this->delimiter = $delimiter;
        $this->charset = $charset;
    }

    public function getHeader()
    {
        $this->load();
        return $this->header;
    }

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

    public function current()
    {
        return $this->row();
    }

    public function key()
    {
        return $this->pointer;
    }

    public function next()
    {
        $this->row = null;
        $this->pointer++;
    }

    public function rewind()
    {
        $this->loaded = false;
    }

    public function valid()
    {
        return ($this->row() !== false);
    }

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

