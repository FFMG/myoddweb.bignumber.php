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

require_once 'bignumberiterator.php';
require_once 'bignumberconstants.php';

class BigNumberException extends \Exception{};

function BigNumber()
{
  $rc=new \ReflectionClass('\MyOddWeb\BigNumber');
  return $rc->newInstanceArgs( func_get_args() );
}

class BigNumber
{
  /**
   * All the numbers in our number.
   * @var bignumberiterator $_numebrs
   */
  protected $_numbers = null;

  /**
   * The base of the big number, (base 10, 2, 16 etc...)
   * @var number $_base
   */
  protected $_base = 10;

  /**
   * If the number is negative or not.
   * @var boolean $_neg
   */
  protected $_neg = false;

  /**
   * If the number is not a number of not, (like 1/0 for example).
   * @var boolean $_nan
   */
  protected $_nan = false;

  /**
   * If the number is zero or not.
   * @var boolean $_zero
   */
  protected $_zero = false;

  /**
   * The number of decimal places in our array of numbers.
   * @var int the number of decimal places.
   */
  protected $_decimals = 0;

  public function __construct()
  {
    $num = func_num_args();
    switch ( $num )
    {
    case 0:
      $this->_Default();
      break;

    case 1:
      $arg = func_get_arg(0);
      if( is_null($arg))
      {
        throw new BigNumberException( "The given argument cannot be NULL." );
      }
      else
      if( is_string($arg ))
      {
        $this->_ParseString($arg );
      }
      else
      if( is_numeric($arg ))
      {
        $this->_ParseNumber($arg );
      }
      else
      {
        throw new BigNumberException( "Unknown argument type." );
      }
      break;

    case 3:
      $numbers = func_get_arg(0);
      $decimals = func_get_arg(1);
      $neg = func_get_arg(2);
      $this->_ParseArray($numbers, $decimals, $neg );
      break;

    default:
      // just make this a zero
      $this->_ParseNumber( 0 );
      break;
    }

    // the number is just zero.
  }

  public function __clone() {
    $this->_numbers = clone $this->_numbers;
  }

  /**
   * Copy all the values from the source on
   * @param BigNumber $src the source we are copying from.
   * @return none;
   */
  protected function _Copy( $src )
  {
    $this->_base = $src->_base;
    $this->_decimals = $src->_decimals;
    $this->_nan = $src->_nan;
    $this->_neg = $src->_neg;
    $this->_numbers = $src->_numbers;
    $this->_zero = $src->_zero;
  }

  /**
   * Reset all the values to their default.
   * This will clear all the flags and values.
   */
  protected function _Default()
  {
    $this->_base = 10;
    $this->_neg = false;
    $this->_nan = false;
    $this->_zero = false;
    $this->_decimals = 0;
    $this->_numbers = new \MyOddWeb\BigNumberIterator();
  }

  /**
   * Create a BigNumber from a string.
   * @throws BigNumberException
   * @param number $source the string we want to create from.
   * @return none.
   */
  protected function _ParseNumber( $source )
  {
    if( !is_numeric($source))
    {
      throw new BigNumberException( "This function expects a number." );
    }

    // we can then part the string.
    $this->_ParseString( strval($source) );
  }

  /**
   * Create a big number using an array of number.
   * @param array[int] $numbers the array of numbers.
   * @param number $decimals the decimal places.
   * @param boolean $neg if this is a negative number or not.
   * @throws BigNumberException if one or more values are invalid.
   */
  protected function _ParseArray( $numbers, $decimals, $neg )
  {
    // check the array of numbers.
    if( !is_array($numbers))
    {
      throw new BigNumberException( "The numbers must be an array." );
    }

    // check the flag and decimals.
    if( !is_numeric($decimals) || $decimals < 0 )
    {
      throw new BigNumberException( "The number of decimals must be a non negative number." );
    }

    if( !is_bool($neg))
    {
      throw new BigNumberException( "The negative flag must be true/false." );
    }

    // do the default
    $this->_Default();

    // copy the values.
    $this->_neg = (boolean) $neg;
    $this->_decimals = (int)$decimals;

    // add the numbers
    foreach ( $numbers as $number )
    {
      $this->_numbers->push_back($number);
    }

    // clean it all up.
    $this->PerformPostOperations( $this->_decimals );
  }

  /**
   * Create a BigNumber from a string.
   * @throws BigNumberException
   * @param string $source the string we want to create from.
   * @return none.
   */
  protected function _ParseString( $source )
  {
    // do the default
    $this->_Default();

    //  sanity checks.
    if( !is_string($source))
    {
      throw new BigNumberException( "The given variable is not a string." );
    }

    // is it NaN?
    if (strcmp($source, "NaN") == 0)
    {
      // not a number
      $this->_nan = true;

      // done
      return;
    }

    // allow the +/- sign
    $allowSign = true;

    // where the decimal point is.
    $decimalPoint = -1;

    // loop around all the characters
    $sourceLen = strlen( $source );
    for( $i = 0; $i < $sourceLen; $i++ )
    {
      $char = substr( $source, $i, 1 );
      if (true == $allowSign)
      {
        if ($char == '-')
        {
          $this->_neg = true;
          $allowSign = false;
          continue;
        }
        if ($char == '+')
        {
          $this->_neg = false;
          $allowSign = false;
          continue;
        }
      }

      // is it a space?
      if ( $char == ' ')
      {
        // then it we can just move on
        continue;
      }

      // decimal
      if ( $decimalPoint == -1 && $char == '.')
      {
        $decimalPoint = $this->_numbers->size();
        if ($decimalPoint == 0)
        {
          //  make sure it is '0.xyz' rather than '.xyz'
          $this->_numbers->push_back(0);
          ++$decimalPoint;
        }
        continue;
      }

      if ( !is_numeric($char) )
      {
        throw new BigNumberException( "The given value is not a number.");
      }
      $this->_numbers->insert( 0, (int)$char );

      // either way, signs are no longer allowed.
      $allowSign = false;
    }
    // get the number of decimals.
    $this->_decimals = ($decimalPoint == -1) ? 0 : $this->_numbers->size() - $decimalPoint;

    // clean it all up.
    $this->PerformPostOperations( $this->_decimals );
  }

