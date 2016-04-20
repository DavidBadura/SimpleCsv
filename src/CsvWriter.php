<?php

namespace DavidBadura\SimpleCsv;

/**
 * @author David Badura <d.a.badura@gmail.com>
 */
class CsvWriter
{
    /**
     * @var string
     */
    private $buffer;

    /**
     * @param string $file
     */
    public function __construct($file = 'php://temp')
    {
        $this->buffer = fopen($file, 'r+');
    }

    /**
     * @param array $data
     */
    public function write(array $data)
    {
        if (!$this->buffer) {
            throw new \RuntimeException('csv file has been closed');
        }

        fputcsv($this->buffer, $data);
    }

    /**
     *
     */
    public function close() {
        if (!$this->buffer) {
            return;
        }

        fclose($this->buffer);
        $this->buffer = null;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function output()
    {
        if (!$this->buffer) {
            throw new \RuntimeException('csv file has been closed');
        }

        rewind($this->buffer);

        $csv = stream_get_contents($this->buffer);

        $this->close();

        return $csv;
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->close();
    }
}