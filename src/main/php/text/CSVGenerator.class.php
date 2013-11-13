<?php namespace text;
 
use io\Stream;


/**
 * This class can be used to easily create correct csv-files.
 * It handles escaping of special characters and thus creates
 * csv-files, that can be used to be exchanged with other OSes
 * 
 * @test    xp://net.xp_framework.unittest.text.CsvGeneratorTest
 * @see     xp://text.parser.CSVParser
 * @purpose Small and simple CSV Generator
 * @deprecated Use the text.csv package instead
 */ 
class CSVGenerator extends \lang\Object {
  public
    $stream;
    
  public
    $colDelim= '|',
    $lineDelim = "\n",  // on unix-based systems we expect an \n as delimiter
    $escape= '"';
  
  public
    $colName;
    
  public
    $headerWritten= false,
    $delimWritten= true;
  
  /**
   * Set the output stream. The stream must be writeable. If the
   * stream is not open, it will be opened.
   *
   * @param   io.Stream stream
   * @return  bool success
   */    
  public function setOutputStream($stream) {
    if (!$stream->isOpen()) $stream->open (STREAM_MODE_WRITE);
    $this->stream= $stream;
    return true;
  }
  
  /**
   * Sets another column delimiter (standard is pipe "|").
   *
   * @param   string delim
   */
  public function setColDelimiter($delim) {
    $this->colDelim= $delim{0};
  }
  
  /**
   * Sets another line delimiter (standard is "\n")
   * 
   * if we want to generate files for other oses as unix, we need to change the delimiter
   * e.g. windows: "\r\n"
   *
   * @param  string delim 
   */
  public function setLineDelimiter($delim) {
    $this->lineDelim= $delim; // could be more than one character
  }

  /**
   * Sets the header information. The keys in this array will be
   * used to write the records, so be sure they are named exactly
   * as the data.
   *
   * @param   array header
   */    
  public function setHeader($array) {
    $this->colName= $array;
    $this->headerWritten= false;
  }

  /**
   * Returns whether we have header information available
   *
   * @return  bool hasHeader
   */    
  protected function _hasHeader() {
    return (isset ($this->colName) && !empty ($this->colName));
  }

  /**
   * Writes the header line.
   *
   */    
  protected function _writeHeader() {
    $this->stream->write(
      implode ($this->colDelim, array_values ($this->colName))
    );
    // Insert Newline
    $this->stream->write($this->lineDelim);
    $this->headerWritten= true;
  }

  /**
   * Write a single column into the stream. This function takes
   * care of quotedness and escaping.
   *
   * @param   string data
   */    
  protected function _writeColumn($data= '') {
    if (!$this->delimWritten) $this->stream->write ($this->colDelim);
    $this->delimWritten= false;

    if (0 == strlen ($data)) {
      return;
    }
    
    $mustQuote= false;
    if (false !== strstr ($data, $this->colDelim)) $mustQuote= true;
    if (false !== strstr ($data, $this->escape)) $mustQuote= true;
    if (false !== strstr ($data, "\n")) $mustQuote= true;
    
    if ($mustQuote) {
      $data= '"'.str_replace ($this->escape, $this->escape.$this->escape, $data).'"';
    }
    
    $this->stream->write ($data);
  }

  /**
   * Writes a record into the stream.
   *
   * @param   array data
   * @throws  lang.XPException e if any error occurs
   */    
  public function writeRecord($data) {
    if ($this->_hasHeader() && !$this->headerWritten)
      $this->_writeHeader();
  
    $cols= array_keys ($data);
    
    if ($this->_hasHeader())
      $cols= array_keys ($this->colName);
  
    foreach ($cols as $idx => $colName) {
      if (isset ($data[$colName]))
        $this->_writeColumn ($data[$colName]);
      else
        $this->_writeColumn('');
    }
    
    // Insert Newline
    $this->stream->write($this->lineDelim);
    $this->delimWritten= true;
  }
}
