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

class TestDiv extends PHPUnit_Framework_TestCase
{
  public function testDevideByZero()
  {
    $num = \MyOddWeb\BigNumber("123")->Div( 0 );
    $this->assertTrue( $num->IsNan() );
  }

  public function testZeroDevidedByAnyNumber ()
  {
    $x = (rand() % 32767) + 1;
    $num = \MyOddWeb\BigNumber(0)->Div($x);
    $this->assertTrue($num->IsZero());
  }

  public function testDevideWholePositiveNumbers ()
  {
    $num = \MyOddWeb\BigNumber( 10 )->Div( 5 )->ToInt();
    $this->assertSame(2, $num);
  }

  public function testDevideWholeNegativeNumbers()
  {
    $num = \MyOddWeb\BigNumber(-10)->Div(-5)->ToInt();
    $this->assertSame(2, $num);
  }

  public function testDevideWholeNegativeAndPositiveNumbers()
  {
    {
      $num = \MyOddWeb\BigNumber(-10)->Div(5)->ToInt();
      $this->assertSame(-2, $num);
    }
    {
      $num = \MyOddWeb\BigNumber(10)->Div(-5)->ToInt();
      $this->assertSame(-2, $num);
    }
  }

  public function testDevideRationalPositiveNumbers()
  {
    $num = \MyOddWeb\BigNumber(5)->Div(2)->ToDouble();
    $this->assertSame(2.5, $num);
  }

  public function testSmallNumberDividedByLargeNumberRecuringResult()
  {
    $x = \MyOddWeb\BigNumber( 1234 );
    $y = \MyOddWeb\BigNumber( 3456 );

    $numA = \MyOddWeb\BigNumber($x)->Div($y)->ToString();
    $a = "0.3570601851851851851851851851851851851851851851851851851851851851851851851851851851851851851851851851";
    $this->assertSame( $a, $numA);

    $numB = \MyOddWeb\BigNumber($x)->Div($y, 615 )->ToString();
    $b = "0.357060185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185185";
    $this->assertSame( $b, $numB );
  }

  public function testDivisionNonRecuring()
  {
    {
      $x = \MyOddWeb\BigNumber("0.125");
      $y = \MyOddWeb\BigNumber(2);

      $z = $x->Div($y)->ToString();
      $this->assertSame("0.0625", $z);
    }

    {
      $x = \MyOddWeb\BigNumber("1.25");
      $y = \MyOddWeb\BigNumber(2);

      $z = $x->Div($y)->ToString();
      $this->assertSame("0.625", $z);
    }

    {
      $x = \MyOddWeb\BigNumber( 0.625 );
      $y = $x->Div(2);
      $z = $y->ToString();
      $this->assertSame("0.3125", $z);
    }
  }

  public function testDivideExactSameWholeNumber()
  {
    $x = \MyOddWeb\BigNumber( 12345 );
    $y = \MyOddWeb\BigNumber( 12345 );
    $z = $x->Div($y)->ToInt();
    $this->assertSame(1, $z);
  }

  public function testDivideExactSameBigRealNumber()
  {
    $x = \MyOddWeb\BigNumber( "123456789123456789123456789123456789.123456789123456789123456789123456789123456789123456789123456789123456789");
    $y = \MyOddWeb\BigNumber( "123456789123456789123456789123456789.123456789123456789123456789123456789123456789123456789123456789123456789" );
    $z = $x->Div($y)->ToInt();
    $this->assertSame(1, $z);
  }

  public function testModuloWithDenominatorLargerThanNuumerator()
  {
    $x = \MyOddWeb\BigNumber( 5 );
    $y = \MyOddWeb\BigNumber( 20 );
    $z = $x->Mod( $y )->ToInt();
    $this->assertSame( 5, $z );
  }

  public function testModExactSameWholeNumber()
  {
    $x = \MyOddWeb\BigNumber( 12345 );
    $y = \MyOddWeb\BigNumber( 12345 );
    $z = $x->Mod($y)->ToInt();
    $this->assertSame(0, $z);
  }

