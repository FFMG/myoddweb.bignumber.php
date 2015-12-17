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

class BigNumberException extends \Exception{};

function BigNumber()
{
  $rc=new \ReflectionClass('\MyOddWeb\BigNumber');
  return $rc->newInstanceArgs( func_get_args() );
}

class BigNumber
{
  // zero.
  protected static $_number_zero = null;

  // one.
  protected static $_number_one = null;

  // two
  protected static $_number_two = null;

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
    if( null === static::$_number_zero )
    {
      static::$_number_zero = false;

      // zero.
      static::$_number_zero = new BigNumber( 0 );

      // one.
      static::$_number_one = new BigNumber( 1 );

      // two
      static::$_number_two = new BigNumber( 2 );
    }

    $num = func_num_args();
    switch ( $num )
    {
    case 1:
      $arg = func_get_arg(0);
      if( is_string($arg ))
      {
        $this->_ParseString($arg );
      }
      else if( is_numeric($arg ))
      {
        $this->_ParseNumber($arg );
      }
      break;

    default:
      // just make this a zero
      $this->_ParseNumber( 0 );
      break;
    }

    // the number is just zero.
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
   * @return BigNumber& the number we cleaned up.
   */
  protected function PerformPostOperations( $precision)
  {
    if ( $this->_decimals > $precision)
    {
      // trunc will call this function again.
      return $this->Trunc(precision);
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
    $c = _numbers.at( 0 + $this->_decimals );

    // is that number even?
    return (($c % 2) == 0);
  }

  /**
   * Transform the number into absolute number.
   * @return BigNumber& this non negative number.
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
   * @param const BigNumber& lhs the left hand side number
   * @param const BigNumber& rhs the right hand size number
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
   * @param const BigNumber& the number we are comparing to.
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
   * Calculate the remainder when 2 numbers are divided.
   * @param const BigNumber& denominator the denominator dividing this number
   * @param BigNumber the remainder of the division.
   */
  public function Mod( $denominator)
  {
    // validate the value.
    $denominator = static::FromValue($denominator);

    // quick shortcut for an often use function.
    if ( $denominator->Compare( static::$_number_two) == 0)
    {
      // use this function, it is a lot quicker.
      return $this->IsEven() ? static::$_number_zero : static::$_number_one;
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
   * @param const BigNumber& denominator the denominator dividing this number
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
   * @param const BigNumber& numerator the numerator been devided.
   * @param const BigNumber& denominator the denominator dividing the number.
   * @param BigNumber& quotient the quotient of the division
   * @param BigNumber& remainder the remainder.
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
   * Calculate the quotien and remainder of a division
   * @see https://en.wikipedia.org/wiki/Modulo_operation
   * @param const BigNumber& numerator the numerator been devided.
   * @param const BigNumber& denominator the denominator dividing the number.
   * @param BigNumber& quotient the quotient of the division
   * @param BigNumber& remainder the remainder.
   */
  static protected function AbsQuotientAndRemainder($numerator, $denominator, &$quotient, $remainder)
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
    $remainder = $numerator;
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

    // do a 'quick' remainder calculatations.
    //
    // 1- look for the 'max' denominator.
    //    we need the number to be positive.
    $max_denominator = $denominator;
    $max_denominator->_neg = false;
    $base_multiplier = static::$_number_one;

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
}
?>