  /**
   * Clean up the number to remove leading zeros and unneeded trailing zeros, (for decimals).
   * @param number precision the max precision we want to set.
   * @return BigNumber the number we cleaned up.
   */
  protected function PerformPostOperations( $precision)
  {
    if ( $this->_decimals > $precision)
    {
      // trunc will call this function again.
      return $this->Trunc( $precision);
    }

    // assume that we are not zero
    $this->_zero = false;

    while ( $this->_decimals > 0)
    {
      //  get the decimal number
      $it = $this->_numbers->at(0);
      if ($it === false )
      {
        // we have no more numbers
        // we have reached the end.
        // there can be no decimals.
        $this->_decimals = 0;
        break;
      }

      if ($it != 0)
      {
        //  we are done.
        break;
      }

      // remove that number
      $this->_numbers->erase( 0 );

      // move back one decimal.
      --$this->_decimals;
    }

    // remember that the numbers are in reverse
    for (;;)
    {
      $l = $this->_numbers->size() - 1;

      // do we have a number?
      if ($l < 0 )
      {
        break;
      }

      // get the last number
      $it = $this->_numbers->at( $l );

      if ( $it != 0)
      {
        //  we are done.
        break;
      }

      // remove that 'leading' zero.
      $this->_numbers->erase( $l );
    }

    //  are we zero?
    $l = $this->_numbers->size();
    if ($l == 0)
    {
      //  this is empty, so the number _must_ be zero
      $this->_neg = false;
      $this->_zero = true;
      $this->_decimals = 0;
      $this->_numbers->push_back(0);
      ++$l;
    }

    while ($l < $this->_decimals+1)
    {
      //  this is empty, so the number _must_ be zero
      $this->_numbers->push_back(0);
      ++$l;
    }

    //  return this number.
    return $this;
  }

  /**
   * return if the number is an integer or not.
   * @see https://en.wikipedia.org/wiki/Integer
   * @return bool
   */
  public function IsInteger()
  {
    // if we have no decimals, we are an int
    // but we must also be a valid number.
    // zero is also an integer.
    return ($this->_decimals == 0 && !$this->IsNan());
  }

  /**
   * return if the number is zero or not
   * @return bool
   */
  public function IsZero()
  {
    return $this->_zero;
  }

  /**
   * return if the number is not a number.
   * @return bool
   */
  public function IsNan()
  {
    return $this->_nan;
  }

  /**
   * return if the number is negative or not
   * @return bool
   */
  public function IsNeg()
  {
    return $this->_neg;
  }

  /**
   * Fast check if we are an odd number.

   * @return bool if this is an odd or even number.
   */
  public function IsOdd()
  {
    // if we are NaN then we are not odd or even
    if ($this->IsNan())
    {
      return false;
    }

    //  if we are not even, we are odd.
    return !$this->IsEven();
  }

  /**
   * Fast check if we are an even number.
   * Faster than using Mod(2).IsZero() as it does not do a full divide.
   * @return bool if this is an odd or even number.
   */
  public function IsEven()
  {
    // if we are NaN then we ar not odd or even
    if ($this->IsNan())
    {
      return false;
    }

    // get the first non decimal number.
    $c = $this->_numbers->at( 0 + $this->_decimals );

    // is that number even?
    return (($c % 2) == 0);
  }

  /**
   * Transform the number into absolute number.
   * @return BigNumber this non negative number.
   */
  public function Abs()
  {
    //  we are not negative
    $this->_neg = false;

    // done.
    return $this;
  }

  /**
   * Convert a big number to an integer.
   * @return number the converted number to an int.
   */
  public function ToInt()
  {
    if ( $this->IsNan())
    {
      //  c++ does not have a Nan() number.
      return 0;
    }

    // the return number.
    $number = 0;

    // the total number of items.
    $l = $this->_numbers->size();

    // go around each number and re-create the integer.
    foreach ( array_reverse($this->_numbers->raw() ) as $c )
    {
      $number = $number * $this->_base + $c;

      // have we reached the decimal point?
      // if we have then we must stop now as all
      // we are after is the integer.
      if ( --$l - $this->_decimals == 0 )
      {
        break;
      }
    }
    return $this->IsNeg() ? -1 * $number : $number;
  }

  /**
   * Convert a big number to a double.
   * @return double the converted number to a double.
   */
  public function ToDouble()
  {
    if ($this->IsNan())
    {
      //  php does not have a Nan() number.
      return 0;
    }

    // the return number.
    return doubleval( $this->ToString() );
  }

  /**
   * Convert a big number to an integer.
   * @return string the converted number to a string.
   */
  public function ToString()
  {
    if ( $this->IsNan())
    {
      return "NaN";
    }

    // the return number
    $number = "";

    // the total number of items.
    $l = $this->_numbers->size();

    // go around each number and re-create the integer.
    foreach ( array_reverse($this->_numbers->raw() ) as $c )
    {
      $number .= strval((int)$c);
      if (--$l - $this->_decimals == 0 && $l != 0 )  //  don't add it right at the end...
      {
        $number .= '.';
      }
    }
    return $this->IsNeg() ? ('-' . $number) : $number;
  }

  /**
   * Create a BigNumber value from a given variable.
   * @param varried $src the variable we want to make sure is changed to BigNumber
   * @return BigNumber the big number.
   */
  static protected function FromValue( &$src )
  {
    // if we are already a big number, then there is nothing else to do.
    if( $src instanceof BigNumber )
    {
      return $src;
    }

    // make this value a BigNumber value
    $src = new BigNumber( $src );
    return $src;
  }

