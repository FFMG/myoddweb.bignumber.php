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

require_once 'bignumberconstants.php';

class BigNumberException extends \Exception{};

/**
 * Helper function to create a Bignumber without the need of calling new BigNumber( ... )
 * It also helps to allow chaining, (with the 'new' keyword you need to wrap the whole thing in brackets.
 * Just call $x = BigNumber( 1234 )->Div( 100 )->ToString();
 * @return \MyOddWeb\BigNumber the created BigNumber object.
 */
function BigNumber()
{
  $rc=new \ReflectionClass('\MyOddWeb\BigNumber');
  return $rc->newInstanceArgs( func_get_args() );
}

class BigNumber
{
/**
 * The version number vMajor.vMinor.vBuild
 * The derived version will try and follow that number.
 * (X*1000000 + Y*1000 + Z)
 *   #1   = major
 *   #2-4 = minor
 *   #5-7 = build
 */
  const BIGNUMBER_VERSION        = "0.1.303";
  const BIGNUMBER_VERSION_NUMBER = "0001303";

  const BIGNUMBER_BASE = 10;
  const BIGNUMBER_DEFAULT_PRECISION = 100;
  const BIGNUMBER_MAX_LN_ITERATIONS = 200;
  const BIGNUMBER_MAX_EXP_ITERATIONS = 100;
  const BIGNUMBER_MAX_ROOT_ITERATIONS = 100;

  /**
   * All the numbers in our number.
   * @var array $_numebrs
   */
  protected $_numbers = null;

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
      if( $arg instanceof BigNumber )
      {
        $this->_Copy( $arg );
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

  /**
   * Copy all the values from the source on
   * @param BigNumber $src the source we are copying from.
   * @return none;
   */
  protected function _Copy( $src )
  {
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
    $this->_neg = false;
    $this->_nan = false;
    $this->_zero = false;
    $this->_decimals = 0;
    $this->_numbers = []; // it has at least number zero
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

    // positive
    if( is_int($source))
    {
      // do the default
      $this->_Default();

      $neg = ($source < 0);
      $source = abs($source);
      while ($source > 0)
      {
        $s = $source % self::BIGNUMBER_BASE;
        $this->_numbers[] = $s;

        // the next number
        $source = (int)($source / self::BIGNUMBER_BASE);
      }

      // is negative.
      $this->_neg = $neg;

      // it is an integer...
      $this->PerformPostOperations(0);
    }
    else
    {
      // we can then part the string.
      $this->_ParseString( strval($source) );
    }
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

    // validate the numbers.
    foreach ( $numbers as &$number )
    {
      static::ValidateNumber( $number );
    }

    // the values are now safe.
    static::_FromSafeValues( $this, $numbers, $decimals, $neg);
  }

  protected static function _FromSafeValues( &$src, $numbers, $decimals, $neg )
  {
    // copy the values.
    $src->_neg = (boolean) $neg;
    $src->_decimals = (int)$decimals;

    $src->_numbers = $numbers;

    // clean it all up.
    $src->PerformPostOperations( $src->_decimals );

    // return the result.
    return $src;
  }

  /**
   * Make sure that a number is valid.
   * @param number $what the number we want to validate.
   * @throws BigNumberException if the number cannot be added to the array.
   */
  private function ValidateNumber( &$what )
  {
    // this must be a number
    if( !is_numeric($what))
    {
      throw new BigNumberException( "You must insert a number" );
    }

    $what = (int)$what;

    // the number _must_ be between 0 and 9
    if( $what < 0 || $what > 9 )
    {
      throw new BigNumberException( "You must insert a number between 0 and 9 only." );
    }
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
        $decimalPoint = count($this->_numbers);
        if ($decimalPoint == 0)
        {
          //  make sure it is '0.xyz' rather than '.xyz'
          $this->_numbers[] = 0;
          ++$decimalPoint;
        }
        continue;
      }

      static ::ValidateNumber( $char );
      array_unshift( $this->_numbers, $char );

      // either way, signs are no longer allowed.
      $allowSign = false;
    }
    // get the number of decimals.
    $this->_decimals = ($decimalPoint == -1) ? 0 : count($this->_numbers) - $decimalPoint;

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
      $it = $this->_numbers[0];
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
      array_splice( $this->_numbers, 0, 1 );

