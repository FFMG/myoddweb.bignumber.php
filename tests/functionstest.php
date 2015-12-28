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

require ( dirname(__FILE__) . "\../src/bignumber.php" );

class TestFunctions extends PHPUnit_Framework_TestCase
{
  public function testNegativeNumberAbs()
  {
    $num = (new \MyOddWeb\BigNumber("-12"))->Abs()->ToInt();
    $this->assertSame(12, $num);
  }

  public function testPositiveNumber()
  {
    $num = \MyOddWeb\BigNumber("12")->Abs()->ToInt();
    $this->assertSame(12, $num);
  }

  public function testConvertToIntNegativeNumber()
  {
    $num = \MyOddWeb\BigNumber("-12")->ToInt();
    $this->assertSame( -12, $num);
  }

  public function testConvertToIntPositiveNumber()
  {
    $num = \MyOddWeb\BigNumber("12")->ToInt();
    $this->assertSame(12, $num);
  }

  public function testConvertToIntPositiveNumberWithPlusSign()
  {
    $num = \MyOddWeb\BigNumber("+12")->ToInt();
    $this->assertSame( 12, $num);
  }

  public function testSpacesAreAllowedPositiveNumber()
  {
    $num = \MyOddWeb\BigNumber("   + 1 2    ")->ToInt();
    $this->assertSame(12, $num);
  }

  public function testSpacesAreAllowedNegativeUpdate()
  {
    $num = \MyOddWeb\BigNumber("   - 1 2    ")->ToInt();
    $this->assertSame(-12, $num);
  }

  public function testCreateFromInt()
  {
    $num = \MyOddWeb\BigNumber(123456789)->ToInt();
    $this->assertSame(123456789, $num);
  }

  public function testCreateFromChar()
  {
    $num = \MyOddWeb\BigNumber("123456789")->ToInt();
    $this->assertSame( 123456789, $num);
  }

  public function testCompareSameNumbers()
  {
    $lhs = new \MyOddWeb\BigNumber ("123");
    $rhs = new \MyOddWeb\BigNumber ("123");
    $geq = $lhs->Compare( $rhs);
    $this->assertSame( 0, $geq );
  }

  public function testLhsGreaterThanRhsButClose()
  {
    $lhs = new \MyOddWeb\BigNumber("124");
    $rhs = new \MyOddWeb\BigNumber("123");
    $geq = $lhs->Compare($rhs);
    $this->assertSame(1, $geq);
  }

  public function testLhsSmallerThanRhsButClose()
  {
    $lhs = new \MyOddWeb\BigNumber ("123");
    $rhs = new \MyOddWeb\BigNumber ("124");
    $geq = $lhs->Compare($rhs);
    $this->assertSame(-1, $geq);
  }

  public function testLhsGreaterThanRhsNotClose()
  {
    $lhs = new \MyOddWeb\BigNumber  ("924");
    $rhs = new \MyOddWeb\BigNumber  ("123");
    $geq = $lhs->Compare($rhs);
    $this->assertSame(1, $geq);
  }

  public function testLhsGreaterThanRhsByLen()
  {
    $lhs = new \MyOddWeb\BigNumber  ("1234");
    $rhs = new \MyOddWeb\BigNumber  ("456");
    $geq = $lhs->Compare($rhs);
    $this->assertSame(1, $geq);
  }

  public function testBothItemsAreZeroLength()
  {
    $lhs = new \MyOddWeb\BigNumber  ;
    $rhs = new \MyOddWeb\BigNumber  ;
    $geq = $lhs->Compare($rhs);
    $this->assertSame(0, $geq);
  }

  public function testCompareTwoDecimalNumbersSameInteger()
  {
    {
      $dx = 20.123;
      $dy = 20.1;

      $lhs = new \MyOddWeb\BigNumber  ($dx);
      $rhs = new \MyOddWeb\BigNumber  ($dy);

      $geq = $lhs->Compare($rhs);
      $this->assertSame(1, $geq);  //  lhs is greater
    }

    {
      $dx = 20.1;
      $dy = 20.123;

      $lhs = new \MyOddWeb\BigNumber  ($dx);
      $rhs = new \MyOddWeb\BigNumber  ($dy);

      $geq = $lhs->Compare($rhs);
      $this->assertSame(-1, $geq);  //  rhs is greater
    }
  }

  public function testCompareTwoDecimalNumbers()
  {
    {
      $dx = 20.1;
      $dy = 17.123;

      $lhs = new \MyOddWeb\BigNumber  ($dx);
      $rhs = new \MyOddWeb\BigNumber  ($dy);

      $geq = $lhs->Compare($rhs);
      $this->assertSame(1, $geq);  //  lhs is greater
    }

    {
      $dx = 17.123;
      $dy = 20.1;

      $lhs = new \MyOddWeb\BigNumber  ($dx);
      $rhs = new \MyOddWeb\BigNumber  ($dy);

      $geq = $lhs->Compare($rhs);
      $this->assertSame(-1, $geq);  //  rhs is greater
    }
  }

  public function testNumberIsCleanedUp()
  {
    $src = new \MyOddWeb\BigNumber ( "00000123" );
    $this->assertSame(123, $src->ToInt() );
    $this->assertSame("123", $src->ToString());
  }

  public function testNumberIsCleanedUpNegative()
  {
    $src = new \MyOddWeb\BigNumber ("-00000123");
    $this->assertSame(-123, $src->ToInt());
    $this->assertSame("-123", $src->ToString());
  }

  public function testParseDoubleGivenAsAString()
  {
    $src = new \MyOddWeb\BigNumber ("-1234.5678");
    $this->assertSame(-1234, $src->ToInt());
    $this->assertSame("-1234.5678", $src->ToString());
  }

  public function testParseDoubleGivenAsAStringButTheNumberIsActuallyAnInt()
  {
    {
      $src = new \MyOddWeb\BigNumber ("-1234");       //  number is an int
      $this->assertSame("-1234", $src->ToString());
    }
    {
      $src = new \MyOddWeb\BigNumber ("-1234.");      //  just one dot, but it is an int.
      $this->assertSame("-1234", $src->ToString());
    }
    {
      $src = new \MyOddWeb\BigNumber ("-1234.0000");  //  all the zeros, it is still an int.
      $this->assertSame("-1234", $src->ToString());
    }
  }

  public function testModulusZero()
  {
    $src = new \MyOddWeb\BigNumber ( 20 );
    $mod = $src->Mod(5);
    $this->assertSame(20, $src->ToInt());
    $this->assertSame(0, $mod->ToInt());
  }

  public function testModulusDecimalNumber()
  {
    $src = new \MyOddWeb\BigNumber (1000);
    $mod = $src->Mod(244.14025);
    $this->assertSame(23.439, $mod->ToDouble() );
  }