  /**
   * Compare two number ignoring the sign.
   * @param const BigNumber lhs the left hand side number
   * @param const BigNumber rhs the right hand size number
   * @return int -ve rhs is greater, +ve lhs is greater and 0 = they are equal.
   */
  static protected function AbsCompare( $lhs, $rhs)
  {
    // make sure that they are big numbers.
    $lhs = static::FromValue($lhs);
    $rhs = static::FromValue($rhs);

    $ll = $lhs->_numbers->size();
    $rl = $rhs->_numbers->size();

    $maxDecimals = (int)($lhs->_decimals >= $rhs->_decimals ? $lhs->_decimals : $rhs->_decimals);
    $lhsDecimalsOffset = $maxDecimals - (int)$lhs->_decimals;
    $rhsDecimalsOffset = $maxDecimals - (int)$rhs->_decimals;

    if ($ll+$lhsDecimalsOffset > $rl + $rhsDecimalsOffset) {
      return 1;
    }
    if ($ll + $lhsDecimalsOffset < $rl + $rhsDecimalsOffset) {
      return -1;
    }

    if ($ll == 0 ) {
      return 0; //  they both zero len
    }

    // compare the whole numbers first.
    // because we know these are the same len, (they have to be).
    // otherwise the numbers above would not have worked.
    for ($i = (int)($ll- $lhs->_decimals -1); $i >= 0; --$i)
    {
      // get the numbers past the multiplier.
      $ucl = $lhs->_numbers->at( $i+ $lhs->_decimals);
      $ucr = $rhs->_numbers->at( $i+ $rhs->_decimals);
      if ($ucl == $ucr) //  still the same number
      {
        continue;
      }

      //  123 > 113
      if ($ucl > $ucr)
      {
        return 1;
      }

      //  123 < 133
      if ($ucl < $ucr)
      {
        return -1;
      }
    }

    // ok so the two whole numbers are the same
    // something like 20.123 and 20.122
    // we now know that 20=20
    // but we must now compare the decimal points.
    // unlike the above when we go in reverse, in the case we must go forward 122 < 123.
    // the number of decimals might also not match.
    for ($i = $maxDecimals -1; $i >= 0 ; --$i )
    {
      $ucl = ($i - $lhsDecimalsOffset < 0) ? 0 : $lhs->_numbers->at($i - $lhsDecimalsOffset);
      $ucr = ($i - $rhsDecimalsOffset < 0) ? 0 : $rhs->_numbers->at($i - $rhsDecimalsOffset);
      if ($ucl == $ucr) //  still the same number
      {
        continue;
      }

      //  .123 > .113
      if ($ucl > $ucr)
      {
        return 1;
      }

      //  .123 < .133
      if ($ucl < $ucr)
      {
        return -1;
      }
    }

    // they are the same
    return 0;
  }

  /**
   * Compare this number to the number given
   * @see AbsCompare( ... )
   * +ve = *this > rhs
   * -ve = *this < rhs
   *   0 = *this == rhs
   * @param const BigNumber the number we are comparing to.
   * @return number the comparaison, +/- or zero.
   */
  public function Compare( $rhs )
  {
    // make sure that rhs is a BigNumber
    $rhs = static::FromValue( $rhs );

    // do an absolute value compare.
    $compare = static::AbsCompare( $this, $rhs );

    switch ( $compare )
    {
    case 0:
      // they look the same, but if their signs are not the same
      // then they are not really the same.
      if ($this->IsNeg() != $rhs->IsNeg())
      {
        // the Abs value is the same, but not the sign
        // -2 != 2 or 2 != -2
        if ($this->IsNeg())
        {
          // we are negative, rhs is not, so we are less.
          $compare = -1;
        }
        else
        {
          // we are positive, rhs is not, so we are more.
          $compare = 1;
        }
      }
      break;

    case 1:
      //  it looks like we are greater
      // but if the sign is not the same we might actualy be smaller.
      if ($this->IsNeg() != $rhs->IsNeg())
      {
        // -2 < 1 or 2 != -2
        if ($this->IsNeg())
        {
          // whatever the number, we are smaller.
          $compare = -1;
        }
        else
        {
          // we are indeed bigger, because the other number is even smaller, (negative).
          $compare = 1;
        }
      }
      else
      {
        // negative numbers are oposite.
        // -5 < -3 but |-5| > |-3|
        if ($this->IsNeg())
        {
          $compare = -1;
        }
      }
      break;

    case -1:
      // it looks like we are smaller
      // but if the sign is not the same we might actually be smaller.
      if ($this->IsNeg() != $rhs->IsNeg())
      {
        // -5 < 6
        if ($this->IsNeg())
        {
          // whatever the number, we are indeed smaller.
          $compare = -1;
        }
        else
        {
          // we are bigger because rhs is negative
          // 5 > -7
          $compare = 1;
        }
      }
      else
      {
        // negative numbers are opposite.
        // -3 > -5 but |-3| < |-5|
        if ($this->IsNeg())
        {
          $compare = 1;
        }
      }
      break;

    default:
      break;
    }

    return $compare;
  }

  /**
   * Check if this number is equal to the given number.
   * @see BigNumber::Compare( ... )
   * @param const BigNumber& rhs the number we are comparing to.
   * @return bool if the 2 numbers are the same.
   */
  public function IsEqual( $rhs )
  {
    return (0 == $this->Compare($rhs));
  }

  /**
   * Check is a number does not equal another number.
   * @see BigNumber::Compare( ... )
   * @param const BigNumber& rhs the number we are comparing to.
   * @return bool if the 2 numbers are not the same.
   */
  public function IsUnequal( $rhs)
  {
    return (0 != $this->Compare($rhs));
  }

  /**
   * Check if this number is greater than the one given.
   * @see BigNumber::Compare( ... )
   * @param const BigNumber& rhs the number we are comparing to.
   * @return bool if this number is greater than the given number
   */
  public function IsGreater( $rhs)
  {
    return (1 == $this->Compare($rhs));
  }

  /**
   * Check if this number is smaler than the one given.
   * @see BigNumber::Compare( ... )
   * @param const BigNumber& rhs the number we are comparing to.
   * @return bool if this number is smaller
   */
  public function IsLess( $rhs)
  {
    return (-1 == $this->Compare( $rhs ));
  }

  /**
   * Check if this number is greater or equal to the rhs
   * @see BigNumber::Compare( ... )
   * @param const BigNumber& rhs the number we are comparing to.
   * @return bool
   */
  public function IsGreaterEqual( $rhs)
  {
    $compare = $this->Compare( $rhs );
    return ($compare == 0 || $compare == 1);
  }

  /**
   * Compare if a number is less or equal
   * @see BigNumber::Compare( ... )
   * @param const BigNumber& rhs the number we are comparing to.
   * @return bool if this number is smaller or equal to this number.
   */
  public function IsLessEqual( $rhs )
  {
    $compare = $this->Compare( $rhs );
    return ($compare == 0 || $compare == -1);
  }

  /**
   * Calculate the remainder when 2 numbers are divided.
   * @param const BigNumber denominator the denominator dividing this number
   * @param BigNumber the remainder of the division.
   */
  public function Mod( $denominator)
  {
    // validate the value.
    $denominator = static::FromValue($denominator);

    // quick shortcut for an often use function.
    if ( $denominator->Compare( BigNumberConstants::Two() ) == 0)
    {
      // use this function, it is a lot quicker.
      return $this->IsEven() ? BigNumberConstants::Zero() : BigNumberConstants::One();
    }

    // calculate both the quotient and remainder.
    $quotient = new BigNumber();
    $remainder = new BigNumber();
    static::QuotientAndRemainder($this, $denominator, $quotient, $remainder);

    // return the remainder
    return $remainder;
  }

