<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 16-5-27 下午5:23
 */
namespace Runner\MonologReader;

use Runner\MonologReader\Parser\ParserInterface;

class Logger implements \Iterator
{

    /**
     * @var \SplFileObject
     */
    protected $file;

    /**
     * @var ParserInterface
     */
    protected $parser = null;

    /**
     * @var string
     */
    protected $pattern = '/\[([\d\s:\-]+)\]\s(\w+)\.(\w+)\:\s(.*?)\s([\[\{].*?[\]\}])\s([\[\{].*?[\]\}])/';


    /**
     * Logger constructor.
     * @param \SplFileObject $splFileObject
     */
    public function __construct(\SplFileObject $splFileObject)
    {
        $this->file = $splFileObject;
    }


    /**
     * @param ParserInterface $parserInterface
     * @return $this
     */
    public function setParser(ParserInterface $parserInterface)
    {
        $this->parser = $parserInterface;

        return $this;
    }


    /**
     * @param string $row
     * @return array
     */
    protected function parseRow($row)
    {
        $match = null;

        if(0 === preg_match($this->pattern, $row, $match)) {
            return [
                'date'    => '',
                'logger'  => '',
                'level'   => '',
                'message' => '',
                'context' => '',
                'extra'   => '',
            ];
        }

        array_shift($match);
        list($date, $logger, $level, $message, $context, $extra) = $match;
        unset($match);

        if(!is_null($this->parser)) {
            $message = $this->parser->messageHandle($message);
            $context = $this->parser->messageHandle($context = json_decode($context, true));
            $extra   = $this->parser->messageHandle($extra   = json_decode($extra, true));
        }

        return [
            'date'    => strtotime($date),
            'logger'  => $logger,
            'level'   => $level,
            'message' => $message,
            'context' => $context,
            'extra'   => $extra,
        ];
    }


    /**
     * @return array
     */
    public function current()
    {
        return $this->parseRow($this->file->current());
    }


    /**
     * @return void
     */
    public function next()
    {
        $this->file->next();
    }


    /**
     * @return int
     */
    public function key()
    {
        return $this->file->key();
    }


    /**
     * @return bool
     */
    public function valid()
    {
        return (!$this->file->eof() && "\n" != $this->file->current() && !empty($this->file->current()));
    }


    /**
     * @return void
     */
    public function rewind()
    {
        $this->file->rewind();
    }
}