  public function testModulusZeroNegativeDivisor()
  {
    $src = new \MyOddWeb\BigNumber (20);
    $mod = $src->Mod(-5);
    $this->assertSame(20, $src->ToInt());
    $this->assertSame(0, $mod->ToInt());
  }

  public function testModulusZeroNegativeDivident()
  {
    $src = new \MyOddWeb\BigNumber (-20);
    $mod = $src->Mod(5);
    $this->assertSame(-20, $src->ToInt());
    $this->assertSame(0, $mod->ToInt());
  }

  public function testModulusZeroNegativeDividentAndDivisor()
  {
    $src = new \MyOddWeb\BigNumber (-20);
    $mod = $src->Mod(-5);
    $this->assertSame(-20, $src->ToInt());
    $this->assertSame(0, $mod->ToInt());
  }

  public function testModulusDividendSmallerToDivisor()
  {
    $src = new \MyOddWeb\BigNumber (5);
    $mod = $src->Mod(20);
    $this->assertSame(5, $mod->ToInt());
  }

  public function testModulusOfLongLongNumberShortDivisor()
  {
    $src = new \MyOddWeb\BigNumber ("18446744073709551619");
    $mod = $src->Mod("5");
    $this->assertSame(4, $mod->ToInt());
  }

  public function testModulusOfLongLongNumberLongDivisor()
  {
    $src = new \MyOddWeb\BigNumber ("18446744073709551619");
    $mod = $src->Mod("1844674407370955161");
    $this->assertSame(9, $mod->ToInt());
  }

  public function testModulusOfLongLongNumberLongerDivisor()
  {
    $src = new \MyOddWeb\BigNumber ("18446744073709551619");
    $mod = $src->Mod("184467440737095516198");
    $this->assertSame("18446744073709551619", $mod->ToString() );
  }

  public function testModulusOfLongLongNumberWithZerosLongDivisor()
  {
    $src = new \MyOddWeb\BigNumber ("10000000000000000000");
    $mod = $src->Mod("10000000000000000000");
    $this->assertSame(0, $mod->ToInt());
  }

  public function testModulusOfLongLongNumberWithZeros()
  {
    $src = new \MyOddWeb\BigNumber ("10000000000000000000");
    $mod = $src->Mod("5");
    $this->assertSame(0, $mod->ToInt());
  }

  public function testModulusWithZeroDivisor()
  {
    $src = new \MyOddWeb\BigNumber ("10000000000000000000");
    $mod = $src->Mod("0");
    $this->assertTrue($mod->IsNan());
  }

  public function testDivDenominatorIsZero()
  {
    $src = new \MyOddWeb\BigNumber (38);
    $div = $src->Quotient(0);
    $this->assertTrue($div->IsNan());
  }

  public function testDivDividendSmallerToDivisor()
  {
    $src = new \MyOddWeb\BigNumber (38);
    $div = $src->Quotient(5);
    $this->assertSame(7, $div->ToInt());  // 38/5 = 7, remainder 3
  }

  public function testNotANumberToInt()
  {
    $src = new \MyOddWeb\BigNumber (38);
    $nan = $src->Quotient(0);
    $this->assertTrue( $nan->IsNan());
    $this->assertSame(0, $nan->ToInt());
  }

  public function testNotANumberToString()
  {
    $src = new \MyOddWeb\BigNumber (38);
    $nan = $src->Quotient(0);
    $this->assertTrue($nan->IsNan());
    $this->assertSame("NaN", $nan->ToString());
  }

  public function testParseADouble()
  {
    $src = new \MyOddWeb\BigNumber (32.5);
    $this->assertSame( 32, $src->ToInt());
  }

  public function testDoubleNumberToIntReturnsProperly()
  {
    $src = new \MyOddWeb\BigNumber (32.123456);
    $this->assertSame(32, $src->ToInt());
  }

  public function testDoubleNegativeNumberToIntReturnsProperly()
  {
    $src = new \MyOddWeb\BigNumber (-32.123456);
    $this->assertSame(-32, $src->ToInt());
  }

  public function testToDoublePositiveNumber()
  {
    $src = new \MyOddWeb\BigNumber (32.123456);
    $this->assertSame(32.123456, $src->ToDouble());
  }

  public function testToDoubleNegativeNumber()
  {
    $src = new \MyOddWeb\BigNumber ( -32.123456);
    $this->assertSame( -32.123456, $src->ToDouble());
  }

  public function testZeroNumbersWithDecimalsAreNotTimmed()
  {
    {
      $y = new \MyOddWeb\BigNumber (0.012345);
      $sy = $y->ToString();
      $this->assertSame("0.012345", $sy);
    }
    {
      $y = new \MyOddWeb\BigNumber (0.12345);
      $sy = $y->ToString();
      $this->assertSame("0.12345", $sy);
    }
    {
      $y = new \MyOddWeb\BigNumber (0000.12345);
      $sy = $y->ToString();
      $this->assertSame("0.12345", $sy);
    }
  }

  public function testFractorialOfZero()
  {
    $c = new \MyOddWeb\BigNumber(0);
    $x = $c->Factorial()->ToInt();
    $this->assertSame(1, $x);
  }

  public function testFractorialOfNegativeNumber()
  {
    $c = new \MyOddWeb\BigNumber(-20 );
    $c->Factorial();
    $this->assertTrue( $c->IsNan() );
  }

  public function testSmallFractorial()
  {
    $c = new \MyOddWeb\BigNumber(5);
    $x = $c->Factorial()->ToInt();
    $this->assertSame( 120, $x );
  }

  public function testBigFractorial()
  {
    $c = new \MyOddWeb\BigNumber(20);
    $x = $c->Factorial()->ToString();
    $this->assertSame("2432902008176640000", $x);
  }

  public function testTruncatePositiveInteger()
  {
    $c = new \MyOddWeb\BigNumber(20);
    $d = $c->Trunc()->ToDouble();  //  use a double so we don't truncate it.
    $this->assertSame( 20.0 , $d );
  }

  public function testTruncateNegativeInteger()
  {
    $c = new \MyOddWeb\BigNumber(20);
    $d = $c->Trunc()->ToDouble();  //  use a double so we don't truncate it.
    $this->assertSame(20.0, $d);
  }

  public function testTruncatePositiveRealNumber()
  {
    {
      $c = new \MyOddWeb\BigNumber(2.3);
      $d = $c->Trunc()->ToDouble();  //  use a double so we don't truncate it.
      $this->assertSame(2.0, $d);
    }

    {
      $c = new \MyOddWeb\BigNumber(7.999);
      $d = $c->Trunc()->ToDouble();  //  use a double so we don't truncate it.
      $this->assertSame(7.0, $d);
    }

    {
      $c = new \MyOddWeb\BigNumber(12.5);
      $d = $c->Trunc()->ToDouble();  //  use a double so we don't truncate it.
      $this->assertSame(12.0, $d);
    }
  }

