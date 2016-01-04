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

class TestMul extends PHPUnit_Framework_TestCase
{
  public function testMultiplyTwoSimplePositiveNumbers ()
  {
    {
      $num = \MyOddWeb\BigNumber("5")->Mul(\MyOddWeb\BigNumber("15"))->ToInt();
      $this->assertSame(75, $num);
    }

    {
      $num = \MyOddWeb\BigNumber("15")->Mul(\MyOddWeb\BigNumber("5"))->ToInt();
      $this->assertSame(75, $num);
    }

    {
      $num = \MyOddWeb\BigNumber("15")->Mul(\MyOddWeb\BigNumber("25"))->ToInt();
      $this->assertSame(375, $num);
    }
  }

  public function testMultiplyTwoSimpleNegativeNumbers()
  {
    {
      $num = \MyOddWeb\BigNumber("-5")->Mul(\MyOddWeb\BigNumber("-15"))->ToInt();
      $this->assertSame(75, $num);
    }

    {
      $num = \MyOddWeb\BigNumber("-15")->Mul(\MyOddWeb\BigNumber("-5"))->ToInt();
      $this->assertSame(75, $num);
    }

    {
      $num = \MyOddWeb\BigNumber("-15")->Mul(\MyOddWeb\BigNumber("-25"))->ToInt();
      $this->assertSame(375, $num);
    }
  }

  public function testMultiplyTwoSimpleNegativeAndNegativeNumbers()
  {
    {
      $num = \MyOddWeb\BigNumber("5")->Mul(\MyOddWeb\BigNumber("-15"))->ToInt();
      $this->assertSame(-75, $num);
    }

    {
      $num = \MyOddWeb\BigNumber("15")->Mul(\MyOddWeb\BigNumber("-5"))->ToInt();
      $this->assertSame(-75, $num);
    }

    {
      $num = \MyOddWeb\BigNumber("15")->Mul(\MyOddWeb\BigNumber("-25"))->ToInt();
      $this->assertSame(-375, $num);
    }

    {
      $num = \MyOddWeb\BigNumber("-5")->Mul(\MyOddWeb\BigNumber("15"))->ToInt();
      $this->assertSame(-75, $num);
    }

    {
      $num = \MyOddWeb\BigNumber("-15")->Mul(\MyOddWeb\BigNumber("5"))->ToInt();
      $this->assertSame(-75, $num);
    }

    {
      $num = \MyOddWeb\BigNumber("-15")->Mul(\MyOddWeb\BigNumber("25"))->ToInt();
      $this->assertSame(-375, $num);
    }
  }

  public function testMultiplyPositiveLongLongNumbers()
  {
    {
      $num = \MyOddWeb\BigNumber("18446744073709551615")->Mul(\MyOddWeb\BigNumber("15"))->ToString();
      $this->assertSame( "276701161105643274225", $num);
    }
    {
      $num = \MyOddWeb\BigNumber("18446744073709551615")->Mul(\MyOddWeb\BigNumber("18446744073709551615"))->ToString();
      $this->assertSame("340282366920938463426481119284349108225", $num);
    }
  }

  public function testMultiplyNegativeLongLongNumbers()
  {
    {
      $num = \MyOddWeb\BigNumber("-18446744073709551615")->Mul(\MyOddWeb\BigNumber("-15"))->ToString();
      $this->assertSame("276701161105643274225", $num);
    }
    {
      $num = \MyOddWeb\BigNumber("-18446744073709551615")->Mul(\MyOddWeb\BigNumber("-18446744073709551615"))->ToString();
      $this->assertSame("340282366920938463426481119284349108225", $num);
    }
  }

  public function testMultiplyNegativeAndPositiveLongLongNumbers()
  {
    {
      $num = \MyOddWeb\BigNumber("18446744073709551615")->Mul(\MyOddWeb\BigNumber("-15"))->ToString();
      $this->assertSame("-276701161105643274225", $num);
    }
    {
      $num = \MyOddWeb\BigNumber("-18446744073709551615")->Mul(\MyOddWeb\BigNumber("15"))->ToString();
      $this->assertSame("-276701161105643274225", $num);
    }
    {
      $num = \MyOddWeb\BigNumber("-18446744073709551615")->Mul(\MyOddWeb\BigNumber("18446744073709551615"))->ToString();
      $this->assertSame("-340282366920938463426481119284349108225", $num);
    }
    {
      $num = \MyOddWeb\BigNumber("18446744073709551615")->Mul(\MyOddWeb\BigNumber("-18446744073709551615"))->ToString();
      $this->assertSame("-340282366920938463426481119284349108225", $num);
    }
  }