  public function testModExactSameRealNumber()
  {
    $x = \MyOddWeb\BigNumber( 12345.678 );
    $y = \MyOddWeb\BigNumber( 12345.678 );
    $z = $x->Mod($y)->ToInt();
    $this->assertSame(0, $z);
  }

  public function testOneOverADecimalNumber()
  {
    $x = \MyOddWeb\BigNumber( 1 );
    $y = \MyOddWeb\BigNumber( 244.140625 );
    $z = $x->Div($y)->ToDouble();
    $this->assertSame(0.004096, $z);
  }

  public function testDecimalDivision()
  {
    {
      $x = \MyOddWeb\BigNumber( 93 );
      $z = $x->Div(1.5)->ToString();
      $this->assertSame("62", $z);
    }
    {
      $x = \MyOddWeb\BigNumber( 9.3 );
      $z = $x->Div(1.5)->ToString();
      $this->assertSame("6.2", $z);
    }
  }

  public function testExactDivision()
  {
    {
      $x = \MyOddWeb\BigNumber( 20 );
      $z = $x->Div(2)->ToString();
      $this->assertSame("10", $z);
    }
    {
      $x = \MyOddWeb\BigNumber( 100 );
      $z = $x->Div(100)->ToString();
      $this->assertSame("1", $z);
    }
  }

  public function testMultipleLevelDivisions()
  {
    {
      $x = \MyOddWeb\BigNumber( 1 );
      $z = $x->Div(244.140625)->ToString();  //  0.004096
      $this->assertSame("0.004096", $z);
    }
    {
      $x = \MyOddWeb\BigNumber( 10 );
      $z = $x->Div(244.140625)->ToString();  //  0.04096
      $this->assertSame("0.04096", $z);
    }
    {
      $x = \MyOddWeb\BigNumber( 100 );
      $z = $x->Div(244.140625)->ToString();  //  0.4096
      $this->assertSame("0.4096", $z);
    }
    {
      $x = \MyOddWeb\BigNumber( 1000 );
      $z = $x->Div(244.140625)->ToString();  //  4.096
      $this->assertSame("4.096", $z);
    }
    {
      $x = \MyOddWeb\BigNumber( 10000 );
      $z = $x->Div(244.140625)->ToString();  //  40.96
      $this->assertSame("40.96", $z);
    }
    {
      $x = \MyOddWeb\BigNumber( 100000 );
      $z = $x->Div(244.140625)->ToString();  //  409.6
      $this->assertSame("409.6", $z);
    }
    {
      $x = \MyOddWeb\BigNumber( 1000000 );
      $z = $x->Div(244.140625)->ToString();  //  4096
      $this->assertSame("4096", $z);
    }
  }

  public function testNegativeNumberDividedByPositiveNumber()
  {
    {
      $x = \MyOddWeb\BigNumber( -20 );
      $z = $x->Div(2)->ToString();
      $this->assertSame("-10", $z);
    }
    {
      $x = \MyOddWeb\BigNumber( -100 );
      $z = $x->Div(100)->ToString();
      $this->assertSame("-1", $z);
    }
    {
      $x = \MyOddWeb\BigNumber ( -1000000 );
      $z = $x->Div(244.140625)->ToString();  //  4096
      $this->assertSame("-4096", $z);
    }
  }

  public function testPositiveNumberDividedByNegativeNumber()
  {
    {
      $x = \MyOddWeb\BigNumber( 20 );
      $z = $x->Div(-2)->ToString();
      $this->assertSame("-10", $z);
    }
    {
      $x = \MyOddWeb\BigNumber( 100 );
      $z = $x->Div(-100)->ToString();
      $this->assertSame("-1", $z);
    }
    {
      $x = \MyOddWeb\BigNumber( 1000000 );
      $z = $x->Div(-244.140625)->ToString();  //  4096
      $this->assertSame("-4096", $z);
    }
  }
}
?>