  public function testTruncateNegativeRealNumber()
  {
    {
      $c = new \MyOddWeb\BigNumber(-2.3);
      $d = $c->Trunc()->ToDouble();  //  use a double so we don't truncate it.
      $this->assertSame(-2.0, $d);
    }

    {
      $c = new \MyOddWeb\BigNumber(-7.999);
      $d = $c->Trunc()->ToDouble();  //  use a double so we don't truncate it.
      $this->assertSame(-7.0, $d);
    }

    {
      $c = new \MyOddWeb\BigNumber(-12.5);
      $d = $c->Trunc()->ToDouble();  //  use a double so we don't truncate it.
      $this->assertSame(-12.0, $d);
    }
  }

  public function testTruncateZero()
  {
    $c = new \MyOddWeb\BigNumber( 0.0 );
    $d = $c->Trunc()->ToDouble();  //  use a double so we don't truncate it.
    $this->assertSame( 0.0, $d);
  }

  public function testCeilZero()
  {
    $c = new \MyOddWeb\BigNumber(0.0);
    $d = $c->Ceil()->ToDouble();  //  use a double so we don't truncate it.
    $this->assertSame(0.0, $d);
  }

  public function testCeilPositiveInteger()
  {
    $c = new \MyOddWeb\BigNumber(12.0);
    $d = $c->Ceil()->ToDouble();  //  use a double so we don't truncate it.
    $this->assertSame(12.0, $d);
  }

  public function testCeilNegativeInteger()
  {
    $c = new \MyOddWeb\BigNumber( -12.0);
    $d = $c->Ceil()->ToDouble();  //  use a double so we don't truncate it.
    $this->assertSame( -12.0, $d);
  }

  public function testCeilPositiveRealNumber()
  {
    {
      $c = new \MyOddWeb\BigNumber(2.3);
      $d = $c->Ceil()->ToDouble();  //  use a double so we don't truncate it.
      $this->assertSame(3.0, $d);
    }

    {
      $c = new \MyOddWeb\BigNumber(7.999);
      $d = $c->Ceil()->ToDouble();  //  use a double so we don't truncate it.
      $this->assertSame(8.0, $d);
    }

    {
      $c = new \MyOddWeb\BigNumber(12.5);
      $d = $c->Ceil()->ToDouble();  //  use a double so we don't truncate it.
      $this->assertSame(13.0, $d);
    }
  }

  public function testCeilNegativeRealNumber()
  {
    {
      $c = new \MyOddWeb\BigNumber(-2.3);
      $d = $c->Ceil()->ToDouble();  //  use a double so we don't truncate it.
      $this->assertSame(-2.0, $d);
    }

    {
      $c = new \MyOddWeb\BigNumber(-7.999);
      $d = $c->Ceil()->ToDouble();  //  use a double so we don't truncate it.
      $this->assertSame(-7.0, $d);
    }

    {
      $c = new \MyOddWeb\BigNumber(-12.5);
      $d = $c->Ceil()->ToDouble();  //  use a double so we don't truncate it.
      $this->assertSame( -12.0, $d);
    }
  }

  public function testFloorPositiveInteger()
  {
    $c = new \MyOddWeb\BigNumber(12.0);
    $d = $c->Floor()->ToDouble();  //  use a double so we don't truncate it.
    $this->assertSame(12.0, $d);
  }

  public function testFloorNegativeInteger()
  {
    $c = new \MyOddWeb\BigNumber(-12.0);
    $d = $c->Floor()->ToDouble();  //  use a double so we don't truncate it.
    $this->assertSame(-12.0, $d);
  }

  public function testFloorPositiveRealNumber()
  {
    {
      $c = new \MyOddWeb\BigNumber(2.3);
      $d = $c->Floor()->ToDouble();  //  use a double so we don't truncate it.
      $this->assertSame(2.0, $d);
    }

    {
      $c = new \MyOddWeb\BigNumber(7.999);
      $d = $c->Floor()->ToDouble();  //  use a double so we don't truncate it.
      $this->assertSame(7.0, $d);
    }

    {
      $c = new \MyOddWeb\BigNumber(12.5);
      $d = $c->Floor()->ToDouble();  //  use a double so we don't truncate it.
      $this->assertSame(12.0, $d);
    }
  }

  public function testFloorNegativeRealNumber()
  {
    {
      $c = new \MyOddWeb\BigNumber(-2.3);
      $d = $c->Floor()->ToDouble();  //  use a double so we don't truncate it.
      $this->assertSame(-3.0, $d);
    }

    {
      $c = new \MyOddWeb\BigNumber(-7.999);
      $d = $c->Floor()->ToDouble();  //  use a double so we don't truncate it.
      $this->assertSame(-8.0, $d);
    }

    {
      $c = new \MyOddWeb\BigNumber(-12.5);
      $d = $c->Floor()->ToDouble();  //  use a double so we don't truncate it.
      $this->assertSame(-13.0, $d);
    }
  }

  public function testCreateNaN()
  {
    $n = new \MyOddWeb\BigNumber( "NaN" );
    $this->assertTrue( $n->IsNan());
  }

  public function testCreateWithNullString()
  {
    $this->setExpectedException("\MyOddWeb\BigNumberException" );
    new \MyOddWeb\BigNumber( NULL );
  }

  public function testTheGivenStringIsInvalid()
  {
    $this->setExpectedException("\MyOddWeb\BigNumberException" );
    new \MyOddWeb\BigNumber(" Hello World");
  }

  public function testGetNaturalETo150places()
  {
    $e1 = \MyOddWeb\BigNumber::e();
    $e1->Round(150);

    // one zero at the end is dropped...
    $se = "2.718281828459045235360287471352662497757247093699959574966967627724076630353547594571382178525166427427466391932003059921817413596629043572900334295261";

    //
    $this->assertSame($se, $e1->ToString());
  }

  public function testGetNaturalETo1000places()
  {
    $e1 = \MyOddWeb\BigNumber::e();

    // one zero at the end is dropped...
    $se = "2.7182818284590452353602874713526624977572470936999595749669676277240766303535475945713821785251664274274663919320030599218174135966290435729003342952605956307381323286279434907632338298807531952510190115738341879307021540891499348841675092447614606680822648001684774118537423454424371075390777449920695517027618386062613313845830007520449338265602976067371132007093287091274437470472306969772093101416928368190255151086574637721112523897844250569536967707854499699679468644549059879316368892300987931277361782154249992295763514822082698951936680331825288693984964651058209392398294887933203625094431173012381970684161403970198376793206832823764648042953118023287825098194558153017567173613320698112509961818815930416903515988885193458072738667385894228792284998920868058257492796104841984443634632449684875602336248270419786232090021609902353043699418491463140934317381436405462531520961836908887070167683964243781405927145635490613031072085103837505101157477041718986106873969655212671546889570350354";

    //
    $this->assertSame($se, $e1->ToString());
  }

