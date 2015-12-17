<?php
// Copyright 2015 MyOddWeb.com.
// All Rights Reserved.
//
// Redistribution and use in source and binary forms, with or without
// modification, are permitted provided that the following conditions are
// met:
//
//     * Redistributions of source code must retain the above copyright
// notice, this list of conditions and the following disclaimer.
//     * Redistributions in binary form must reproduce the above
// copyright notice, this list of conditions and the following disclaimer
// in the documentation and/or other materials provided with the
// distribution.
//     * Neither the name of MyOddWeb.com nor the names of its
// contributors may be used to endorse or promote products derived from
// this software without specific prior written permission.
//
// THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
// "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
// LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
// A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
// OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
// SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
// LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
// DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
// THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
// (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
// OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
//
// Author: Florent Guelfucci

namespace MyOddWeb;

class BigNumberIteratorException extends \Exception{};

class BigNumberIterator implements \Iterator
{
  /**
   * The number of items in the array
   */
  private $_size = 0;

  /**
   * Our current position in the array.
   * @var int
   */
  private $_position = 0;

  /**
   * All the numbers in our array.
   * @var array[int] array of unsigned numbers.
   */
  private $_numbers = [];

  public function __construct()
  {
    // reset the position
    $this->_position = 0;
  }

  /**
   * Reset the position to the beginning of the array.
   * {@inheritDoc}
   * @see Iterator::rewind()
   */
  public function rewind()
  {
    //  reset the position
    $this->_position = 0;
  }

  /**
   * Get the current number at the current position.
   * {@inheritDoc}
   * @see Iterator::current()
   */
  public function current()
  {
    return $this->_numbers[$this->_position];
  }

  /**
   * Get the current key
   * {@inheritDoc}
   * @see Iterator::key()
   */
  public function key()
  {
    return $this->_position;
  }

  /**
   * Move forward to the next item.
   * {@inheritDoc}
   * @see Iterator::next()
   */
  public function next()
  {
    ++$this->_position;
  }

  /**
   * Check if there is a valid number at the position.
   * {@inheritDoc}
   * @see Iterator::valid()
   * @return boolean if there is a number at the position.
   */
  public function valid()
  {
    return isset($this->_numbers[$this->_position]);
  }

  /**
   * Return the number of items in the array
   * slightly faster than count(...)
   * @return number the number of numbers in the array
   */
  public function size()
  {
    return $this->_size;
  }

  /**
   * Get the raw array of data.
   * @return array[int] the numbers array.
   */
  public function raw()
  {
    return $this->_numbers;
  }

  /**
   * Make sure that a number is valid.
   * @param number $what the number we want to validate.
   * @throws BigNumberIteratorException if the number cannot be added to the array.
   */
  private function ValidateNumber( $what )
  {
    // this must be a number
    if( !is_int($what))
    {
      throw new BigNumberIteratorException( "You must insert a number" );
    }

    // the number _must_ be between 0 and 9
    if( $what < 0 || $what > 9 )
    {
      throw new BigNumberIteratorException( "You must insert a number between 0 and 9 only." );
    }
  }

  /**
   * Get a value at a certain position.
   * @param number $where where we are getting the number.
   * @return boolean|number either the number or false.
   */
  public function at( $where )
  {
    if( !is_int($where) )
    {
      throw new BigNumberIteratorException( "The position has to be an integer." );
    }
    if( $where < 0 )
    {
      throw new BigNumberIteratorException( "The position cannot be negative." );
    }

    // is it past the end?
    if( $where >= $this->size() )
    {
      return false;
    }

    // return it at position
    return $this->_numbers[$where];
  }

  public function erase( $where )
  {
    if( !is_int($where) )
    {
      throw new BigNumberIteratorException( "The position has to be an integer." );
    }
    if( $where < 0 )
    {
      throw new BigNumberIteratorException( "The position cannot be negative." );
    }

    // is it past the end?
    if( $where >= $this->size() )
    {
      throw new BigNumberIteratorException( "You cannot erase past the end position." );
    }

    // @see http://www.php.net/manual/en/function.array-splice.php
    array_splice( $this->_numbers, $where, 1 );

    // we removed one item
    --$this->_size;

    // return the new size
    return $this->size();
  }

  /**
   * Add the number at the end f the array
   * @param number $what the number we wish to add.
   * @return number the new array size.
   */
  public function push_back( $what )
  {
    // validate this number
    $this->ValidateNumber($what);

    // add it at the end of the array
    $this->_numbers[] = $what;

    // the size has been updated
    ++$this->_size;

    // return the new size
    return $this->size();
  }

  /**
   * Insert a number at a given position
   * @param number $where where we want to insert the number
   * @param number $what the number we want to add.
   * @return number the new size of the array
   */
  public function insert( $where, $what )
  {
    // can't insert past the end.
    if( $where > $this->size() )
    {
      throw new BigNumberIteratorException( "You cannot insert a number past the end of the array" );
    }

    if( $where == $this->size() )
    {
      return $this->push_back($what);
    }

    // can 'reverse' insert.
    if( $where < 0  )
    {
      throw new BigNumberIteratorException( "You cannot insert a number before the start of the array" );
    }

    // validate this number
    $this->ValidateNumber($what);

    // @see http://www.php.net/manual/en/function.array-splice.php
    array_splice( $this->_numbers, $where, 0, (array)$what );

    // we have one new item
    ++$this->_size;

    // return the new size
    return $this->size();
  }
}
?>