  /**
   * Calculate the quotient when 2 numbers are divided.
   * @param const BigNumber denominator the denominator dividing this number
   * @param BigNumber the quotient of the division.
   */
  public function Quotient( $denominator)
  {
    // validate the value.
    $denominator = static::FromValue($denominator);

    // calculate both the quotient and remainder.
    $quotient = new BigNumber();
    $remainder = new BigNumber();
    static::QuotientAndRemainder($this, $denominator, $quotient, $remainder);

    // return the quotient
    return $quotient;
  }

  /**
   * Calculate the quotien and remainder of a division
   * @see https://en.wikipedia.org/wiki/Modulo_operation
   * @param const BigNumber numerator the numerator been devided.
   * @param const BigNumber denominator the denominator dividing the number.
   * @param BigNumber quotient the quotient of the division
   * @param BigNumber remainder the remainder.
   */
  static function QuotientAndRemainder( $numerator, $denominator, &$quotient, &$remainder)
  {
    $numerator = static::FromValue($numerator);
    $denominator = static::FromValue($denominator);
    $quotient = static::FromValue($quotient);
    $remainder = static::FromValue($remainder);

    // do it all positive
    static::AbsQuotientAndRemainder($numerator, $denominator, $quotient, $remainder);

    // clean up the quotient and the remainder.
    if ( !$denominator->IsZero())
    {
      if ($numerator->IsNeg())
      {
        // 10 modulo -3 = -2
        $remainder->_neg = true;
      }
    }
  }

  /**
   * Devide By Base, effectively remove a zero at the end.
   * 50 (base16) / 10 (base16) = 5
   * 50 (base10) / 10 (base10) = 5
   * if the number is smaller then we need to add zeros.
   * 5 (base10) / 10 (base10) = 0.5
   * @param number divisor the number of times we are multiplying this by.
   */
  protected function DevideByBase( $divisor )
  {
    // set the decimals
    $this->_decimals += $divisor;

    // check that the length is valid
    $l = $this->_numbers->size();
    while ($l < $this->_decimals + 1)
    {
      $this->_numbers->push_back( 0 );
      ++$l;
    }
    $this->PerformPostOperations( $this->_decimals );
  }

  /**
   * Multiply By Base, effectively add a zero at the end.
   * 5 (base16) * 10 (base16) = 50
   * 5 (base10) * 10 (base10) = 50
   * @param number multiplier the number of times we are multiplying this by.
   */
  protected function MultiplyByBase( $multiplier)
  {
    //  shortcut...
    if ($multiplier == $this->_decimals)
    {
      $this->_decimals = 0;
      $this->PerformPostOperations( 0 );
      return;
    }

    // muliply by _base means that we are shifting the multipliers.
    while ( $this->_decimals > 0 && $multiplier > 0 )
    {
      --$this->_decimals;
      --$multiplier;
    }

    // if we have any multipliers left,
    // keep moving by adding zeros.
    for ($i = 0; $i < $multiplier; ++$i)
    {
      $this->_numbers->insert( 0, 0);
    }

    //  clean up
    $this->PerformPostOperations( $this->_decimals );
  }

  /**
   * Work out the bigest denominator we can use for the given remainder
   * This is not a stand alone function, the values are calculated based on the value given to us.
   * @param BigNumber max_denominator the current denominator, if it is too big we will devide it by base
   * @param BigNumber base_multiplier the current multiplier, it the denominator is too big, we will divide it as well.
   * @param const BigNumber remainder the current remainder value.
   * @return bool if we can continue using the values or if we must end now.
   */
  static protected function _RecalcDenominator( $max_denominator, $base_multiplier, $remainder)
  {
    $max_denominator = static::FromValue($max_denominator);
    $base_multiplier = static::FromValue($base_multiplier);
    $remainder = static::FromValue($remainder);

    // are done with this?
    if ($remainder->IsZero())
    {
      return false;
    }

    // if the max denominator is greater than the remained
    // then we must devide by _base.
    $compare = static::AbsCompare($max_denominator, $remainder);
    switch ( $compare )
    {
      case 0:
        //  it is the same!
        // the remainder has to be zero
        return true;

        // we cannot subtract the max_denominator as it is greater than the remainder.
        // so we divide it so we can look for a smaller number.
      case 1:
        // divide all by _base
        $max_denominator->DevideByBase(1);
        $base_multiplier->DevideByBase(1);

        // have we reached the end of the division limits?
        if ( !$base_multiplier->IsInteger() )
        {
          // the number is no longer an integer
          // meaning that we have divided it to the maximum.
          return false;
        }

        // compare the value again, if it is _still_ too big, then go around divide it again.
        // this causes recursion, but it should never hit any limits.
        $compare = static::AbsCompare($max_denominator, $remainder);
        if ($compare != -1)
        {
          return static::_RecalcDenominator( $max_denominator, $base_multiplier, $remainder);
        }

        // the number is now small enought and can be used.
        return true;
    }

    //  still big enough.
    return true;
  }