      // move back one decimal.
      --$this->_decimals;
    }

    // remember that the numbers are in reverse
    $l = count( $this->_numbers ) -1;
    for (;;)
    {
      // do we have a number?
      if ($l < 0 )
      {
        break;
      }

      // get the last number
      $it = $this->_numbers[ $l ];

      // if that number is not zero then we have no more leading zeros.
      if ( $it != 0)
      {
        //  we are done.
        break;
      }

      // remove that 'leading' zero.
      array_splice( $this->_numbers, $l, 1 );
      --$l;
    }

    //  are we zero size?
    $l = count($this->_numbers);
    if ($l == 0)
    {
      //  this is empty, so the number _must_ be zero
      $this->_neg = false;
      $this->_zero = true;
      $this->_decimals = 0;
      $this->_numbers[] = 0;
      ++$l;
    }

    while ($l < $this->_decimals+1)
    {
      // we have a decimals but no 'leading' zero ".123" instead of "0.123"
      // to avoid headaches later we add the zero.
      $this->_numbers[] = 0;
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
    $c = $this->_numbers[ 0 + $this->_decimals ];

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
    $l = count( $this->_numbers );

    // go around each number and re-create the integer.
    foreach ( array_reverse( $this->_numbers ) as $c )
    {
      $number = $number * self::BIGNUMBER_BASE + $c;

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
    return $this->ToBase( self::BIGNUMBER_BASE, $this->_decimals );
  }

  /**
   * Convert a big number to a string.
   * @see http://mathbits.com/MathBits/CompSci/Introduction/frombase10.htm
   * @param unsigned short the base we want to convert this number to.
   * @param size_t precision the max precision we want to reach.
   * @return std::string the converted number to a string.
   */
  public function ToBase( $base, $precision = self::BIGNUMBER_DEFAULT_PRECISION )
  {
    if ( $this->IsNan())
    {
      return "NaN";
    }

    if ($base > 62)
    {
      throw new BigNumberException("You cannot convert to a base greater than base 62");
    }
    if ($base <= 1)
    {
      throw new BigNumberException("You cannot convert to a base greater smaller than base 2");
    }

    // is it the correct base already?
    if (self::BIGNUMBER_BASE == $base )
    {
      return static::_ToString( array_reverse( $this->_numbers ), $this->_decimals, $this->IsNeg(), $precision );
    }

    // the base is not the same, so we now have to rebuild it.
    // in the 'correct' base.
    // @see http://mathbits.com/MathBits/CompSci/Introduction/frombase10.htm
    // @see http://www.mathpath.org/concepts/Num/frac.htm
    $numbersInteger = [];
    static::_ConvertIntegerToBase( $this, $numbersInteger, $base);

    $numbersFrac = [];
    static::_ConvertFractionToBase( $this, $numbersFrac, $base, $precision );

    // we now need to join the two.
    $numbersInteger = array_merge( $numbersFrac, $numbersInteger );

    // no idea how to re-build that number.
    return static::_ToString( array_reverse( $numbersInteger ),  count($numbersFrac), $this->IsNeg(), $precision );
  }

  /**
   * Convert a number to a given base, (only the integer part).
   * Convert the integer part of a number to the given base.
   * @see http://mathbits.com/MathBits/CompSci/Introduction/frombase10.htm
   * @see http://www.mathpath.org/concepts/Num/frac.htm
   * @param BigNumber $givenNumber
   * @param array[int] $numbers the container that will contain all the numbers, (array of unsigned char).
   * @param number $base the base we are converting to.
   */
  static protected function _ConvertIntegerToBase( &$givenNumber, &$numbers, $base)
  {
    $numbers = [];
    $resultInteger = BigNumber( $givenNumber )->Integer();
    if ($resultInteger->IsZero())
    {
      // the integer part must have at least one number, 'zero' itself.
      $numbers[] = 0;
      return;
    }

    $bigNumberBase = new BigNumber( $base );

    for (;;)
    {
      $quotient = new BigNumber();
      $remainder = new BigNumber();
      static::AbsQuotientAndRemainder( $resultInteger, $bigNumberBase, $quotient, $remainder);
      $numbers[]  = $remainder->ToInt();

      // are we done?
      if ( $quotient->IsZero() )
      {
        break;
      }
      $resultInteger = $quotient;
    }
  }

  /**
   * Convert a number to a given base, (only the fractional part).
   * Convert the fractional part of a number to the given base.
   * @see http://mathbits.com/MathBits/CompSci/Introduction/frombase10.htm
   * @see http://www.mathpath.org/concepts/Num/frac.htm
   * @param BigNumber $givenNumber
   * @param array[int] numbers the container that will contain all the numbers, (array of unsigned char).
   * @param number $base the base we are converting to.
   * @param number $precision the max presision we want to reach.
   */
  static protected function _ConvertFractionToBase
  (
      &$givenNumber,
      &$numbers,
      $base,
      $precision
  )
  {
    $numbers = [];
    $resultFrac = BigNumber( $givenNumber)->Frac();
    if ( $resultFrac->IsZero())
    {
      return;
    }

    $bigNumberBase = new BigNumber( $base );
    $actualPrecision = 0;

    for (;;)
    {
      //  have we reached a presision limit?
      if ( $actualPrecision >= $precision)
      {
        break;
      }

      // the oresision we are at.
      ++$actualPrecision;

      // we have to use our multiplier so we don't have rounding issues.
      // if we use double/float
      $resultFrac = static::AbsMul( $resultFrac, $bigNumberBase, 1000 );

      // the 'number' is the integer part.
      $remainder = BigNumber( $resultFrac )->Integer();
      $numbers[] = $remainder->ToInt();

      // as long as the fractional part is not zero we can continue.
      $resultFrac = BigNumber($resultFrac)->Frac();

      // if it is zero, then we are done
      if ( $resultFrac->IsZero())
      {
        break;
      }
    }

    // we now need to reverse the array as this is the way all our numbers are.
    $numbers = array_reverse( $numbers );
  }


  /**
   * Convert a NUMBERS number to an integer.
   * @param unsigned short the base we want to convert this number to.
   * @param size_t precision the max presision we want to reach.
   * @return std::string the converted number to a string.
   */
  static protected function _ToString( $numbers, $decimals, $isNeg, $precision )
  {
    $trimmedNumbers = $numbers;
    if ($decimals > $precision)
    {
      $l = count($numbers);
      $end = $decimals - $precision;
      array_splice( $trimmedNumbers, $l - $end );
      $decimals = $precision;
    }

    // the return number
    $number = "";

    // the total number of items.
    $l = count($trimmedNumbers);

    // go around each number and re-create the integer.
    foreach ( $trimmedNumbers as $c )
    {
      if ((int)$c <= 9)
      {
        $number .= strval((int)$c);
      }
      else if ((int)$c <= 36/*26+10*/)
      {
        $number .= chr( ord('A') + (int)$c - 10);
      }
      else if ((int)$c <= 62/*26+26+10*/)
      {
        $number .= chr( ord('a') + (int)$c - 36 );
      }
      if (--$l - $decimals == 0 && $l != 0 )  //  don't add it right at the end...
      {
        $number .= '.';
      }
    }
    return $isNeg ? ('-' . $number) : $number;
  }

  /**
   * Use the tostring magic function to convert this value to a string.
   * useful for direct echo and so on
   * @uses self::ToString();
   * @return string the string value
   */
  public function __toString()
  {
    return $this->ToString();
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
    $lhs = clone static::FromValue($lhs);
    $rhs = clone static::FromValue($rhs);

    $ll = count( $lhs->_numbers );
    $rl = count( $rhs->_numbers );

    // fast comapare 2 arrays
    if( $ll == $rl && $lhs->_decimals == $rhs->_decimals )
    {
      // go in reverse
      for( $i= $ll -1; $i >= 0; --$i )
      {
        $ucl = &$lhs->_numbers[ $i ];
        $ucr = &$rhs->_numbers[ $i ];

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

      // numbers are the same len and all numbers are the same.
      return 0;
    }

    // get the max number of decimals.
    $maxDecimals = $lhs->_decimals >= $rhs->_decimals ? $lhs->_decimals : $rhs->_decimals;
    $lhsDecimalsOffset = $maxDecimals - $lhs->_decimals;
    $rhsDecimalsOffset = $maxDecimals - $rhs->_decimals;

    // check the whole number, if one is greater than the other
    // then no need to compare in details.
    // the decimal does not matter, xxx.0000001 > yy.999
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
    for ($i = ($ll- $lhs->_decimals -1); $i >= 0; --$i)
    {
      // get the numbers past the multiplier.
      $ucl = &$lhs->_numbers[ $i+ $lhs->_decimals ];
      $ucr = &$rhs->_numbers[ $i+ $rhs->_decimals ];
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
      $ucl = ($i - $lhsDecimalsOffset < 0) ? 0 : $lhs->_numbers[ $i - $lhsDecimalsOffset ];
      $ucr = ($i - $rhsDecimalsOffset < 0) ? 0 : $rhs->_numbers[ $i - $rhsDecimalsOffset ];
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

    // they are the same, we should never reach this
    // if they are the same len then we should have done
    // a fast compare earlier.
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
   * Check if this number is smaller than the one given.
   * @see BigNumber::Compare( ... )
   * @param const BigNumber& rhs the number we are comparing to.
   * @return boolean if this number is smaller
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
   * Divide By Base, effectively remove a zero at the end.
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
    $l = count( $this->_numbers );
    while ($l < $this->_decimals + 1)
    {
      $this->_numbers[] = 0;
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

    // muliply by self::BIGNUMBER_BASE means that we are shifting the multipliers.
    while ( $this->_decimals > 0 && $multiplier > 0 )
    {
      --$this->_decimals;
      --$multiplier;
    }

    // if we have any multipliers left,
    // keep moving by adding zeros.
    for ($i = 0; $i < $multiplier; ++$i)
    {
      array_unshift( $this->_numbers, 0 );
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
    // make sure that the value are /MyOddWeb/BigNumber
    $max_denominator = static::FromValue($max_denominator);
    $base_multiplier = static::FromValue($base_multiplier);
    $remainder = static::FromValue($remainder);

    // are done with this?
    if ($remainder->IsZero())
    {
      return false;
    }

    // if the max denominator is greater than the remained
    // then we must devide by self::BIGNUMBER_BASE.
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
        // divide all by self::BIGNUMBER_BASE
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
    //  clone
    $numerator = clone static::FromValue($numerator);
    $denominator = clone static::FromValue($denominator);

    // are we trying to divide by zero?
    if ($denominator->IsZero())
    {
      // those are not value numbers.
      $remainder = new BigNumber(); $remainder->_nan = true;
      $quotient = new BigNumber(); $quotient->_nan = true;
      return;
    }

    // no-clone as we want to change the actual values.
    $quotient = static::FromValue($quotient);
    $remainder = static::FromValue($remainder);

    // reset the quotient to 0.
    $quotient = BigNumberConstants::Zero();

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
      $remainder = static::AbsSub( $remainder, $max_denominator);

      // and add the quotient.
      $quotient = static::AbsAdd( $quotient, $base_multiplier);
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
      $quotient->Add( BigNumberConstants::One() );

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
   * @return BigNumber *this number to allow chaining
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
   * @param size_t precision the precision we want to use.
   * @return BigNumber this number.
   */
  public function Mul( $rhs, $precision= self::BIGNUMBER_DEFAULT_PRECISION )
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
  public function Div( $rhs, $precision= self::BIGNUMBER_DEFAULT_PRECISION )
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
   * @return BigNumber this number to allow chaining
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
  protected static function AbsDiv( $lhs, $rhs, $precision = self::BIGNUMBER_DEFAULT_PRECISION )
  {
    $lhs = clone static::FromValue($lhs)->Round( BigNumberConstants::PrecisionPadding($precision) );
    $rhs = clone static::FromValue($rhs)->Round( BigNumberConstants::PrecisionPadding($precision) );

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
      // 0 / n = 0
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

    // the result in reverse order.
    $c = [];

    // the number we are working with.
    $number = clone $lhs;
    $number->_neg = false;

    // quotient/remainder we will use.
    $quotient = new BigNumber();
    $remainder = new BigNumber();

    // divide until we are done ... or we reached the precision limit.
    for (;;)
    {
      // get the quotient and remainder.
      static::QuotientAndRemainder($number, $rhs, $quotient, $remainder);

      // add the quotient to the current number.
      $c = array_merge( $quotient->_numbers, $c);

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
    return static::_FromSafeValues( new BigNumber(), $c, $decimals, false );
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
    $lhs = clone static::FromValue($lhs)->Round( BigNumberConstants::PrecisionPadding($precision));
    $rhs = clone static::FromValue($rhs)->Round( BigNumberConstants::PrecisionPadding($precision));

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
    // by maxDecimals * self::BIGNUMBER_BASE
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
    //           = 30 * self::BIGNUMBER_BASE
    //           = 300+75=375

    // the two sizes
    $ll = count( $lhs->_numbers );
    $rl = count( $rhs->_numbers );

    // the return number
    $c = new BigNumber();
    $shift = 4;           // max int = 2147483647 on a 32 bit process
                          // so the biggest number we can have is 46340 (46340*46340=2147395600)
                          // so using 1 and 0 only, the biggest number is 10000 (and shift=4xzeros)
                          // the biggest number is 9999*9999= 99980001
    $shiftszerros = array_fill( 0, $shift, 0 );

    $max_base = 10000;

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
          $s = $sum % self::BIGNUMBER_BASE;
          $numbers[] = $s;

          $sum = (int)((int)$sum / (int)self::BIGNUMBER_BASE);
        }
      }

      // add the carry over if we have one
      while ($carryOver > 0)
      {
        $s = $carryOver % self::BIGNUMBER_BASE;
        $numbers[] = $s;
        $carryOver = (int)((int)$carryOver / (int)self::BIGNUMBER_BASE);
      }

      // add a bunch of zeros _in front_ of our current number.
      $numbers = array_merge( $shifts, $numbers );

      $shifts = array_merge( $shifts, $shiftszerros );

      // then add the number to our current total.
      $c = static::AbsAdd($c, static::_FromSafeValues( new BigNumber(), $numbers, 0, false));
    }

    // this is the number with no multipliers.
    return $c->PerformPostOperations( $precision );
  }

  protected function _MakeNumberAtIndex( $index, $length)
  {
    $number = 0;
    $l = count($this->_numbers);
    for ( $i = ($length-1); $i >= 0; --$i)
    {
      $pos = ($i + $index);
      if ($pos >= $l)
      {
        continue;
      }
      $number = ($number * self::BIGNUMBER_BASE) + $this->_numbers[ $pos ];
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
      if ($sum >= self::BIGNUMBER_BASE)
      {
        $sum -= self::BIGNUMBER_BASE;
        $carryOver = 1;
      }
      $numbers[] = $sum;
    }

    if ( $carryOver > 0)
    {
      $numbers[] = 1;
    }

    // this is the new numbers
    return static::_FromSafeValues( new BigNumber(), $numbers, $maxDecimals, false );
  }

  /**
   * Subtract 2 absolute numbers together.
   * @param const BigNumber lhs the number been subtracted from
   * @param const BigNumber rhs the number been subtracted with.
   * @return BigNumber the diff of the two numbers.
   */
  protected static function AbsSub( $lhs, $rhs)
  {
    $lhs = clone static::FromValue($lhs);
    $rhs = clone static::FromValue($rhs);

    // is it negative?
    $neg = false;

    // compare the 2 numbers
    if (static::AbsCompare($lhs, $rhs) < 0 )
    {
      // swap the two values to get a positive result.
      $tmp = $lhs;
      $lhs = $rhs;
      $rhs = $tmp;

      // but we know it is negative
      $neg = true;

      // and continue the subtraction.
    }

    // if we want to subtract zero from the lhs, then the result is rhs
    if ( $rhs->IsZero() )
    {
      return static::_FromSafeValues( new BigNumber(), $lhs->_numbers, $lhs->_decimals, $neg );
    }

    // we know that lhs is greater than rhs.
    // because we swapped it earlier.
    $carryOver = 0;

    // get the total len, including the decimal/
    $ll = count( $lhs->_numbers );
    $rl = count( $rhs->_numbers );

    // get the maximum number of decimals.
    // so if we do 4.23 - 4.2 the max number of decimals is 2.
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

      // work our way backward sooo
      // 4.2 - 4.13 =
      // 0-3, (4.20 - 4.13) = -3 = +7
      // 2-1, (4.2  - 4.1)  =  1 =  0 // carry over
      // 4-4, (4    - 4)    =  0 =  0 // no carry over
      $l = ($i >= $lhsDecimalsOffset && $i < $ll + $lhsDecimalsOffset) ? $lhs->_numbers[ $i - $lhsDecimalsOffset ] : 0;
      $r = ($i >= $rhsDecimalsOffset && $i < $rl + $rhsDecimalsOffset) ? $rhs->_numbers[ $i - $rhsDecimalsOffset ] : 0;

      // the new number is the lhs - carry over - rhs
      // if the number is negative, we have another carry over.
      $sum = $l - $carryOver - $r;

      $carryOver = 0;
      if ($sum < 0)
      {
        $sum += self::BIGNUMBER_BASE;
        $carryOver = 1;
      }
      $numbers[] = $sum;
    }

    // this is the new numbers
    return static::_FromSafeValues( new BigNumber(), $numbers, $maxDecimals, $neg );
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
    if($actualPosition < 0 || $actualPosition >= count($this->_numbers ))
    {
      return (int)255;
    }

    // we all good!
    return $this->_numbers[ $actualPosition ];
  }

  /**
   * Calculate the power of 'base' raised to 'exp' or x^y, (base^exp)
   * @param BigNumber $base the base we want to raise.
   * @param BigNumber $exp the exponent we are raising the base to.
   * @param number $precision the precision we want to use.
   * @return BigNumber the base raised to the exp.
   */
  static protected function AbsPow( $base, $exp, $precision)
  {
    $base = clone static::FromValue($base);
    $exp = clone static::FromValue($exp);

    if( $exp->IsZero() )
    {
      return BigNumberConstants::One();
    }

    // +ve 1 exp = x
    if ( static::AbsCompare( $exp, BigNumberConstants::One() ) == 0 )
    {
      return $base;
    }

    // copy the base and exponent and make sure that they are positive.
    $copyBase = clone $base; $copyBase->Abs();
    $copyExp = clone $exp; $copyExp->Abs();

    // the current result.
    $result = BigNumberConstants::One();

    // if we have decimals, we need to do it the hard/long way...
    if ( $copyExp->_decimals > 0)
    {
      $copyBase->Ln(BigNumberConstants::PrecisionPadding($precision)); //  we need the correction, do we don't loose it too quick.
      $copyBase->Mul( $copyExp, BigNumberConstants::PrecisionPadding($precision));
      $result = $copyBase->Exp( BigNumberConstants::PrecisionPadding($precision) );
    }
    else
    {
      // until we reach zero.
      while (!$copyExp->IsZero())
      {
        // if it is odd...
        if ($copyExp->IsOdd())
        {
          $result = static::AbsMul( $result, $copyBase, BigNumberConstants::PrecisionPadding($precision));
        }

        // divide by 2 with no decimal places.
        $copyExp = static::AbsDiv( $copyExp, BigNumberConstants::Two(), 0);
        if ( $copyExp->IsZero() )
        {
          break;
        }

        // multiply the base by itself.
        $copyBase = static::AbsMul( $copyBase, $copyBase, BigNumberConstants::PrecisionPadding($precision));
      }
    }

    // clean up and return
    return $result->PerformPostOperations( $precision );
  }

  /**
   * Calculate the factorial of a non negative number
   * 5! = 5x4x3x2x1 = 120
   * @see https://en.wikipedia.org/wiki/Factorial
   * @param size_t precision the precision we want to use.
   * @return BigNumber the factorial of this number.
   */
  public function Factorial( $precision = self::BIGNUMBER_DEFAULT_PRECISION )
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
   * @return BigNumber the truncated number.
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
      array_splice( $this->_numbers, 0, $end );

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
  public function ToDegree($precision = self::BIGNUMBER_DEFAULT_PRECISION )
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
  public function ToRadian($precision = self::BIGNUMBER_DEFAULT_PRECISION )
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
      $l = count( $this->_numbers );
      array_splice( $this->_numbers, $this->_decimals, $l - $this->_decimals );
      $this->_numbers[] = 0;
    }

    // truncate and return, the sign is kept.
    return $this->PerformPostOperations( $this->_decimals );
  }

  /**
   * Raise this number to the given exponent.
   * @see http://brownmath.com/alge/expolaws.htm
   * @param BigNumber $exp the exponent.
   * @param number $precision the precision we want to use.
   * @return BigNumber this number.
   */
  public function Pow( $exp, $precision = self::BIGNUMBER_DEFAULT_PRECISION )
  {
    $exp = static::FromValue($exp);

    // just multiply
    $this->_Copy( static::AbsPow( $this, $exp, $precision ) );

    // if the exponent is negative
    // then we need to divide.
    if ($exp->IsNeg())
    {
      // x^(-y) = 1/^|y|
      $this->_Copy( (new BigNumber( BigNumberConstants::One() ))->Div( $this, $precision ));
    }

    // return this/cleaned up.
    return $this->PerformPostOperations( $precision );
  }

  /**
   * @see http://stackoverflow.com/questions/4657468/fast-fixed-point-pow-log-exp-and-sqrt
   * @see http://www.convict.lu/Jeunes/ultimate_stuff/exp_ln_2.htm
   * @see http://www.netlib.org/cephes/qlibdoc.html#qlog
   * @see https://en.wikipedia.org/wiki/Logarithm
   * Get the logarithm function of the current function.
   * @param number $precision the max number of decimals.
   * @return BigNumber this number base 10 log.
   */
  public function Ln( $precision = self::BIGNUMBER_DEFAULT_PRECISION )
  {
    // sanity checks
    if ( $this->IsNeg())
    {
      $this->_Copy( new BigNumber("NaN") );
      return $this->PerformPostOperations( $precision );
    }

    //  if this is 1 then log 1 is zero.
    if ( $this->Compare( BigNumberConstants::One() ) == 0 )
    {
      $this->_Copy( BigNumberConstants::Zero() );
      return $this->PerformPostOperations( $precision );
    }

    // @see https://www.quora.com/How-can-we-calculate-the-logarithms-by-hand-without-using-any-calculatorhttps://www.quora.com/How-can-we-calculate-the-logarithms-by-hand-without-using-any-calculator
    // @see https://www.mathsisfun.com/algebra/logarithms.html

    $counter1 = 0;
    $counter2 = 0;
    $counter8 = 0;

    while ( $this->Compare(0.8) < 0)
    {
      $this->Mul(1.8, BigNumberConstants::PrecisionPadding( $precision));
      ++$counter8;
    }
    while ( $this->Compare( BigNumberConstants::Two() ) > 0)
    {
      $this->Div(BigNumberConstants::Two(), BigNumberConstants::PrecisionPadding($precision));
      ++$counter2;
    }
    while ($this->Compare(1.1) > 0)
    {
      $this->Div(1.1, BigNumberConstants::PrecisionPadding($precision));
      ++$counter1;
    }

    //  we must make sure that *is
    $base = BigNumber($this)->Sub( BigNumberConstants::One() ); // Base of the numerator; exponent will be explicit
    $den = BigNumberConstants::One();                           // Denominator of the nth term
    $neg = false;                     // start positive.

    //                  (x-1)^2    (x-1)^3   (x-1)^4
    // ln(x) = (x-1) - --------- + ------- - ------- ...
    //                     2          3         4
    $result = new BigNumber( $base );            // Kick it off
    $baseRaised = new BigNumber( $base );
    for ( $i = 0; $i < self::BIGNUMBER_MAX_LN_ITERATIONS; ++$i )
    {
      // next denominator
      $den->Add(BigNumberConstants::One() );

      // swap operation
      $neg = !$neg;

      // the denominator+power is the same thing
      $baseRaised->Mul( $base, BigNumberConstants::PrecisionPadding($precision));

      // now divide it
      $currentBase = BigNumber( $baseRaised )->Div( $den, BigNumberConstants::PrecisionPadding($precision));

      // there is no need to go further, with this precision
      // and with this number of iterations we will keep adding/subtracting zeros.
      if ( $currentBase->IsZero())
      {
        break;
      }

      // and add it/subtract it from the result.
      if ($neg)
      {
        $result->Sub( $currentBase );
      }
      else
      {
        $result->Add( $currentBase );
      }
    }

    // log rules are... ln(ab) = ln(a) + ln(b)
    if ( $counter1 > 0)
    {
      // "0.0953101798043248600439521232807650922206053653086441991852398081630010142358842328390575029130364930727479418458517498888460436935129806386890150217023263755687346983551204157456607731117050481406611584967219092627683199972666804124629171163211396201386277872575289851216418802049468841988934550053918259553296705084248072320206243393647990631942365020716424972582488628309770740635849277971589257686851592941134955982468458204470563781108676951416362518738052421687452698243540081779470585025890580291528650263570516836272082869034439007178525831485094480503205465208833580782304569935437696233763597527612962802333"
      $ln11 = new BigNumber( [3,3,3,2,0,8,2,6,9,2,1,6,7,2,5,7,9,5,3,6,7,3,3,2,6,9,6,7,3,4,5,3,9,9,6,5,4,0,3,2,8,7,0,8,5,3,3,8,8,0,2,5,6,4,5,0,2,3,0,5,0,8,4,4,9,0,5,8,4,1,3,8,5,2,5,8,7,1,7,0,0,9,3,4,4,3,0,9,6,8,2,8,0,2,7,2,6,3,8,6,1,5,0,7,5,3,6,2,0,5,6,8,2,5,1,9,2,0,8,5,0,9,8,5,2,0,5,8,5,0,7,4,9,7,7,1,8,0,0,4,5,3,4,2,8,9,6,2,5,4,7,8,6,1,2,4,2,5,0,8,3,7,8,1,5,2,6,3,6,1,4,1,5,9,6,7,6,8,0,1,1,8,7,3,6,5,0,7,4,4,0,2,8,5,4,8,6,4,2,8,9,5,5,9,4,3,1,1,4,9,2,9,5,1,5,8,6,8,6,7,5,2,9,8,5,1,7,9,7,7,2,9,4,8,5,3,6,0,4,7,0,7,7,9,0,3,8,2,6,8,8,4,2,8,5,2,7,9,4,2,4,6,1,7,0,2,0,5,6,3,2,4,9,1,3,6,0,9,9,7,4,6,3,9,3,3,4,2,6,0,2,0,2,3,2,7,0,8,4,2,4,8,0,5,0,7,6,9,2,3,5,5,9,5,2,8,1,9,3,5,0,0,5,5,4,3,9,8,8,9,1,4,8,8,6,4,9,4,0,2,0,8,8,1,4,6,1,2,1,5,8,9,8,2,5,7,5,2,7,8,7,7,2,6,8,3,1,0,2,6,9,3,1,1,2,3,6,1,1,7,1,9,2,6,4,2,1,4,0,8,6,6,6,2,7,9,9,9,1,3,8,6,7,2,6,2,9,0,9,1,2,7,6,9,4,8,5,1,1,6,6,0,4,1,8,4,0,5,0,7,1,1,1,3,7,7,0,6,6,5,4,7,5,1,4,0,2,1,5,5,3,8,9,6,4,3,7,8,6,5,5,7,3,6,2,3,2,0,7,1,2,0,5,1,0,9,8,6,8,3,6,0,8,9,2,1,5,3,9,6,3,4,0,6,4,8,8,8,8,9,4,7,1,5,8,5,4,8,1,4,9,7,4,7,2,7,0,3,9,4,6,3,0,3,1,9,2,0,5,7,5,0,9,3,8,2,3,2,4,8,8,5,3,2,4,1,0,1,0,0,3,6,1,8,0,8,9,3,2,5,8,1,9,9,1,4,4,6,8,0,3,5,6,3,5,0,6,0,2,2,2,9,0,5,6,7,0,8,2,3,2,1,2,5,9,3,4,0,0,6,8,4,2,3,4,0,8,9,7,1,0,1,3,5,9,0,0 ], 616, false);
      $ln11->Mul( $counter1, BigNumberConstants::PrecisionPadding($precision));
      $result->Add( $ln11 );
    }

    // log rules are... ln(ab) = ln(a) + ln(b)
    if ( $counter2 > 0 )
    {
      // "0.693147180559945309417232121458176568075500134360255254120680009493393621969694715605863326996418687542001481020570685733685520235758130557032670751635075961930727570828371435190307038623891673471123350115364497955239120475172681574932065155524734139525882950453007095326366642654104239157814952043740430385500801944170641671518644712839968171784546957026271631064546150257207402481637773389638550695260668341137273873722928956493547025762652098859693201965058554764703306793654432547632744951250406069438147104689946506220167720424524529612687946546193165174681392672504103802546259656869144192871608293803172714368"
      $ln2 = new BigNumber([8,6,3,4,1,7,2,7,1,3,0,8,3,9,2,8,0,6,1,7,8,2,9,1,4,4,1,9,6,8,6,5,6,9,5,2,6,4,5,2,0,8,3,0,1,4,0,5,2,7,6,2,9,3,1,8,6,4,7,1,5,6,1,3,9,1,6,4,5,6,4,9,7,8,6,2,1,6,9,2,5,4,2,5,4,2,4,0,2,7,7,6,1,0,2,2,6,0,5,6,4,9,9,8,6,4,0,1,7,4,1,8,3,4,9,6,0,6,0,4,0,5,2,1,5,9,4,4,7,2,3,6,7,4,5,2,3,4,4,5,6,3,9,7,6,0,3,3,0,7,4,6,7,4,5,5,8,5,0,5,6,9,1,0,2,3,9,6,9,5,8,8,9,0,2,5,6,2,6,7,5,2,0,7,4,5,3,9,4,6,5,9,8,2,9,2,2,7,3,7,8,3,7,2,7,3,1,1,4,3,8,6,6,0,6,2,5,9,6,0,5,5,8,3,6,9,8,3,3,7,7,7,3,6,1,8,4,2,0,4,7,0,2,7,5,2,0,5,1,6,4,5,4,6,0,1,3,6,1,7,2,6,2,0,7,5,9,6,4,5,4,8,7,1,7,1,8,6,9,9,3,8,2,1,7,4,4,6,8,1,5,1,7,6,1,4,6,0,7,1,4,4,9,1,0,8,0,0,5,5,8,3,0,3,4,0,4,7,3,4,0,2,5,9,4,1,8,7,5,1,9,3,2,4,0,1,4,5,6,2,4,6,6,6,3,6,2,3,5,9,0,7,0,0,3,5,4,0,5,9,2,8,8,5,2,5,9,3,1,4,3,7,4,2,5,5,5,1,5,6,0,2,3,9,4,7,5,1,8,6,2,7,1,5,7,4,0,2,1,9,3,2,5,5,9,7,9,4,4,6,3,5,1,1,0,5,3,3,2,1,1,7,4,3,7,6,1,9,8,3,2,6,8,3,0,7,0,3,0,9,1,5,3,4,1,7,3,8,2,8,0,7,5,7,2,7,0,3,9,1,6,9,5,7,0,5,3,6,1,5,7,0,7,6,2,3,0,7,5,5,0,3,1,8,5,7,5,3,2,0,2,5,5,8,6,3,3,7,5,8,6,0,7,5,0,2,0,1,8,4,1,0,0,2,4,5,7,8,6,8,1,4,6,9,9,6,2,3,3,6,8,5,0,6,5,1,7,4,9,6,9,6,9,1,2,6,3,9,3,3,9,4,9,0,0,0,8,6,0,2,1,4,5,2,5,5,2,0,6,3,4,3,1,0,0,5,5,7,0,8,6,5,6,7,1,8,5,4,1,2,1,2,3,2,7,1,4,9,0,3,5,4,9,9,5,5,0,8,1,7,4,1,3,9,6,0], 615, false);
      $ln2->Mul( $counter2, BigNumberConstants::PrecisionPadding($precision));
      $result->Add($ln2);
    }

    // log rules are... ln(ab) = ln(a) + ln(b)
    if ($counter8 > 0 )
    {
      // "0.587786664902119008189731140618863769769379761376981181556740775800809598729560169117097631534284566775973755110200168585012003222536363442471987124070849093654145900869579488705254541486380394750214985439990943264901458147307801981343725602329350916457819213072437061657645370725998495814483186568232484236059984884946504043108616216273293809193522251042201711480828917893925532893803444719889512011504399314051421418444171441064659998892289089035003091141787128108024952008593307276614322356640449112819566260840792601819695518817384830430694637551056654910817069372465364862878039189497360001395678426943344493527"
      $ln18 = new BigNumber( [7,2,5,3,9,4,4,4,3,3,4,9,6,2,4,8,7,6,5,9,3,1,0,0,0,6,3,7,9,4,9,8,1,9,3,0,8,7,8,2,6,8,4,6,3,5,6,4,2,7,3,9,6,0,7,1,8,0,1,9,4,5,6,6,5,0,1,5,5,7,3,6,4,9,6,0,3,4,0,3,8,4,8,3,7,1,8,8,1,5,5,9,6,9,1,8,1,0,6,2,9,7,0,4,8,0,6,2,6,6,5,9,1,8,2,1,1,9,4,4,0,4,6,6,5,3,2,2,3,4,1,6,6,7,2,7,0,3,3,9,5,8,0,0,2,5,9,4,2,0,8,0,1,8,2,1,7,8,7,1,4,1,1,9,0,3,0,0,5,3,0,9,8,0,9,8,2,2,9,8,8,9,9,9,5,6,4,6,0,1,4,4,1,7,1,4,4,4,8,1,4,1,2,4,1,5,0,4,1,3,9,9,3,4,0,5,1,1,0,2,1,5,9,8,8,9,1,7,4,4,4,3,0,8,3,9,8,2,3,5,5,2,9,3,9,8,7,1,9,8,2,8,0,8,4,1,1,7,1,0,2,2,4,0,1,5,2,2,2,5,3,9,1,9,0,8,3,9,2,3,7,2,6,1,2,6,1,6,8,0,1,3,4,0,4,0,5,6,4,9,4,8,8,4,8,9,9,5,0,6,3,2,4,8,4,2,3,2,8,6,5,6,8,1,3,8,4,4,1,8,5,9,4,8,9,9,5,2,7,0,7,3,5,4,6,7,5,6,1,6,0,7,3,4,2,7,0,3,1,2,9,1,8,7,5,4,6,1,9,0,5,3,9,2,3,2,0,6,5,2,7,3,4,3,1,8,9,1,0,8,7,0,3,7,4,1,8,5,4,1,0,9,4,6,2,3,4,9,0,9,9,9,3,4,5,8,9,4,1,2,0,5,7,4,9,3,0,8,3,6,8,4,1,4,5,4,5,2,5,0,7,8,8,4,9,7,5,9,6,8,0,0,9,5,4,1,4,5,6,3,9,0,9,4,8,0,7,0,4,2,1,7,8,9,1,7,4,2,4,4,3,6,3,6,3,5,2,2,2,3,0,0,2,1,0,5,8,5,8,6,1,0,0,2,0,1,1,5,5,7,3,7,9,5,7,7,6,6,5,4,8,2,4,3,5,1,3,6,7,9,0,7,1,1,9,6,1,0,6,5,9,2,7,8,9,5,9,0,8,0,0,8,5,7,7,0,4,7,6,5,5,1,8,1,1,8,9,6,7,3,1,6,7,9,7,3,9,6,7,9,6,7,3,6,8,8,1,6,0,4,1,1,3,7,9,8,1,8,0,0,9,1,1,2,0,9,4,6,6,6,8,7,7,8,5,0], 615, false);
      $ln18->Mul( $counter8, BigNumberConstants::PrecisionPadding($precision));
      $result->Sub( $ln18 );
    }

    // done
    $this->_Copy( $result );

    // clean up and done.
    return $this->PerformPostOperations( $precision );
  }

  /**
   * Raise e to the power of this.
   * @param number $precision the precision we want to return this to (default = DEFAULT_PRECISION).
   * @return BigNumber e raised to the power of *this.
   */
  public function Exp( $precision = self::BIGNUMBER_DEFAULT_PRECISION)
  {
    // shortcut
    if ( $this->IsZero())
    {
      // reset this to 1
      $this->_Copy( BigNumberConstants::One() );

      //  done
      return $this->PerformPostOperations( $precision );
    }

    // get the integer part of the number.
    $integer = (new BigNumber($this))->Integer();

    // now get the decimal part of the number
    $fraction = (new BigNumber($this))->Frac();

    // reset 'this' to 1
    $this->_Copy( BigNumberConstants::One() );

    // the two sides of the equation
    // the whole number.
    if (!$integer->IsZero())
    {
      // get the value of e
      $e = BigNumberConstants::e();

      // truncate the precision so we do not do too many multiplications.
      // add a bit of room for more accurate precision.
      $e->Trunc(BigNumberConstants::PrecisionPadding($precision));

      //  then raise it.
      $this->_Copy( $e->Pow( $integer, BigNumberConstants::PrecisionPadding($precision)) );
    }

    if (!$fraction->IsZero())
    {
      //     x^1   x^2   x^3
      // 1 + --- + --- + --- ...
      //      1!    2!    3!
      $fact = BigNumberConstants::One();
      $base = new BigNumber( $fraction );
      $power = new BigNumber( $base );

      $result = BigNumberConstants::One();
      for ( $i = 1; $i < self::BIGNUMBER_MAX_EXP_ITERATIONS; ++$i )
      {
        //  calculate the number up to the precision we are after.
        $calulatedNumber = (new BigNumber( $power ))->Div( $fact, BigNumberConstants::PrecisionPadding($precision));
        if ( $calulatedNumber->IsZero() )
        {
          break;
        }

        // add it to our number.
        $result->Add( $calulatedNumber );

        // x * x * x ...
        $power->Mul( $base, BigNumberConstants::PrecisionPadding($precision));

        //  1 * 2 * 3 ...
        $fact->Mul( (int)($i+1), BigNumberConstants::PrecisionPadding($precision));
      }

      //  the decimal part of the number.
      $fraction = $result;

      // multiply the decimal number with the fraction.
      $this->Mul( $fraction, BigNumberConstants::PrecisionPadding($precision));
    }

    // clean up and return.
    return $this->PerformPostOperations( $precision );
  }

  /**
   * Calculate the square root of this number
   * @see http://mathworld.wolfram.com/SquareRoot.html
   * @see http://brownmath.com/alge/expolaws.htm
   * @param number precision the number of decimals.
   * @return BigNumber this number square root.
   */
  public function Sqrt( $precision = self::BIGNUMBER_DEFAULT_PRECISION )
  {
    // get the nroot=2
    // sqrt = x ^ (1 / 2)
    return $this->Root( BigNumberConstants::Two(), $precision);
  }

  /**
   * Calculate the nth root using the Newton alorithm
   * @see https://en.wikipedia.org/wiki/Nth_root_algorithm
   * @see https://en.wikipedia.org/wiki/Newton%27s_method
   * @param BigNumber $nthroot the nth root we want to calculate.
   * @param number $precision the number of decimals.
   * @return BigNumber this numbers nth root.
   */
  protected function RootNewton( $nthroot, $precision = self::BIGNUMBER_DEFAULT_PRECISION )
  {
    if ( $this->Compare( BigNumberConstants::One() ) == 0)
    {
      $this->_Copy( BigNumberConstants::One() );
      return $this;
    }

    // the padded precision so we do not, slowly, loose our final precision.
    $padded_precision = BigNumberConstants::PrecisionPadding($precision);

    // copy this number variable so it is easier to read.
    $x = clone $this;

    // values used a lot
    $r_less_one = (new BigNumber($nthroot))->Sub( BigNumberConstants::One() );
    $one_over_r = (new BigNumber( BigNumberConstants::One() ))->Div( $nthroot, $padded_precision);

    // calculate this over and over again.
    for ( $i = 0; $i < self::BIGNUMBER_MAX_ROOT_ITERATIONS; ++$i )
    {
      //  y = n / pow( x, r_less_one)
      $y1 = ( new BigNumber($x))->Pow($r_less_one, $padded_precision);
      $y  = ( new BigNumber($this))->Div($y1, $padded_precision);

      // x = one_over_r *(r_less_one * x +  y);
      $x_temp = (new BigNumber($one_over_r) )->Mul( (new BigNumber($r_less_one))->Mul($x, $padded_precision)->Add($y), $padded_precision);

      // if the calculation we just did, did not really change anything
      // it means that we can stop here, there is no point in trying
      // to refine this any further.
      if ($x_temp->Compare($x) == 0)
      {
        break;
      }

      // set *this to the the updated value.
      $x = clone $x_temp;
    }

    // set the value.
    $this->_Copy( $x->Round($precision) );

    // clean up.
    return $this->PerformPostOperations( $precision );
  }

  /**
   * Calculate the nth root of this number
   * @see http://www.mathwords.com/r/radical_rules.htm
   * @param BigNumber $nthroot the nth root we want to calculate.
   * @param number $precision the number of decimals.
   * @return BigNumber this numbers nth root.
   */
  public function Root( $nthroot, $precision = self::BIGNUMBER_DEFAULT_PRECISION )
  {
    // make sure that the number is a BigNumber
    $nthroot = static::FromValue($nthroot);

    // sanity checks, even nthroots cannot get negative nuber
    // Root( 4, -24 ) is not posible as nothing x * x * x  * x can give a negative result
    if ($this->IsNeg() && $nthroot->IsEven() )
    {
      // sqrt(-x) == NaN
      $this->_Copy( new BigNumber("NaN") );

      // all done
      return $this->PerformPostOperations( $precision );
    }

    // the nth root cannot be zero.
    if ( $nthroot->IsZero() )
    {
      // sqrt(-x) == NaN
      $this->_Copy( new BigNumber("NaN") );

      // all done
      return $this->PerformPostOperations( $precision );
    }

    // if the number is zero than this is unchanged.
    // because for x*x*x = 0 then x = 0
    if ( $this->IsZero() )
    {
      // sqrt(0) == 0 and we are already zero...
      return $this->PerformPostOperations( $precision );
    }

    // if the number is one, then this number is one.
    // it has to be as only 1*1 = 1 is the only possibility is.
    if ( $this->Compare( BigNumberConstants::One() ) == 0)
    {
      $this->_Copy( BigNumberConstants::One() );
    }
    else
    {
      // try and use some of the shortcuts...
      if ( $this->IsInteger())
      {
        return $this->RootNewton( $nthroot, $precision);
      }

      // try and use the power of...
      // nthroot = x^( 1/nthroot)
      $number_one_over = BigNumberConstants::One()->Div( $nthroot, BigNumberConstants::PrecisionPadding($precision) );

      // calculate it, use the correction to make sure we are well past
      // the actual value we want to set is as.
      // the rounding will then take care of the rest.
      $this->_Copy( $this->Pow( $number_one_over, BigNumberConstants::PrecisionPadding($precision))->Round( $precision ) );
    }

    // return this/cleaned up.
    // we already truncated it.
    return $this->PerformPostOperations( $precision );
  }
}
?>