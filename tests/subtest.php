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

require_once ( "src/bignumber.php" );

class TestAdd extends PHPUnit_Framework_TestCase
{
  public function testSubstractNegativeNumber ()
  {
    $num = \MyOddWeb\BigNumber("17")->Sub(\MyOddWeb\BigNumber("-26"))->ToInt();
    $this->assertSame(43, $num );
  }

  public function testSubstractEqualNumbersEqualsZero()
  {
    {
      $num = \MyOddWeb\BigNumber("12")->Sub(\MyOddWeb\BigNumber("12"))->ToInt();
      $this->assertSame(0, $num);
    }
    {
      $num = \MyOddWeb\BigNumber("123456789")->Sub(\MyOddWeb\BigNumber("123456789"))->ToInt();
      $this->assertSame(0, $num);
    }
  }

  public function testSecondNumberLessThansFirst()
  {
    {
      $num = \MyOddWeb\BigNumber("12")->Sub(\MyOddWeb\BigNumber("10"))->ToInt();
      $this->assertSame(2, $num);
    }
    {
      $num = \MyOddWeb\BigNumber("1234")->Sub(\MyOddWeb\BigNumber("234"))->ToInt();
      $this->assertSame(1000, $num);
    }
  }

  public function testSubtractPositiveNumberFromNegative()
  {
  {
    $num = \MyOddWeb\BigNumber(-10)->Sub(\MyOddWeb\BigNumber(27))->ToInt();
    $this->assertSame(-37, $num);
  }
  {
    $num = \MyOddWeb\BigNumber("-1234")->Sub(\MyOddWeb\BigNumber("456789"))->ToInt();
    $this->assertSame(-458023, $num);
  }
}

  public function testSubstractTwoNegativeNumbers()
  {
    {
      $num = \MyOddWeb\BigNumber(-10)->Sub(\MyOddWeb\BigNumber(-27))->ToInt();
      $this->assertSame(17, $num);
    }
    {
      $num = \MyOddWeb\BigNumber(-10)->Sub(\MyOddWeb\BigNumber(-7))->ToInt();
      $this->assertSame( -3, $num);
    }
    {
      $num = \MyOddWeb\BigNumber("-1234")->Sub(\MyOddWeb\BigNumber("-456789"))->ToInt();
      $this->assertSame(455555, $num);
    }
  }

  public function testSubstractPositiveNumberFromNegativeRandom()
  {
    $x = (rand() % 32767) * -1;
    $y = (rand() % 32767);

    $num = \MyOddWeb\BigNumber($x)->Sub(\MyOddWeb\BigNumber($y))->ToInt();
    $this->assertSame(($x - $y), $num);
  }

  public function testSubstractNegativeNumberFromPositive()
  {
    $x = 17;
    $y = -7;

    $num = \MyOddWeb\BigNumber($x)->Sub(\MyOddWeb\BigNumber($y))->ToInt();
    $this->assertSame(($x - $y), $num);
  }

  public function testSubstractNegativeNumberFromPositiveRandom()
  {
    $x = (rand() % 32767);
    $y = (rand() % 32767) * -1;

    $num = \MyOddWeb\BigNumber($x)->Sub(\MyOddWeb\BigNumber($y))->ToInt();
    $this->assertSame(($x - $y), $num);
  }

  public function testSubstractTwoNegativeNumber()
  {
    $x = -17;
    $y = -7;

    $num = \MyOddWeb\BigNumber($x)->Sub(\MyOddWeb\BigNumber($y))->ToInt();
    $this->assertSame(-10, $num);
  }

  public function testSubstractTwoNegativeNumberRandom()
  {
    $x = (rand() % 32767) * -1;
    $y = (rand() % 32767) * -1;

    $num = \MyOddWeb\BigNumber($x)->Sub(\MyOddWeb\BigNumber($y))->ToInt();
    $this->assertSame(($x - $y), $num);
  }

  public function testSubstractTwoPositiveNumberRandom()
  {
    $x = (rand() % 32767);
    $y = (rand() % 32767);

    $num = \MyOddWeb\BigNumber($x)->Sub(\MyOddWeb\BigNumber($y))->ToInt();
    $this->assertSame(($x - $y), $num);
  }

  public function testSubstractTwoNegativeNumberWithRhsGreater()
  {
    $x = -7;
    $y = -17;

    $num = \MyOddWeb\BigNumber($x)->Sub(\MyOddWeb\BigNumber($y))->ToInt();
    $this->assertSame(10, $num);
  }