  /**
   * Calculate the quotien and remainder of a division
   * @see https://en.wikipedia.org/wiki/Modulo_operation
   * @param const BigNumber numerator the numerator been devided.
   * @param const BigNumber denominator the denominator dividing the number.
   * @param BigNumber quotient the quotient of the division
   * @param BigNumber remainder the remainder.
   */
  static protected function AbsQuotientAndRemainder($numerator, $denominator, &$quotient, &$remainder)
  {
    $numerator = static::FromValue($numerator);
    $denominator = static::FromValue($denominator);
    $quotient = static::FromValue($quotient);
    $remainder = static::FromValue($remainder);

    // check if we can actually do this, it should work for all
    // but we need to test it first...
    if ($numerator->_base != 10 || $numerator->_base != $denominator->_base)
    {
      throw new BigNumberException( "This function was only tested with base 10!");
    }

    // are we trying to divide by zero?
    if ($denominator->IsZero())
    {
      // those are not value numbers.
      $remainder = new BigNumber( "NaN" );
      $quotient = new BigNumber( "NaN" );
      return;
    }

    // reset the quotient to 0.
    $quotient = new BigNumber(0);

    // and set the current remainder to be the numerator.
    // that way we know that we can return now something valid.
    // 20 % 5 = 0 ('cause 5*4 = 20 remainder = 0)
    // we need the number to be positive for now.
    $remainder = clone $numerator;
    $remainder->_neg = false;

    // if the numerator is greater than the denominator
    // then there is nothing more to do, we will never be able to
    // divide anything and have a quotient
    // the the remainder has to be the number and the quotient has to be '0'
    // so 5 % 20 = 5 ( remainder = 5 / quotient=0 = 0*20 + 5)
    if (static::AbsCompare($numerator, $denominator) < 0)
    {
      return;
    }

    // do a 'quick' remainder calculations.
    //
    // 1- look for the 'max' denominator.
    //    we need the number to be positive.
    $max_denominator = clone $denominator;
    $max_denominator->_neg = false;
    $base_multiplier = BigNumberConstants::One();

    while ( static::AbsCompare($max_denominator, $numerator) < 0)
    {
      $max_denominator->MultiplyByBase(1);
      $base_multiplier->MultiplyByBase(1);
    }

    // 2- subtract, (if need be, then update the quotient accordingly).
    for (;;)
    {
      // make sure that the max denominator and multiplier
      // are still within the limits we need.
      if (!static::_RecalcDenominator( $max_denominator, $base_multiplier, $remainder))
      {
        break;
      }

      // we can still remove this amount from the loop.
      $remainder->Sub( $max_denominator);

      // and add the quotient.
      $quotient->Add( $base_multiplier);
    }

    for (;;)
    {
      $f = static::AbsSub($remainder, $denominator);
      if ($f->IsNeg())
      {
        //  that's it, removing that number would
        // cause the number to be negative.
        // so we cannot remove anymore.
        break;
      }

      // we added it one more time
      $quotient->Add(1);

      // set the new value of the remainder.
      $remainder = $f;
    }

    // clean up the quotient and the remainder.
    $remainder->PerformPostOperations( $remainder->_decimals );
    $quotient->PerformPostOperations( $quotient->_decimals );
  }

  /**
   * Add a big number to this number.
   * @param const BigNumber rhs the number we want to add.
   * @return BigNumber *this number to allow chainning
   */
  public function Add( $rhs )
  {
    $rhs = static::FromValue($rhs);

    if ($this->IsNeg() == $rhs->IsNeg() )
    {
      //  both +1 or both -1
      // -1 + -1 = -1 * (1+1)
      // 1 + 1 = 1 * (1+1)
      $this->_Copy( static::AbsAdd( $rhs, $this ) );

      // the sign of *this will be lost and become
      // positive, (this is what the function does).
      // but we can use the one from rhs as that was not changed.
      $this->_neg = $rhs->_neg;

      // return this/cleaned up.
      return $this->PerformPostOperations( $this->_decimals );
    }

    // both numbers are not the same sign
    // compare the absolute values.
    //
    if (static::AbsCompare( $this, $rhs ) >= 0 )
    {
      //  save the sign
      $neg = $this->IsNeg();

      //  10 + -5 = this._neg * (10 - 5)  = 5
      //  -10 + 5 = this._neg * (10 - 5)  = -5
      $this->_Copy( static::AbsSub($this, $rhs ) );

      // set the sign
      $this->_neg = $neg;

      // return this/cleaned up.
      return $this->PerformPostOperations( $this->_decimals );
    }

    //  save the sign
    $neg = $rhs->IsNeg();

    //  5 + -10 = this._neg * (10 - 5)  = -5
    //  -5 + 10 = this._neg * (10 - 5)  = 5
    $this->_Copy( static::AbsSub($rhs, $this) );

    // set the sign
    $this->_neg = $neg;

    // return this/cleaned up.
    return $this->PerformPostOperations( $this->_decimals );
  }

  /**
   * Multiply this number to the given number.
   * @param const BigNumber the number we are multiplying to.
   * @param size_t precision the presision we want to use.
   * @return BigNumber this number.
   */
  public function Mul( $rhs, $precision = 100 )
  {
    $rhs = static ::FromValue($rhs);

    // if one of them is negative, but not both, then it is negative
    // if they are both the same, then it is positive.
    // we need to save the value now as the next operation will make it positive
    $neg = ($rhs->IsNeg() != $this->IsNeg());

    // just multiply
    $this->_Copy( static::AbsMul($this, $rhs, $precision ) );

    // set the sign.
    $this->_neg = $neg;

    // return this/cleaned up.
    return $this->PerformPostOperations( $precision );
  }

  /**
   * Devide this number by the given number.
   * @param BigNumber $rhs the number we want to devide this by
   * @param number $precision the max precision we wish to reache.
   * @return BigNumber this number devided.
   */
  public function Div( $rhs, $precision = 100 )
  {
    $rhs = static::FromValue($rhs);

    // if one of them is negative, but not both, then it is negative
    // if they are both the same, then it is positive.
    // we need to save the value now as the next operation will make it positive
    $neg = ($rhs->IsNeg() != $this->IsNeg());

    // just multiply
    $this->_Copy( static::AbsDiv( $this, $rhs, $precision ) );

    // set the sign.
    $this->_neg = $neg;

    // return this/cleaned up.
    return $this->PerformPostOperations( $precision );
  }

  /**
   * Substract a big number from this number.
   * @param const BigNumber rhs the number we want to substract.
   * @return BigNumber *this number to allow chainning
   */
  public function Sub($rhs)
  {
    // make sure that the value is bignumber
    $rhs = static::FromValue($rhs);

    // if they are not the same sign then we add them
    // and save the current sign
    if ($this->IsNeg() != $rhs->IsNeg())
    {
      // save the sign
      $neg = $this->IsNeg();

      //  5 - -10 = this._neg * (10 + 5)  = 15
      //  -5 - 10 = this._neg * (10 + 5)  = -15
      $this->_Copy( static::AbsAdd($rhs, $this) );

      // set the sign
      $this->_neg = $neg;

      // return this/cleaned up.
      return $this->PerformPostOperations( $this->_decimals );
    }

    // both signs are the same, check if the absolute numbers.
    // if lhs is greater than rhs then we can do a subtraction
    // using our current sign
    if (static::AbsCompare($this, $rhs) >= 0)
    {
      //  save the sign
      $neg = $this->IsNeg();

      //  -10 - -5 = this._neg * (10 - 5)  = -5
      //  10 - 5 = this._neg * (10 - 5)  = 5
      $this->_Copy( static::AbsSub($this, $rhs) );

      // set the sign
      $this->_neg = $neg;

      // return this/cleaned up.
      return $this->PerformPostOperations( $this->_decimals );
    }

    // in this case asb(rhs) is greater than abs(lhs)
    // so we must use the oposite sign of rhs

    //  save the sign
    $neg = $rhs->IsNeg();

    //  -5 - -10 = !rhs._neg * (10 - 5)  = 5
    //  5 - 10 = !rhs._neg * (10 - 5)  = -5
    $this->_Copy( static::AbsSub( $rhs, $this) );

    // set the oposite sign
    $this->_neg = !$neg;

    // return this/cleaned up.
    return $this->PerformPostOperations( $this->_decimals );
  }