  public function testSimpleMod()
  {
    $x = new \MyOddWeb\BigNumber ("5.23");
    $y = new \MyOddWeb\BigNumber (0.23);
    $z = $x->Mod($y)->ToString();
    $this->assertSame("0.17", $z );
  }

  public function testCompareSamNumberDifferentSign()
  {
    {
      $xrand = (rand() % 32767);
      $x = new \MyOddWeb\BigNumber ($xrand);
      $y = new \MyOddWeb\BigNumber (-1 * $xrand);

      // we are greater.
      $this->assertSame(1, $x->Compare($y));
    }

    {
      $xrand = (rand() % 32767);
      $x = new \MyOddWeb\BigNumber (-1 * $xrand);
      $y = new \MyOddWeb\BigNumber ($xrand);

      // we are smaller.
      $this->assertSame( -1, $x->Compare($y));
    }
  }

  public function testCompareRhsAndLhsDifferentSignsButGreaterByAbsolutValue()
  {
    {
      // by Absolute value we are greater.
      // but by sign, we are not.
      $x = new \MyOddWeb\BigNumber ( -5 );
      $y = new \MyOddWeb\BigNumber ( 3 );

      // we are smaller.
      $this->assertSame(-1, $x->Compare($y));
    }

    {
      // by absolute value and by sign, we are greater.
      $x = new \MyOddWeb\BigNumber ( 5 );
      $y = new \MyOddWeb\BigNumber ( -3 );

      // we are greater.
      $this->assertSame( 1, $x->Compare($y));
    }
  }

  public function testCompareRhsAndLhsDifferentSignsButSmallerByAbsolutValue()
  {
    {
      // by Absolute value we are greater.
      // but by sign, we are not.
      $x = new \MyOddWeb\BigNumber (-3);
      $y = new \MyOddWeb\BigNumber (5);

      // we are smaller.
      $this->assertSame(-1, $x->Compare($y));
    }

    {
      // by absolute value and by sign, we are greater.
      $x = new \MyOddWeb\BigNumber (3);
      $y = new \MyOddWeb\BigNumber (-5);

      // we are greater.
      $this->assertSame(1, $x->Compare($y));
    }
  }

  public function testCompareRhsAndLhsBothNegative()
  {
    // Absolute value is greater, but because of sign
    // we are not greater.
    {
      $x = new \MyOddWeb\BigNumber (-5);
      $y = new \MyOddWeb\BigNumber (-3);

      // we are smaller.
      $this->assertSame(-1, $x->Compare($y));
    }

    {
      $x = new \MyOddWeb\BigNumber (-3);
      $y = new \MyOddWeb\BigNumber (-5);

      // we are greater.
      $this->assertSame( 1, $x->Compare($y));
    }
  }

  public function testEqualNumbers()
  {
    {
      $x = new \MyOddWeb\BigNumber (-3);
      $y = new \MyOddWeb\BigNumber (-3);
      $this->assertTrue($x->IsEqual($y));
    }

    {
      $x = new \MyOddWeb\BigNumber (-3.1234);
      $y = new \MyOddWeb\BigNumber (-3.1234);
      $this->assertTrue($x->IsEqual($y));
    }

    {
      $x = new \MyOddWeb\BigNumber (3);
      $y = new \MyOddWeb\BigNumber (3);
      $this->assertTrue($x->IsEqual($y));
    }

    {
      $x = new \MyOddWeb\BigNumber (3);
      $y = new \MyOddWeb\BigNumber (-3);
      $this->assertFalse($x->IsEqual($y));
    }

    {
      $x = new \MyOddWeb\BigNumber (30);
      $y = new \MyOddWeb\BigNumber (3);
      $this->assertFalse($x->IsEqual($y));
    }

    {
      $x = new \MyOddWeb\BigNumber (3.1234);
      $y = new \MyOddWeb\BigNumber (3.12345);
      $this->assertFalse($x->IsEqual($y));
    }
  }

  public function testUnEqualNumbers()
  {
    {
      $x = new \MyOddWeb\BigNumber (-3);
      $y = new \MyOddWeb\BigNumber (-3);
      $this->assertFalse($x->IsUnequal($y));
    }

    {
      $x = new \MyOddWeb\BigNumber (-3.1234);
      $y = new \MyOddWeb\BigNumber (-3.1234);
      $this->assertFalse($x->IsUnequal($y));
    }

    {
      $x = new \MyOddWeb\BigNumber (3);
      $y = new \MyOddWeb\BigNumber (3);
      $this->assertFalse($x->IsUnequal($y));
    }

    {
      $x = new \MyOddWeb\BigNumber (3);
      $y = new \MyOddWeb\BigNumber (-3);
      $this->assertTrue($x->IsUnequal($y));
    }

    {
      $x = new \MyOddWeb\BigNumber (30);
      $y = new \MyOddWeb\BigNumber (3);
      $this->assertTrue($x->IsUnequal($y));
    }

    {
      $x = new \MyOddWeb\BigNumber (3.1234);
      $y = new \MyOddWeb\BigNumber (3.12345);
      $this->assertTrue($x->IsUnequal($y));
    }
  }

  public function testGreaterNumbers()
  {
    {
      $x = new \MyOddWeb\BigNumber (-3);
      $y = new \MyOddWeb\BigNumber (-3);
      $this->assertFalse($x->IsGreater($y));
    }

    {
      $x = new \MyOddWeb\BigNumber (-3);
      $y = new \MyOddWeb\BigNumber (-5);
      $this->assertTrue($x->IsGreater($y));
    }

    {
      $x = new \MyOddWeb\BigNumber (5);
      $y = new \MyOddWeb\BigNumber (3);
      $this->assertTrue($x->IsGreater($y));
    }

    {
      $x = new \MyOddWeb\BigNumber (3);
      $y = new \MyOddWeb\BigNumber (5);
      $this->assertFalse($x->IsGreater($y));
    }

    {
      $x = new \MyOddWeb\BigNumber (-5);
      $y = new \MyOddWeb\BigNumber (-3);
      $this->assertFalse($x->IsGreater($y));
    }

    {
      $x = new \MyOddWeb\BigNumber (-3.2);
      $y = new \MyOddWeb\BigNumber (-3.5);
      $this->assertTrue($x->IsGreater($y));
    }
  }