  // make sure the numbers are not too big.
  public function testMultiplyPositiveRandom()
  {
    $x = (rand() % 32767);
    $y = (rand() % 32767);

    $num = \MyOddWeb\BigNumber($x)->Mul(\MyOddWeb\BigNumber($y))->ToInt();
    $this->assertSame(($x * $y), $num);
  }

  public function testZeroMultiplyIsZero()
  {
    {
      $x = \MyOddWeb\BigNumber(0);
      $y = \MyOddWeb\BigNumber((rand() % 32767));

      $num = $x->Mul($y)->ToInt();
      $this->assertSame(0, $num);
      $this->assertTrue( $x->IsZero() );
    }
    {
      $x = \MyOddWeb\BigNumber((rand() % 32767));
      $y = \MyOddWeb\BigNumber(0);

      $num = $x->Mul($y)->ToInt();
      $this->assertSame(0, $num);
      $this->assertTrue($x->IsZero());
    }
    {
      $x = \MyOddWeb\BigNumber(0);
      $y = \MyOddWeb\BigNumber(0);

      $num = $x->Mul($y)->ToInt();
      $this->assertSame(0, $num);
      $this->assertTrue($x->IsZero());
    }
  }

  public function testMultiplyPositiveDecimalNumbers()
  {
    $x = \MyOddWeb\BigNumber(10.1);
    $y = \MyOddWeb\BigNumber(10.12345);

    $dr = $x->Mul($y)->ToDouble();
    $de = 102.246845;
    $this->assertSame($de, $dr);
  }

  public function testMultiplyPositiveDecimalNumbersWithZeroWholeNumber()
  {
    $x = \MyOddWeb\BigNumber(0.1);
    $y = \MyOddWeb\BigNumber(0.12345);

    $dr = $x->Mul($y)->ToDouble();
    $de = 0.012345;
    $this->assertSame($de, $dr);
  }

  public function testMultiplyPositiveDecimalNumbersBothNegative()
  {
    $x = \MyOddWeb\BigNumber(-10.1);
    $y = \MyOddWeb\BigNumber(-10.12345);

    $dr = $x->Mul($y)->ToDouble();
    $de = 102.246845;
    $this->assertSame($de, $dr);
  }

  public function testMultiplyPositiveDecimalNumbersOneNegative()
  {
    {
      $x = \MyOddWeb\BigNumber(10.1);
      $y = \MyOddWeb\BigNumber(-10.12345);

      $dr = $x->Mul($y)->ToDouble();
      $de = -102.246845;
      $this->assertSame($de, $dr);
    }
    {
      $x = \MyOddWeb\BigNumber(-10.1);
      $y = \MyOddWeb\BigNumber(10.12345);

      $dr = $x->Mul($y)->ToDouble();
      $de = -102.246845;
      $this->assertSame($de, $dr);
    }
  }

  public function testRaiseSmallPositveNumberToPower()
  {
    $x = \MyOddWeb\BigNumber(2);
    $y = $x->Pow(10);
    $this->assertSame(1024, $y->ToInt());
  }

  public function testSimpleSquare()
  {
    {
      $x = \MyOddWeb\BigNumber(2);
      $y = $x->Pow(2);
      $this->assertSame(4, $y->ToInt() );
    }

    {
      $x = \MyOddWeb\BigNumber(3);
      $y = $x->Pow(2);
      $this->assertSame(9, $y->ToInt());
    }
  }

  public function testAnyNumberRaisedToZero()
  {
    $r = (rand() % 32767);
    $x = \MyOddWeb\BigNumber( $r );
    $y = $x->Pow(0);
    $this->assertSame( 1, $y->ToInt() );
  }

  public function testZeroRaisedToZero()
  {
    $x = \MyOddWeb\BigNumber(0);
    $y = $x->Pow(0);
    $this->assertSame(1, $y->ToInt());
  }

  public function testSmallNumberRaisedToLargeNumber()
  {
    $x = \MyOddWeb\BigNumber(2);
    $y = $x->Pow(128);
    $this->assertSame("340282366920938463463374607431768211456", $y->ToString());
  }

  public function testSmallNumberRaisedToLargeNegativeNumber()
  {
    $x = \MyOddWeb\BigNumber(2);
    $y = $x->Pow(-40);
    $z = $y->ToString();
    $this->assertSame("0.0000000000009094947017729282379150390625", $z );
  }