  /**
   * Multiply 2 absolute numbers together.
   * @param BigNumber rhs the number been multiplied
   * @param BigNumber rhs the number multipling
   * @param size_t precision the max precision to stop once the limit is reached.
   * @return BigNumber the product of the two numbers.
   */
  protected static function AbsDiv( $lhs, $rhs, $precision = 100 )
  {
    $lhs = static::FromValue($lhs);
    $rhs = static::FromValue($rhs);

    // lhs / 0 = nan
    if ( $rhs->IsZero())
    {
      // lhs / 0 = nan
      $c = new BigNumber();
      $c->_nan = true;
      return $c;
    }

    // 0 / n = 0
    if ($lhs->IsZero())
    {
      // lhs / 0 = nan
      return BigNumberConstants::Zero();
    }

    // any number divided by one is one.
    if ( $rhs->Compare( BigNumberConstants::One() ) == 0)
    {
      // lhs / 1 = lhs
      return $lhs;
    }

    // the decimal place.
    $decimals = 0;

    // the result
    $c = [];

    // the number we are working with.
    $number = clone $lhs;
    $number->_neg = false;

    // quotien/remainder we will use.
    $quotient = new BigNumber();
    $remainder = new BigNumber();

    // divide until we are done ... or we reached the presision limit.
    for (;;)
    {
      // get the quotient and remainder.
      static::QuotientAndRemainder($number, $rhs, $quotient, $remainder);

      // add the quotien to the current number.
      foreach( $quotient->_numbers as $number )
      {
        array_unshift( $c, $number );
      }

      //  are we done?
      if ($remainder->IsZero())
      {
        break;
      }

      //
      $number = clone $remainder;
      $number->MultiplyByBase( 1 );

      // have we reached our limit?
      if ($decimals >= $precision)
      {
        break;
      }

      //  the number of decimal
      ++$decimals;
    }

    // then create the result with the known number of decimals.
    return new BigNumber( $c, $decimals, false );
  }

  /**
   * Multiply 2 absolute numbers together.
   * @param const BigNumber rhs the number been multiplied
   * @param const BigNumber rhs the number multipling
   * @param size_t precision the max precision we want to use.
   * @return BigNumber the product of the two numbers.
   */
  protected static function AbsMul( $lhs, $rhs, $precision )
  {
    $rhs = static::FromValue( $rhs );
    $lhs = static::FromValue( $lhs );

    // if either number is zero, then the total is zero
    // that's the rule.
    if ($lhs->IsZero() || $rhs->IsZero())
    {
      //  zero * anything = zero.
      return BigNumberConstants::Zero();
    }

    // anything multiplied by one == anything
    if (static::AbsCompare($lhs, BigNumberConstants::One() ) == 0) // 1 x rhs = rhs
    {
      return $rhs;
    }
    if (static::AbsCompare($rhs, BigNumberConstants::One()) == 0) // lhs x 1 = lhs
    {
      return $lhs;
    }

    $maxDecimals = (int)($lhs->_decimals >= $rhs->_decimals ? $lhs->_decimals : $rhs->_decimals);

    // if we have more than one decimals then we have to shift everything
    // by maxDecimals * _base
    // this will allow us to do the multiplication.
    if ($maxDecimals > 0 )
    {
      // the final number of decimals is the total number of decimals we used.
      // 10.12 * 10.12345=102.4493140
      // 1012 * 1012345 = 1024493140
      // decimals = 2 + 5 = 102.4493140
      $decimals = $lhs->_decimals + $rhs->_decimals;

      // copy the lhs with no decimals
      $tlhs = clone $lhs;
      $tlhs->MultiplyByBase( $lhs->_decimals);

      // copy the rhs with no decimals
      $trhs = clone $rhs;
      $trhs->MultiplyByBase( $rhs->_decimals );

      // do the multiplication without any decimals.
      $c = static::AbsMul( $tlhs, $trhs, 0);

      //  set the current number of decimals.
      $c->DevideByBase( $decimals );

      // return the value.
      return $c->PerformPostOperations( $precision );
    }

    //  15 * 5  = 5*5 = 25 = push(5) carry_over = 2
    //          = 5*1+ccarry_over) = 7 push(7)
    //          = 75

    //  15 * 25  = 5*5             = 25 = push(5) carry_over = 2
    //           = 5*1+ccarry_over =  7 = push(7) carry_over = 0
    //           = 75
    //           = 2*5             = 10 = push(0) carry_over = 1
    //           = 2*1+ccarry_over =  3 = push(3) carry_over = 0
    //           = 30 * _base
    //           = 300+75=375

    // the two sizes
    $ll = $lhs->_numbers->size();
    $rl = $rhs->_numbers->size();

    // the return number
    $c = new BigNumber();
    $shift = 7;
    $max_base = 10000000;

    $shifts = [];
    for ( $x = 0; $x < $ll; $x+= $shift)
    {
      // this number
      $numbers = [];

      // and the carry over.
      $carryOver = 0;

      // get the numbers.
      $lhs_number = $lhs->_MakeNumberAtIndex( $x, $shift );

      for ( $y = 0; $y < $rl; $y += $shift)
      {
        $rhs_number = $rhs->_MakeNumberAtIndex($y, $shift);
        $sum = $lhs_number * $rhs_number + $carryOver;
        $carryOver = (int)((int)$sum / (int)$max_base);

        for ($z = 0; $z < $shift; ++$z )
        {
          $s = $sum % $rhs->_base;
          $numbers[] = $s;

          $sum = (int)((int)$sum / (int)$rhs->_base);
        }
      }

      // add the carry over if we have one
      while ($carryOver > 0)
      {
        $s = $carryOver % $rhs->_base;
        $numbers[] = $s;
        $carryOver = (int)((int)$carryOver / (int)$rhs->_base);
      }

      // shift everything
      foreach( $shifts as $numberToAdd )
      {
        array_unshift( $numbers, $numberToAdd );
      }

      for ($z = 0; $z < $shift; ++$z) {
        $shifts[] = 0;
      }

      // then add the number to our current total.
      $c = static::AbsAdd($c, new BigNumber($numbers, 0, false));
    }

    // this is the number with no multipliers.
    return $c->PerformPostOperations( $precision );
  }