  public function testGreaterEqualNumbers()
  {
    {
      $x = new \MyOddWeb\BigNumber (-3);
      $y = new \MyOddWeb\BigNumber (-3);
      $this->assertTrue($x->IsGreaterEqual($y));
    }

    {
      $x = new \MyOddWeb\BigNumber (-3);
      $y = new \MyOddWeb\BigNumber (-5);
      $this->assertTrue($x->IsGreaterEqual($y));
    }

    {
      $x = new \MyOddWeb\BigNumber (5);
      $y = new \MyOddWeb\BigNumber (3);
      $this->assertTrue($x->IsGreaterEqual($y));
    }

    {
      $x = new \MyOddWeb\BigNumber (3);
      $y = new \MyOddWeb\BigNumber (5);
      $this->assertFalse($x->IsGreaterEqual($y));
    }

    {
      $x = new \MyOddWeb\BigNumber (-5);
      $y = new \MyOddWeb\BigNumber (-3);
      $this->assertFalse($x->IsGreaterEqual($y));
    }

    {
      $x = new \MyOddWeb\BigNumber (-3.2);
      $y = new \MyOddWeb\BigNumber (-3.5);
      $this->assertTrue($x->IsGreaterEqual($y));
    }
  }

  public function testLessNumbers()
  {
    {
      $x = new \MyOddWeb\BigNumber (-3);
      $y = new \MyOddWeb\BigNumber (-3);
      $this->assertFalse($x->IsLess($y));
    }

    {
      $x = new \MyOddWeb\BigNumber (-5);
      $y = new \MyOddWeb\BigNumber (-3);
      $this->assertTrue($x->IsLess($y));
    }

    {
      $x = new \MyOddWeb\BigNumber (3);
      $y = new \MyOddWeb\BigNumber (5);
      $this->assertTrue($x->IsLess($y));
    }

    {
      $x = new \MyOddWeb\BigNumber (5);
      $y = new \MyOddWeb\BigNumber (3);
      $this->assertFalse($x->IsLess($y));
    }

    {
      $x = new \MyOddWeb\BigNumber (-3);
      $y = new \MyOddWeb\BigNumber (-5);
      $this->assertFalse($x->IsLess($y));
    }

    {
      $x = new \MyOddWeb\BigNumber (-3.4);
      $y = new \MyOddWeb\BigNumber (-3.2);
      $this->assertTrue($x->IsLess($y));
    }
  }

  public function testLessOrEqualNumbers()
  {
    {
      $x = new \MyOddWeb\BigNumber (-3);
      $y = new \MyOddWeb\BigNumber (-3);
      $this->assertTrue($x->IsLessEqual($y));
    }

    {
      $x = new \MyOddWeb\BigNumber (-5);
      $y = new \MyOddWeb\BigNumber (-3);
      $this->assertTrue($x->IsLessEqual($y));
    }

    {
      $x = new \MyOddWeb\BigNumber (3);
      $y = new \MyOddWeb\BigNumber (5);
      $this->assertTrue($x->IsLessEqual($y));
    }

    {
      $x = new \MyOddWeb\BigNumber (5);
      $y = new \MyOddWeb\BigNumber (3);
      $this->assertFalse($x->IsLessEqual($y));
    }

    {
      $x = new \MyOddWeb\BigNumber (-3);
      $y = new \MyOddWeb\BigNumber (-5);
      $this->assertFalse($x->IsLessEqual($y));
    }

    {
      $x = new \MyOddWeb\BigNumber (-3.4);
      $y = new \MyOddWeb\BigNumber (-3.2);
      $this->assertTrue($x->IsLessEqual($y));
    }
  }

  public function testNaNIsNeitherOddNorEven()
  {
    $x = new \MyOddWeb\BigNumber ("NaN");
    $this->assertFalse($x->IsOdd());
    $this->assertFalse($x->IsEven());
  }

  public function testZeroIsEven()
  {
    $x = new \MyOddWeb\BigNumber (0);
    $this->assertFalse($x->IsOdd());
    $this->assertTrue($x->IsEven());
  }

  public function testEvenWholeNumber()
  {
    $x = new \MyOddWeb\BigNumber ( 1234 );
    $this->assertFalse($x->IsOdd());
    $this->assertTrue($x->IsEven());
  }

  public function testEvenDecimalNumber()
  {
    $x = new \MyOddWeb\BigNumber (1234.135799);
    $this->assertFalse($x->IsOdd());
    $this->assertTrue($x->IsEven());
  }

  public function testEvenBigWholeNumber()
  {
    $x = new \MyOddWeb\BigNumber ("1234567890987654321123456780");
    $this->assertFalse($x->IsOdd());
    $this->assertTrue($x->IsEven());
  }

  public function testOddWholeNumber()
  {
    $x = new \MyOddWeb\BigNumber (1235);
    $this->assertTrue($x->IsOdd());
    $this->assertFalse($x->IsEven());
  }

  public function testOddDecimalNumber()
  {
    $x = new \MyOddWeb\BigNumber (1235.246);
    $this->assertTrue($x->IsOdd());
    $this->assertFalse($x->IsEven());
  }

  public function testOddBigWholeNumber()
  {
    $x = new \MyOddWeb\BigNumber ("1234567890987654321123456781");
    $this->assertTrue($x->IsOdd());
    $this->assertFalse($x->IsEven());
  }

  public function testOddBigDecimalNumber()
  {
    $x = new \MyOddWeb\BigNumber ("1234567890987654321123456781.2468008642");
    $this->assertTrue($x->IsOdd());
    $this->assertFalse($x->IsEven());
  }

  public function testEvenBigDecimalNumber()
  {
    $x = new \MyOddWeb\BigNumber ("1234567890987654321123456780.1357997531");
    $this->assertFalse($x->IsOdd());
    $this->assertTrue($x->IsEven());
  }

  public function testModuloDivisorGreaterThanNumber()
  {
    $x = new \MyOddWeb\BigNumber ("10");
    $mod = $x->Mod(20)->ToInt();
    $this->assertSame( 10, $mod );
  }

  public function testNegativeModuloDenominatorSmallerThanNumerator()
  {
    $x = new \MyOddWeb\BigNumber ("10");
    $mod = $x->Mod(-3)->ToInt();
    $this->assertSame( 1, $mod);
  }

  public function testNegativeModuloDenominatorGreaterThanNumerator()
  {
    $x = new \MyOddWeb\BigNumber ("10");
    $mod = $x->Mod( -20 )->ToInt();
    $this->assertSame( 10, $mod);
  }

  public function testModuloAllCasesSmallerDenominator()
  {
    {
      // 10 % 3 = 1
      $x = new \MyOddWeb\BigNumber ("10");
      $mod = $x->Mod(3)->ToInt();
      $this->assertSame(1, $mod);
    }
    {
      // 10 % -3 = 1
      $x = new \MyOddWeb\BigNumber ("10");
      $mod = $x->Mod(-3)->ToInt();
      $this->assertSame(1, $mod);
    }
    {
      // -10 % -3 = -1
      $x = new \MyOddWeb\BigNumber ("-10");
      $mod = $x->Mod(-3)->ToInt();
      $this->assertSame(-1, $mod);
    }
    {
      // -10 % 3 = -1
      $x = new \MyOddWeb\BigNumber ("-10");
      $mod = $x->Mod(3)->ToInt();
      $this->assertSame(-1, $mod);
    }
  }