  public function testSubstractTwoPositiveNumberWithRhsGreater()
  {
    $x = 7;
    $y = 17;

    $num = \MyOddWeb\BigNumber($x)->Sub(\MyOddWeb\BigNumber($y))->ToInt();
    $this->assertSame(-10, $num);
  }

  public function testSubstractTwoNumberEqualsZero()
  {
    {
      $x = \MyOddWeb\BigNumber(5);
      $y = \MyOddWeb\BigNumber(5);

      $x->Sub($y);  // 10
      $this->assertSame(0, $x->ToInt());
    }
    {
      $x = \MyOddWeb\BigNumber("18446744073709551615");
      $y = \MyOddWeb\BigNumber("18446744073709551615");

      $x->Sub($y);
      $this->assertSame(0, $x->ToInt());
    }
  }

  public function testSubtractTwoDecimalNumbers()
  {
    $dx = 20.1;
    $dy = 17.123;         // 2.977
    $x = \MyOddWeb\BigNumber($dx);
    $y = \MyOddWeb\BigNumber($dy);  // to prevent double loss

    $x->Sub($y);

    $dz = $x->ToDouble();
    $this->assertSame(2.977, $dz);       //  we cannot use dx-dy because it gives (2.9770000000000003)
  }

  public function testSubtractTwoNegativeDecimalNumbers()
  {
    $dx = -20.1;
    $dy = -17.123;         // 2.977
    $x = \MyOddWeb\BigNumber($dx);
    $y = \MyOddWeb\BigNumber($dy);  // to prevent double loss

    $x->Sub($y);

    $dz = $x->ToDouble();
    $this->assertSame(-2.977, $dz);       //  we cannot use dx-dy because it gives (2.9770000000000003)
  }

  public function testSubtractTwoDecimalNumbersWithLongDecimals()
  {
    $x = \MyOddWeb\BigNumber("93.908505508590963");
    $y = \MyOddWeb\BigNumber("83.635975218970302");

    $x->Sub($y);

    $a = "10.272530289620661"; // double zz = dx - dy; // = because of binary rounding...
    $b = $x->ToString();
    $this->assertSame($a, $b);
  }

  public function testSubtractTwoDecimalNumbersWithLongNegativeDecimals()
  {
    $x = \MyOddWeb\BigNumber("-83.635975218970302");
    $y = \MyOddWeb\BigNumber("-93.908505508590963");

    $x->Sub($y);

    $a = "10.272530289620661"; // double zz = dx - dy; // = because of binary rounding...
    $b = $x->ToString();

    $this->assertSame($a, $b);
  }

  public function testSubtractZeroFromLhs ()
  {
    $x = \MyOddWeb\BigNumber(12345);
    $y = \MyOddWeb\BigNumber(0);

    $x->Sub($y);

    $this->assertSame(12345, $x->ToInt() );
  }

  public function testSubtractZeroFromRhs()
  {
    $x = \MyOddWeb\BigNumber(0);
    $y = \MyOddWeb\BigNumber(12345);

    $x->Sub( $y );

    $this->assertSame(-12345, $x->ToInt());
  }

  public function testSubtractMoreDecimalsInTheItemBeenSubtracted ()
  {
    $x = \MyOddWeb\BigNumber( "2343.75" );
    $z = $x->Sub("244.140625")->ToString();
    $this->assertSame("2099.609375", $z);
  }

  public function testSubtractLessDecimalsInTheItemBeenSubtracted()
  {
    $x = \MyOddWeb\BigNumber( "2343.140625" );
    $z = $x->Sub("244.75")->ToString();
    $this->assertSame("2098.390625", $z);
  }

  public function testSubtractSmallNumberToBecomeInteger()
  {
    $x = \MyOddWeb\BigNumber( "123.02" );
    $y = \MyOddWeb\BigNumber( 0.02 );
    $z = $x->Sub($y)->ToString();
    $this->assertSame("123", $z);
  }

  public function testSubtractSmallNumberToBecomeLargeInteger()
  {
    $x = \MyOddWeb\BigNumber( "123456789123456789123456789123456789.000002" );
    $y = \MyOddWeb\BigNumber( "0.000002" );
    $z = $x->Sub($y)->ToString();
    $this->assertSame("123456789123456789123456789123456789", $z);
  }
}
?>