  protected function _MakeNumberAtIndex( $index, $length)
  {
    $number = 0;
    $l = $this->_numbers->size();
    for ( $i = ($length-1); $i >= 0; --$i)
    {
      $pos = ($i + $index);
      if ($pos >= $l)
      {
        continue;
      }
      $number = ($number * $this->_base) + $this->_numbers->at( $pos );
    }
    return $number;
  }


  /**
   * Add 2 absolute numbers together.
   * @param const BigNumber lhs the number been Added from
   * @param const BigNumber rhs the number been Added with.
   * @return BigNumber the sum of the two numbers.
   */
  protected static function AbsAdd( $lhs, $rhs)
  {
    $lhs = static::FromValue($lhs);
    $rhs = static::FromValue($rhs);

    // the carry over
    $carryOver = 0;

    // get the maximum number of decimals.
    $maxDecimals = (int)($lhs->_decimals >= $rhs->_decimals ? $lhs->_decimals : $rhs->_decimals);

    $numbers = [];
    for ($i = 0;; ++$i)
    {
      $l = $lhs->_At( $i, $maxDecimals);
      $r = $rhs->_At( $i, $maxDecimals);
      if ($l === 255 && $r === 255)
      {
        break;
      }

      $l = ($l == 255) ? 0 : $l;
      $r = ($r == 255) ? 0 : $r;

      $sum = $l + $r + $carryOver;

      $carryOver = 0;
      if ($sum >= $lhs->_base)
      {
        $sum -= $lhs->_base;
        $carryOver = 1;
      }
      $numbers[] = $sum;
    }

    if ( $carryOver > 0)
    {
      $numbers[] = 1;
    }

    // this is the new numbers
    return new BigNumber( $numbers, $maxDecimals, false );
  }

  /**
   * Subtract 2 absolute numbers together.
   * @param const BigNumber lhs the number been subtracted from
   * @param const BigNumber rhs the number been subtracted with.
   * @return BigNumber the diff of the two numbers.
   */
  protected static function AbsSub( $lhs, $rhs)
  {
    $lhs = static::FromValue($lhs);
    $rhs = static::FromValue($rhs);

    // compare the 2 numbers
    if (static::AbsCompare($lhs, $rhs) < 0 )
    {
      // swap the two values to get a positive result.
      $c = static::AbsSub($rhs, $lhs);

      // but we know it is negative
      $c->_neg = true;

      // return the number
      return $c->PerformPostOperations( $c->_decimals );
    }

    // if we want to subtract zero from the lhs, then the result is rhs
    if ( $rhs->IsZero() )
    {
      return $lhs;
    }

    // we know that lhs is greater than rhs.
    $carryOver = 0;
    $ll = $lhs->_numbers->size();
    $rl = $rhs->_numbers->size();

    // get the maximum number of decimals.
    $maxDecimals = (int)($lhs->_decimals >= $rhs->_decimals ? $lhs->_decimals : $rhs->_decimals);
    $lhsDecimalsOffset = $maxDecimals - $lhs->_decimals;
    $rhsDecimalsOffset = $maxDecimals - $rhs->_decimals;

    $numbers = [];
    for ($i = 0;; ++$i)
    {
      if (($i - $lhsDecimalsOffset) >= $ll && ($i - $rhsDecimalsOffset) >= $rl)
      {
        break;
      }

      $l = ($i >= $lhsDecimalsOffset && $i < $ll + $lhsDecimalsOffset) ? $lhs->_numbers->at( $i - $lhsDecimalsOffset) : 0;
      $r = ($i >= $rhsDecimalsOffset && $i < $rl + $rhsDecimalsOffset) ? $rhs->_numbers->at( $i - $rhsDecimalsOffset) : 0;

      $sum = $l - $carryOver - $r;

      $carryOver = 0;
      if ($sum < 0)
      {
        $sum += $lhs->_base;
        $carryOver = 1;
      }

      $numbers[] = $sum;
    }

    // this is the new numbers
    return new BigNumber($numbers, $maxDecimals, false);
  }

  /**
   * Get a number at a certain position, (from the last digit)
   * In a number 1234.456 position #0 = 6 and #3=4
   * The expected decimal, is the number of decimal we should have, if the expected number of decimals is 5, and the
   * current number is 1234.56 then the 'actual' number is 1234.56000 so we have 5 decimal places.
   * @param size_t position the number we want.
   * @param size_t expectedDecimals the number of decimals we _should_ have, (see note).
   * @return unsigned char the number or 255 if there is no valid number.
   */
  protected function _At( $position, $expectedDecimals)
  {
    // the numbers are saved in reverse:
    //    #123 = [3][2][1]
    // decimals are the same
    //    #123.45 = [5][4][3][2][1]
    //
    // 'expectedDecimals' are decimals we are expected to have
    // wether they exist or not, does not matter.
    // so the number 123 with 2 'expected' decimals becomes
    //    #123 = [0][0][3][2][1]
    // but the number 123.45 with 2 'expected' decimals remains
    //    #123 = [5][4][3][2][1]
    //
    // so, if we are looking to item 'position'=0 and we have 2 'expectedDecimals'
    // then what we are really after is the first item '0'.
    //    #123 = [0][0][3][2][1]
    //
    $actualPosition = (int)$position - (int)$expectedDecimals + (int)$this->_decimals;

    // if that number is negative or past our limit
    // then we return 255
    if($actualPosition < 0 || $actualPosition >= $this->_numbers->size() )
    {
      return (int)255;
    }

    // we all good!
    return $this->_numbers->at( $actualPosition );
  }