  public function testModuloAllCasesGreaterDenominator()
  {
    {
      // 10 % 20 = 10
      $x = new \MyOddWeb\BigNumber ("10");
      $mod = $x->Mod(20)->ToInt();
      $this->assertSame(10, $mod);
    }
    {
      // 10 % -20 = 10
      $x = new \MyOddWeb\BigNumber ("10");
      $mod = $x->Mod(-20)->ToInt();
      $this->assertSame(10, $mod);
    }
    {
      // -10 % -20 = -10
      $x = new \MyOddWeb\BigNumber ("-10");
      $mod = $x->Mod(-20)->ToInt();
      $this->assertSame(-10, $mod);
    }
    {
      // -10 % 20 = -10
      $x = new \MyOddWeb\BigNumber ("-10");
      $mod = $x->Mod(20)->ToInt();
      $this->assertSame(-10, $mod);
    }
  }

  public function testIntegerOfPositiveNumber()
  {
    {
      $x = new \MyOddWeb\BigNumber ("1.2");
      $integer = $x->Integer()->ToDouble();
      $this->assertSame(1.0, $integer);
    }
    {
      $x = new \MyOddWeb\BigNumber ("12345.2");
      $integer = $x->Integer()->ToDouble();
      $this->assertSame(12345.0, $integer);
    }
    {
      $x = new \MyOddWeb\BigNumber ("12");
      $integer = $x->Integer()->ToDouble();
      $this->assertSame(12.0, $integer);
    }
    {
      $x = new \MyOddWeb\BigNumber ("0.23456");
      $integer = $x->Integer()->ToDouble();
      $this->assertSame(0.0, $integer);
      $this->assertTrue( $x->IsZero() );
    }
  }

  public function testIntegerOfNegativeNumber()
  {
    {
      $x = new \MyOddWeb\BigNumber ("-1.2");
      $integer = $x->Integer()->ToDouble();
      $this->assertSame(-1.0, $integer);
    }
    {
      $x = new \MyOddWeb\BigNumber ("-12345.2");
      $integer = $x->Integer()->ToDouble();
      $this->assertSame(-12345.0, $integer);
    }
    {
      $x = new \MyOddWeb\BigNumber ("-12");
      $integer = $x->Integer()->ToDouble();
      $this->assertSame(-12.0, $integer);
    }
    {
      $x = new \MyOddWeb\BigNumber ("-0.23456");
      $integer = $x->Integer()->ToDouble();
      $this->assertSame(0.0, $integer);
      $this->assertTrue($x->IsZero());
    }
  }

  public function testFractionOfNegativeNumber()
  {
    {
      $x = new \MyOddWeb\BigNumber ("-1.2");
      $fraction = $x->Frac()->ToString();
      $this->assertSame("-0.2", $fraction);
    }
    {
      $x = new \MyOddWeb\BigNumber ("-12345.678");
      $fraction = $x->Frac()->ToString();
      $this->assertSame("-0.678", $fraction);
    }
    {
      $x = new \MyOddWeb\BigNumber ("-12345.2");
      $fraction = $x->Frac()->ToString();
      $this->assertSame("-0.2", $fraction);
    }
    {
      $x = new \MyOddWeb\BigNumber ("-12");
      $fraction = $x->Frac()->ToString();
      $this->assertSame( "0", $fraction);
      $this->assertTrue($x->IsZero());
    }
    {
      $x = new \MyOddWeb\BigNumber ("-0.23456");
      $fraction = $x->Frac()->ToString();
      $this->assertSame("-0.23456", $fraction);
    }
  }

  public function testFractionOfPositiveNumber()
  {
    {
      $x = new \MyOddWeb\BigNumber ("1.2");
      $fraction = $x->Frac()->ToString();
      $this->assertSame("0.2", $fraction);
    }
    {
      $x = new \MyOddWeb\BigNumber ("12345.678");
      $fraction = $x->Frac()->ToString();
      $this->assertSame("0.678", $fraction);
    }
    {
      $x = new \MyOddWeb\BigNumber ("12345.2");
      $fraction = $x->Frac()->ToString();
      $this->assertSame("0.2", $fraction);
    }
    {
      $x = new \MyOddWeb\BigNumber ("12");
      $fraction = $x->Frac()->ToString();
      $this->assertSame("0", $fraction);
      $this->assertTrue($x->IsZero());
    }
    {
      $x = new \MyOddWeb\BigNumber ("0.23456");
      $fraction = $x->Frac()->ToString();
      $this->assertSame("0.23456", $fraction);
    }
  }

  public function testRoundPositiveNumberNoDecimalsShouldRoundDown()
  {
    $x = new \MyOddWeb\BigNumber ("12.23456");
    $fraction = $x->Round()->ToString();
    $this->assertSame("12", $fraction);
  }

  public function testRoundNegativeNumberNoDecimalsShouldRoundDown()
  {
    $x = new \MyOddWeb\BigNumber ("-12.23456");
    $fraction = $x->Round()->ToString();
    $this->assertSame("-12", $fraction);
  }

  public function testRoundPositiveDefaultNoDecimals()
  {
    $x = new \MyOddWeb\BigNumber ("12.23456");
    $fraction = $x->Round()->ToString();
    $this->assertSame("12", $fraction);
  }

  public function testRoundNegativeDefaultNoDecimals()
  {
    $x = new \MyOddWeb\BigNumber ("-12.23456");
    $fraction = $x->Round()->ToString();
    $this->assertSame("-12", $fraction);
  }

  public function testRoundPositiveNumberNoDecimalsShouldRoundUp()
  {
    $x = new \MyOddWeb\BigNumber ("12.63456");
    $fraction = $x->Round()->ToString();
    $this->assertSame("13", $fraction);
  }

  public function testRoundNegativeNumberNoDecimalsShouldRoundUp()
  {
    $x = new \MyOddWeb\BigNumber ("-12.63456");
    $fraction = $x->Round()->ToString();
    $this->assertSame("-13", $fraction);
  }

  public function testRoundPositiveNumberWithPrecision()
  {
    $x = new \MyOddWeb\BigNumber ("12.63456");
    $fraction = $x->Round(3)->ToString();
    $this->assertSame("12.635", $fraction);
  }

  public function testRoundNegativeNumberWithPrecision()
  {
    $x = new \MyOddWeb\BigNumber ("-12.63456");
    $fraction = $x->Round(3)->ToString();
    $this->assertSame("-12.635", $fraction);
  }

