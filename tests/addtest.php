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

require ( "src/bignumber.php" );

class TestAdd extends PHPUnit_Framework_TestCase
{
  public function testAddTwoPositiveNumbers()
  {
    $num = \MyOddWeb\BigNumber("17")->Add(\MyOddWeb\BigNumber("26"))->ToInt();
    $this->assertSame(43, $num );
  }

  public function testAddTwoPositiveNumbersRandom()
  {
    $x = (rand() % 32767);
    $y = (rand() % 32767);

    $num = \MyOddWeb\BigNumber( $x )->Add(\MyOddWeb\BigNumber( $y ))->ToInt();
    $this->assertSame(($x+$y), $num);
  }

  public function testAddPositiveNumberToNegativePositiveResult()
  {
    $num = \MyOddWeb\BigNumber("-17")->Add(\MyOddWeb\BigNumber("26"))->ToInt();
    $this->assertSame(9, $num);
  }

  public function testAddPositiveNumberToNegativeNegativeResult()
  {
    $num = \MyOddWeb\BigNumber("-47")->Add(\MyOddWeb\BigNumber("26"))->ToInt();
    $this->assertSame(-21, $num);
  }

  public function testAddPositiveNumberToNegativeRandom()
  {
    $x = (rand() % 32767) * -1 ;
    $y = (rand() % 32767);

    $num = \MyOddWeb\BigNumber($x)->Add(\MyOddWeb\BigNumber($y))->ToInt();
    $this->assertSame(($x + $y), $num);
  }

  public function testAddTwoNegativeNumbers()
  {
    $num = \MyOddWeb\BigNumber("-17")->Add(\MyOddWeb\BigNumber("-26"))->ToInt();
    $this->assertSame( -43, $num);
  }

  public function testAddTwoNegativeNumbersRandom()
  {
    $x = (rand() % 32767) * -1;
    $y = (rand() % 32767) * -1;

    $num = \MyOddWeb\BigNumber( $x )->Add(\MyOddWeb\BigNumber( $y ))->ToInt();
    $this->assertSame(($x + $y), $num);
  }

  public function testAddRhsGreaterThanLhs()
  {
    $num = \MyOddWeb\BigNumber("26")->Add(\MyOddWeb\BigNumber("17"))->ToInt();
    $this->assertSame(43, $num);
  }

  public function testAddTwoZeros()
  {
    $num = \MyOddWeb\BigNumber("0")->Add(\MyOddWeb\BigNumber("0"))->ToInt();
    $this->assertSame(0, $num);
  }

  public function testAddNumbersWithPlusSign()
  {
    $num = \MyOddWeb\BigNumber("+23")->Add(\MyOddWeb\BigNumber("7"))->ToInt();
    $this->assertSame(30, $num);
  }

  public function testAddNegativeNumberToPositivePositiveResult()
  {
    $num = \MyOddWeb\BigNumber("17")->Add(\MyOddWeb\BigNumber("-9"))->ToInt();
    $this->assertSame(8, $num);
  }

  public function testAddNegativeToPositveNegativeResult()
  {
    $num = \MyOddWeb\BigNumber("17")->Add(\MyOddWeb\BigNumber("-26"))->ToInt();
    $this->assertSame(-9, $num);
  }

  public function testAddNegativeNumberToPositiveRandom()
  {
    $x = (rand() % 32767);
    $y = (rand() % 32767) * -1;

    $num = \MyOddWeb\BigNumber($x)->Add(\MyOddWeb\BigNumber($y))->ToInt();
    $this->assertSame(($x + $y), $num);
  }

  public function testAddMaxLongLongNumbers()
  {
    $x = \MyOddWeb\BigNumber("18446744073709551615");
    $y = \MyOddWeb\BigNumber("18446744073709551615");

    $x->Add( $y );
    $this->assertSame( "36893488147419103230", $x->ToString() );
  }

  public function testAddLongNumberMaxLongLongNumber()
  {
    $x = \MyOddWeb\BigNumber("18446744073709551615");
    $y = \MyOddWeb\BigNumber("184467440737");

    $x->Add( $y);
    $this->assertSame("18446744258176992352", $x->ToString());
  }

  public function testAddMaxLongLongNegativeNumbers()
  {
    $x = \MyOddWeb\BigNumber("-18446744073709551615");
    $y = \MyOddWeb\BigNumber("-18446744073709551615");

    $x->Add($y);
    $this->assertSame("-36893488147419103230", $x->ToString());
  }

  public function testAddMaxLongLongNegativeAndPositiveNumbers()
  {
    $x = \MyOddWeb\BigNumber("-18446744073709551615");
    $y = \MyOddWeb\BigNumber("18446744073709551615");

    $x->Add($y);
    $this->assertSame("0", $x->ToString());
  }

  public function testAddTwoNumbersWithCarryOverExactlyTen()
  {
    $x = \MyOddWeb\BigNumber( 5 );
    $y = \MyOddWeb\BigNumber( 5 );

    $x->Add($y);  // 10
    $this->assertSame( 10, $x->ToInt() );
  }

  public function testAddTwoDecimalNumbers()
  {
    $dx = 20.1;
    $dy = 17.123;         // 37.223
    $x = \MyOddWeb\BigNumber($dx);
    $y = \MyOddWeb\BigNumber($dy);  // to prevent double loss

    $x->Add( $y );

    $this->assertSame(($dx + $dy), $x->ToDouble());
  }

  public function testAddTwoDecimalNumbersWithLongDecimals()
  {
    $dx = 83.635975218970302;
    $dy = 93.908505508590963;
    $x = \MyOddWeb\BigNumber($dx);
    $y = \MyOddWeb\BigNumber($dy);

    $x->Add($y);

    $a = 177.54448072756131; // double zz = dx + dy; // = 177.54448072756128 because of binary rounding...
    $b = $x->ToDouble();
    $this->assertSame($a, $b);
  }

  public function testAddTwoDecimalNumbersWithLongNegativeDecimals()
  {
    $dx = -83.635975218970302;
    $dy = -93.908505508590963;
    $x = \MyOddWeb\BigNumber($dx);
    $y = \MyOddWeb\BigNumber($dy);

    $x->Add($y);

    $a = -177.54448072756131; // double zz = dx + dy; // = -177.54448072756128 because of binary rounding...
    $b = $x->ToDouble();
    $this->assertSame($a, $b);
  }

  public function testAddAnIntegerToADecimalSmallerThanOne()
  {
    $x = \MyOddWeb\BigNumber( 42 );
    $y = \MyOddWeb\BigNumber( 0.02 );
    $z = $y->Add($x)->ToString();
    $this->assertSame("42.02", $z );
  }

  public function testAddLargeIntegerToADecimalSmallerThanOne()
  {
    $x = \MyOddWeb\BigNumber( "123456789123456789123456789" );
    $y = \MyOddWeb\BigNumber( "0.0000000002" );
    $z = $y->Add($x)->ToString();
    $this->assertSame("123456789123456789123456789.0000000002", $z);
  }
}
?>