  /**
   * Calculate the factorial of a non negative number
   * 5! = 5x4x3x2x1 = 120
   * @see https://en.wikipedia.org/wiki/Factorial
   * @param size_t precision the precision we want to use.
   * @return BigNumber the factorial of this number.
   */
  public function Factorial( $precision = 100 )
  {
    if ($this->IsNeg())
    {
      // we cannot do the factorial of a negative number
      $this->_nan = true;

      // then return this number
      return $this;
    }

    // is it zero
    if ($this->IsZero())
    {
      // The value of 0!is 1, according to the convention for an empty product
      $this->_Copy( BigNumberConstants::One() );

      // then return this number
      return $this;
    }

    // the factorial.
    $c = clone $this;

    while (static::AbsCompare( $this, BigNumberConstants::One() ) == 1 )
    {
      // subtract one.
      $this->Sub( BigNumberConstants::One() );

      // multiply it
      $c->Mul( $this, $precision );
    }

    // clean it all up and update our value.
    $this->_Copy( $c->PerformPostOperations( $c->_decimals ) );

    // return *this
    return $this;
  }

  /**
   * Truncate the number
   * @param size_t precision the max number of decimals.
   * @return const BigNumber the truncated number.
   */
  public function Trunc( $precision = 0 )
  {
    // does anything need to be done.
    if ($this->_decimals <= $precision)
    {
      return $this;
    }

    //  strip all the decimal.
    if ($this->_decimals > $precision)
    {
      $end = $this->_decimals - $precision;
      $this->_numbers->erase(0, $end );
      $this->_decimals -= $end;
    }

    // done.
    return $this->PerformPostOperations( $this->_decimals );
  }

  /**
   * Round a number to the nearest int, ( 1.2 > 1 && 1.8 > 2)
   * @param size_t precision the rounding prescision
   * @return BigNumber this number rounded to 'x' precision.
   */
  public function Round( $precision = 0 )
  {
    // if it is not a number than there is no rounding to do.
    if ( $this->IsNan() )
    {
      return $this;
    }

    if ( $this->IsNeg() )
    {
      $this->_neg = false;
      $this->Round($precision);
      $this->_neg = true;

      // already cleaned up.
      return $this;
    }

    // add 0.5 and floor(...) it.
    $number = new BigNumber( 5 );
    $number->DevideByBase( ($precision+1));
    $x = static::AbsAdd($number, $this);
    $this->_Copy( $x->Floor( $precision ) );

    // clean up.
    return $this->PerformPostOperations( $precision );
  }

  /**
   * Round up the number
   * @param size_t precision the precision we want to set.
   * @return const BigNumber the number rounded up.
   */
  public function Ceil($precision = 0 )
  {
    // does anything need to be done.
    if ( $this->_decimals <= $precision)
    {
      return $this;
    }

    //  strip all the decimal.
    $this->Trunc( $precision );

    // if it positive then we need to go up one more
    if (!$this->IsNeg())
    {
      $this->Add( BigNumberConstants::One() );
    }

    // done.
    return $this->PerformPostOperations( $precision );
  }

  /**
   * Round down the number
   * @param size_t precision the precision we want to set.
   * @return const BigNumber the number rounded up.
   */
  public function Floor($precision = 0 )
  {
    // does anything need to be done.
    if ($this->_decimals <= $precision)
    {
      return $this;
    }

    //  strip all the decimal.
    $this->Trunc( $precision );

    // if it negative then we need to subtract one more.
    if ($this->IsNeg())
    {
      $this->Sub( BigNumberConstants::One() );
    }

    // done.
    return $this->PerformPostOperations( $precision );
  }

  /**
   * @see https://en.wikipedia.org/wiki/E_%28mathematical_constant%29
   * @see http://www.miniwebtool.com/first-n-digits-of-pi/?number=1000
   * @uses return BigNumberConstants::e()
   * @return BigNumber e
   */
  public static function e()
  {
    return BigNumberConstants::e();
  }

  /**
   * The const value of pi to 1000 numbers
   * @see https://en.wikipedia.org/wiki/E_%28mathematical_constant%29
   * @see http://www.wolframalpha.com/input/?i=pi+to+1000+digits
   * @uses return BigNumberConstants::pi()
   * @return BigNumber pi
   */
  public static function pi()
  {
    return BigNumberConstants::pi();
  }

  /**
   * Convert a Radian number to degree
   * @see http://www.mathwarehouse.com/trigonometry/radians/convert-degee-to-radians.php
   * @param size_t precision the precision we want to limit this to.
   * @return BigNumber this number converted to a Degree number.
   */
  public function ToDegree($precision = 100 )
  {
    if ($this->IsZero())
    {
      // nothing to do, apart from trimming.
      return $this->PerformPostOperations($precision);
    }

    // get 180 / pi
    $oneEightyOverpi = static::AbsDiv(180, BigNumberConstants::pi(), BigNumberConstants::PrecisionPadding($precision));

    // the number is x * (180/pi)
    $this->Mul($oneEightyOverpi, BigNumberConstants::PrecisionPadding($precision));

    // clean up and done.
    return $this->Round($precision)->PerformPostOperations($precision);
  }

  /**
   * Convert a Degree number to radian
   * @see http://www.mathwarehouse.com/trigonometry/radians/convert-degee-to-radians.php
   * @param size_t precision the precision we want to limit this to.
   * @return BigNumber this number converted to a Radian number.
   */
  public function ToRadian($precision = 100 )
  {
    if ($this->IsZero())
    {
      // nothing to do, apart from trimming.
      return $this->PerformPostOperations($precision);
    }

    // get pi / 180
    $piOver180 = static::AbsDiv(BigNumberConstants::pi(), 180, BigNumberConstants::PrecisionPadding($precision));

    // the number is x * (pi/180)
    $this->Mul( $piOver180, BigNumberConstants::PrecisionPadding($precision) );

    // clean up and done.
    return $this->Round( $precision)->PerformPostOperations($precision);
  }

  /**
   * Convert this number to an integer
   * @see https://en.wikipedia.org/wiki/Integer
   * @return BigNumber& *this the integer.
   */
  public function Integer()
  {
    // truncate and return, the sign is kept.
    return $this->PerformPostOperations( 0 );
  }

  /**
   * Convert this number to the fractional part of the integer.
   * @see https://en.wikipedia.org/wiki/Fractional_part
   * @return BigNumber& *this the fractional part of the number.
   */
  public function Frac()
  {
    if ($this->_decimals == 0)
    {
      $this->_Copy( BigNumberConstants::Zero() );
    }
    else
    {
      $l = $this->_numbers->size();
      $this->_numbers->erase( $this->_decimals, $l - $this->_decimals );
      $this->_numbers->push_back(0);
    }

    // truncate and return, the sign is kept.
    return $this->PerformPostOperations( $this->_decimals );
  }

}
?>