  public function testAnyNumberRaisedToOne()
  {
    $r = (rand() % 32767);
    $x = \MyOddWeb\BigNumber($r);
    $y = $x->Pow(1);
    $this->assertSame($r, $y->ToInt());
  }

  public function testNegativeWholePowerPositiveNumber()
  {
    {
      $x = \MyOddWeb\BigNumber(2);
      $z = $x->Pow(-3)->ToDouble();
      $this->assertSame(0.125, $z);
    }

    {
      $x = \MyOddWeb\BigNumber(2.5);
      $z = $x->Pow(-6)->ToDouble();
      $this->assertSame(0.004096, $z);
    }
  }

  public function testDecimalPowerNumberSmallerThan1()
  {
    $x = \MyOddWeb\BigNumber(7);
    $x->Pow(0.3, 10 ); //  1.7927899625209972283171715190286289548525381067781507311109
    $z = $x->ToString();
    $this->assertSame("1.7927899625", $z);
  }

  public function testNegativeOnePower()
  {
    $x = new \MyOddWeb\BigNumber(7);
    $x->Pow(-1, 10); //  0.14285714285714285714285714285714
    $z = $x->ToString();
    $this->assertSame("0.1428571428", $z);
  }

  public function testSquareRootOfTwo()
  {
    $x = new \MyOddWeb\BigNumber(4);
    $y = $x->Sqrt();
    $z = $y->ToString(); //  2
    $this->assertSame("2", $z);
  }

  public function testSquareRootOfThree15DecimalPlaces()
  {
    $x = \MyOddWeb\BigNumber(3);
    $y = $x->Sqrt( 15 );
    $z = $y->ToString(); //  1.7320508075688772935274463415058723669428052538103806280558
    $this->assertSame("1.732050807568877", $z);
  }

  public function testSquareRootOfThree30DecimalPlaces()
  {
    $x = \MyOddWeb\BigNumber(3);
    $y = $x->Sqrt(30);
    $z = $y->ToString(); //  1.7320508075688772935274463415058723669428052538103806280558
    $this->assertSame("1.732050807568877293527446341506", $z);
  }

  public function testSquareRootOfSixteen()
  {
    $x = \MyOddWeb\BigNumber( 16 );
    $y = $x->Sqrt();
    $z = $y->ToString(); //  4
    $this->assertSame("4", $z);
  }

  public function testSquareRootOfLargeNumber()
  {
    $x = \MyOddWeb\BigNumber ( "18446744073709551616" );
    $y = $x->Sqrt();
    $z = $y->ToString(); //  4294967296
    $this->assertSame("4294967296", $z);
  }

  public function testSquareRootOfLargeNegativeNumber ()
  {
    $x = \MyOddWeb\BigNumber( "-18446744073709551616" );
    $y = $x->Sqrt();
    $this->assertTrue($y->IsNan());
  }

  public function testSquareRootOfSmallNegativeNumber()
  {
    $x = \MyOddWeb\BigNumber( "-4" );
    $y = $x->Sqrt();
    $this->assertTrue($y->IsNan());
  }

  public function testSquareRootOfZero()
  {
    $x = \MyOddWeb\BigNumber( "0" );
    $y = $x->Sqrt();
    $z = $y->ToString();
    $this->assertSame("0", $z);
    $this->assertTrue($y->IsZero() );
  }

  public function testNthRootOfZero()
  {
    $x = \MyOddWeb\BigNumber( "0" );
    $y = $x->Root( 17 );
    $z = $y->ToString();
    $this->assertSame("0", $z);
    $this->assertTrue($y->IsZero());
  }

  public function testNthRootIsZero()
  {
    $x = \MyOddWeb\BigNumber( 212 );
    $y = $x->Root(0);
    $this->assertTrue($y->IsNan() );
  }

  public function testNthRootOfOne()
  {
    $rnd = (rand() % 32767);
    $x = \MyOddWeb\BigNumber(1);
    $y = $x->Root($rnd);
    $z = $y->ToString();
    $this->assertSame("1", $z);
  }

  public function testCubeRootOfTwentySeven()
  {
    $x = \MyOddWeb\BigNumber( 27 );
    $y = $x->Root(3);
    $z = $y->ToString();
    $this->assertSame("3", $z);
  }

  public function testSqrtOfZeroPointFive()
  {
    $x = \MyOddWeb\BigNumber( 0.5 );
    $y = $x->Sqrt(10);  //  0.70710678118654752440084436210485
    $z = $y->ToString();
    $this->assertSame("0.7071067812", $z);
  }
}
?>