  public function testPositiveNumberIsCreatedAsInteger()
  {
    $x = new \MyOddWeb\BigNumber ("12");
    $this->assertTrue( $x->IsInteger() );
  }

  public function testNegativeNumberIsCreatedAsInteger()
  {
    $x = new \MyOddWeb\BigNumber ("-12");
    $this->assertTrue($x->IsInteger());
  }

  public function testTruncatedDoubleBecomesInteger()
  {
    $x = new \MyOddWeb\BigNumber ("12.1234");
    $this->assertFalse($x->IsInteger());

    $x->Trunc();
    $this->assertTrue($x->IsInteger());
  }

  public function testTruncatedDoubleEffectivelyBecomesInteger()
  {
    $x = new \MyOddWeb\BigNumber ("12.00001234");
    $this->assertFalse($x->IsInteger());

    $x->Trunc(4); // because of the zeros, it becomes an in
    $this->assertTrue($x->IsInteger());
  }

  public function testPositiveNumberIsCreatedAsIntegerEvenWithZeros()
  {
    $x = new \MyOddWeb\BigNumber ("12.00");
    $this->assertTrue($x->IsInteger());
  }

  public function testNegativeNumberIsCreatedAsIntegerEvenWithZeros()
  {
    $x = new \MyOddWeb\BigNumber ("-12.00");
    $this->assertTrue($x->IsInteger());
  }

  public function testAdditionOfPositiveIntegerIsInteger()
  {
    $x = new \MyOddWeb\BigNumber ("12.00");
    $y = new \MyOddWeb\BigNumber ("34.00");
    $z = $x->Add($y);
    $this->assertTrue($z->IsInteger());
  }

  public function testMultiplicationOfPositiveIntegerIsInteger()
  {
    $x = new \MyOddWeb\BigNumber ("12.00");
    $y = new \MyOddWeb\BigNumber ("34.00");
    $z = $x->Mul($y);
    $this->assertTrue( $z->IsInteger());
  }

  public function testAdditionOfNegativeIntegerIsInteger()
  {
    $x = new \MyOddWeb\BigNumber ("-12.00");
    $y = new \MyOddWeb\BigNumber ("-34.00");
    $z = $x->Add($y);
    $this->assertTrue($z->IsInteger());
  }

  public function testMultiplicationOfNegativeIntegerIsInteger()
  {
    $x = new \MyOddWeb\BigNumber ("-12.00");
    $y = new \MyOddWeb\BigNumber ("-34.00");
    $z = $x->Mul($y);
    $this->assertTrue($z->IsInteger());
  }

  public function testZeroIsAnInteger()
  {
    $x = new \MyOddWeb\BigNumber (0);
    $this->assertTrue($x->IsInteger());
  }

  public function testGetPiTo150places()
  {
    $pi1 = \MyOddWeb\BigNumber::pi();
    $pi1->Round(150);

    $spi = "3.141592653589793238462643383279502884197169399375105820974944592307816406286208998628034825342117067982148086513282306647093844609550582231725359408128";

    //
    $this->assertSame($spi, $pi1->ToString());
  }

  public function testGetPiTo1000places()
  {
    $pi1 = \MyOddWeb\BigNumber::pi();

    $spi = "3.1415926535897932384626433832795028841971693993751058209749445923078164062862089986280348253421170679821480865132823066470938446095505822317253594081284811174502841027019385211055596446229489549303819644288109756659334461284756482337867831652712019091456485669234603486104543266482133936072602491412737245870066063155881748815209209628292540917153643678925903600113305305488204665213841469519415116094330572703657595919530921861173819326117931051185480744623799627495673518857527248912279381830119491298336733624406566430860213949463952247371907021798609437027705392171762931767523846748184676694051320005681271452635608277857713427577896091736371787214684409012249534301465495853710507922796892589235420199561121290219608640344181598136297747713099605187072113499999983729780499510597317328160963185950244594553469083026425223082533446850352619311881710100031378387528865875332083814206171776691473035982534904287554687311595628638823537875937519577818577805321712268066130019278766111959092164201989";

    //
    $this->assertSame($spi, $pi1->ToString());
  }

  public function testThirtyDegreesToRadian()
  {
    {
      $rad = \MyOddWeb\BigNumber(30)->ToRadian(10);
      $z = $rad->ToString();
      $this->assertSame("0.5235987756", $z);
    }
    {
      $rad = \MyOddWeb\BigNumber(30)->ToRadian(20);
      $z = $rad->ToString();
      $this->assertSame("0.52359877559829887308", $z);
    }
  }

  public function testNegativeThirtyDegreesToRadian()
  {
    {
      $rad = \MyOddWeb\BigNumber(-30)->ToRadian(10);
      $z = $rad->ToString();
      $this->assertSame("-0.5235987756", $z);
    }
    {
      $rad = (new \MyOddWeb\BigNumber(-30))->ToRadian(20);
      $z = $rad->ToString();
      $this->assertSame("-0.52359877559829887308", $z);
    }
  }

  public function testThirtyDegreesRadianToDegree()
  {
    {
      $rad = (new \MyOddWeb\BigNumber("0.5235987756" ))->ToDegree(10);
      $z = $rad->ToString();
      $this->assertSame("30.0000000001", $z);
    }
    {
      $rad = (new \MyOddWeb\BigNumber("0.52359877559829887308"))->ToDegree(20);
      $z = $rad->ToString();
      $this->assertSame("30.00000000000000000017", $z);
    }
  }

  public function testNegativeThirtyDegreesRadianToDegree()
  {
    {
     $rad = (new \MyOddWeb\BigNumber("-0.5235987756"))->ToDegree(10);
      $z = $rad->ToString();
      $this->assertSame("-30.0000000001", $z);
    }
    {
      $rad = (new \MyOddWeb\BigNumber("-0.52359877559829887308"))->ToDegree(20);
      $z = $rad->ToString();
      $this->assertSame("-30.00000000000000000017", $z);
    }
  }

  public function testPiToDegree()
  {
    $rad = \MyOddWeb\BigNumber(\MyOddWeb\BigNumber::pi() )->ToDegree(10);
    $z = $rad->ToString();
    $this->assertSame("180", $z);
  }

  public function testNegativePiToDegree()
  {
    $rad = \MyOddWeb\BigNumber(\MyOddWeb\BigNumber::pi())->ToDegree(10);
    $rad->Mul(-1);
    $z = $rad->ToString();
    $this->assertSame("-180", $z);
  }

  public function testQuickModCheckForEven()
  {
    $rnd = (rand() % 32767) * 2;  //  it has to be even...
    $evenNumber = new \MyOddWeb\BigNumber($rnd );
    $this->assertTrue($evenNumber->IsEven());//  it has to be even...
    $this->assertSame(0, $evenNumber->Mod(2)->ToInt() );
  }

  public function testQuickModCheckForOdd()
  {
    $rnd = (rand() % 32767) * 2 + 1;  //  it has to be odd...
    $evenNumber = new \MyOddWeb\BigNumber( $rnd );
    $this->assertFalse( $evenNumber->IsEven());   //  it has to be odd...
    $this->assertSame(1, $evenNumber->Mod(2)->ToInt() );
  }

  public function testCannotRoundNanNumbers()
  {
    $x = new \MyOddWeb\BigNumber (1);
    $x->Div(0);
    $this->assertTrue($x->IsNan());
    $x->Round(10);

    $this->assertTrue($x->IsNan());

    $z = $x->ToString();
    $this->assertSame("NaN", $z);
  }

  public function testToBase2OfNotANumber()
  {
    $x = new \MyOddWeb\BigNumber(5);
    $x->Div(0);
    $this->assertTrue( $x->IsNan() );

    $base = $x->ToBase(2);
    $this->assertSame("NaN", $base);
  }

  public function testToBase2PostiveSmallIntegers()
  {
    {
      $x = new \MyOddWeb\BigNumber(5);
      $base = $x->ToBase(2);
      $this->assertSame("101", $base);
    }
    {
      $x = new \MyOddWeb\BigNumber(1024);
      $base = $x->ToBase(2);
      $this->assertSame("10000000000", $base);
    }
    {
      $x = new \MyOddWeb\BigNumber(1023);
      $base = $x->ToBase(2);
      $this->assertSame("1111111111", $base);
    }
    {
      $x = new \MyOddWeb\BigNumber(6);
      $base = $x->ToBase(2);
      $this->assertSame("110", $base);
    }
  }

  public function testToBase8PostiveSmallIntegers()
  {
    {
      $x = new \MyOddWeb\BigNumber(140);
      $base = $x->ToBase(8);
      $this->assertSame("214", $base);
    }
    {
      $x = new \MyOddWeb\BigNumber(1024);
      $base = $x->ToBase(8);
      $this->assertSame("2000", $base);
    }
    {
      $x = new \MyOddWeb\BigNumber(1023);
      $base = $x->ToBase(8);
      $this->assertSame("1777", $base);
    }
  }

  public function testToBase2NegativeSmallIntegers()
  {
    {
      $x = new \MyOddWeb\BigNumber(-5);
      $base = $x->ToBase(2);
      $this->assertSame("-101", $base);
    }
    {
      $x = new \MyOddWeb\BigNumber(-6);
      $base = $x->ToBase(2);
      $this->assertSame("-110", $base);
    }
  }

  public function testToBase16PostiveSmallIntegers()
  {
    {
      $x = new \MyOddWeb\BigNumber(1023);
      $base = $x->ToBase(16);
      $this->assertSame("3FF", $base);
    }
    {
      $x = new \MyOddWeb\BigNumber(1024);
      $base = $x->ToBase(16);
      $this->assertSame("400", $base);
    }
  }

  public function testToStringBase36PostiveSmallIntegers()
  {
    {
      $x = new \MyOddWeb\BigNumber(35);
      $base = $x->ToBase(36);
      $this->assertSame("Z", $base);
    }
    {
      $x = new \MyOddWeb\BigNumber(36);
      $base = $x->ToBase(36);
      $this->assertSame("10", $base);
    }
    {
      $x = new \MyOddWeb\BigNumber(1023);
      $base = $x->ToBase(36);
      $this->assertSame("SF", $base);
    }
  }

  public function testToBase62PostiveSmallIntegers()
  {
    {
      $x = new \MyOddWeb\BigNumber(61);
      $base = $x->ToBase(62);
      $this->assertSame("z", $base);
    }
    {
      $x = new \MyOddWeb\BigNumber(62);
      $base = $x->ToBase(62);
      $this->assertSame("10", $base);
    }
    {
      $x = new \MyOddWeb\BigNumber(1023);
      $base = $x->ToBase(62);
      $this->assertSame("GV", $base);
    }
    {
      $x = new \MyOddWeb\BigNumber(204789);
      $base = $x->ToBase(62);
      $this->assertSame("rH3", $base);
    }
  }

  public function testCannotConvertToABaseGreaterThan62()
  {
    $x = new \MyOddWeb\BigNumber(1023);
    $bigBase = (rand() % 32767) + 63;
    $this->setExpectedException("\MyOddWeb\BigNumberException" );
    $x->ToBase($bigBase);
  }

  public function testCannotConvertToBaseZero()
  {
    $x = new \MyOddWeb\BigNumber(1023);
    $this->setExpectedException("\MyOddWeb\BigNumberException" );
    $x->ToBase(0);
  }

  public function testCannotConvertToBaseOne()
  {
    $x = new \MyOddWeb\BigNumber(1023);
    $this->setExpectedException("\MyOddWeb\BigNumberException" );
    $x->ToBase(1);
  }

  public function testCannotConvertToBase63()
  {
    $x = new \MyOddWeb\BigNumber(1023);
    $this->setExpectedException("\MyOddWeb\BigNumberException" );
    $x->ToBase(63);
  }

  public function testToBase8PostiveFractionNumber()
  {
    {
      $x = new \MyOddWeb\BigNumber(10.8);
      $base = $x->ToBase(8, 2);
      $this->assertSame("12.63", $base);
    }
  }

  public function testToBase5PostiveFractionNumber()
  {
    {
      $x = new \MyOddWeb\BigNumber(0.375);
      $base = $x->ToBase(5, 4);
      $this->assertSame("0.1414", $base);
    }
  }

  public function testToBaseBase2PostiveFractionNumber()
  {
    {
      $x = new \MyOddWeb\BigNumber(0.375);
      $base = $x->ToBase(2, 10);
      $this->assertSame("0.011", $base);
    }
  }

  public function testToBasePrecisionLessThanNumberOfDecimals()
  {
    {
      $x = new \MyOddWeb\BigNumber(0.375);
      $base = $x->ToBase(10, 1);
      $this->assertSame("0.3", $base);

      $base = $x->ToBase(10, 2);
      $this->assertSame("0.37", $base);

      $base = $x->ToBase(10, 3);
      $this->assertSame("0.375", $base);

      $base = $x->ToBase(10, 10);
      $this->assertSame("0.375", $base);
    }

    {
      $x = new \MyOddWeb\BigNumber(1234.375);
      $base = $x->ToBase(10, 1);
      $this->assertSame("1234.3", $base);

      $base = $x->ToBase(10, 2);
      $this->assertSame("1234.37", $base);

      $base = $x->ToBase(10, 3);
      $this->assertSame("1234.375", $base);

      $base = $x->ToBase(10, 10);
      $this->assertSame("1234.375", $base);
    }
  }
}
?>