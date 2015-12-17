# myoddweb.bignumber.php #
A php bignumber class that does not have any external dependency.
Based on the [myoddweb.bignumber.cpp project](https://github.com/FFMG/myoddweb.bignumber.cpp)

# Usage #
## In your project ##

Simply include the files and "src/BigNumber.h" and "src/BigNumber.cpp" in your project.    
The other files are just testing and this project.

    // in your script
    include "path/to/code/BigNumber.php"
   
    // use it
    $lhs = new \MyOddWeb\BigNumber( 10 );
    $lhs = new \MyOddWeb\BigNumber( 10 );
	$sum = $lhs->Add( $rhs );	// = 20

    // good times...

## Example ##

    include "path/to/code/BigNumber.php"

### Simple usage: ###

See the various tests for more sample code.

Big numbers using strings

    // using strings
    $x = new \MyOddWeb\BigNumber("18446744073709551615");
    $y = new \MyOddWeb\BigNumber("348446744073709551615");
    $sum = $x->Add( $y )->ToString();

Integers

    // using int
    $x = new \MyOddWeb\BigNumber(17);
    $y = new \MyOddWeb\BigNumber(26);
    $sum = $x->Add( $y )->ToInt();

Doubles

    // using doubles
    $x = new \MyOddWeb\BigNumber(17.0);
    $y = new \MyOddWeb\BigNumber(26.0);
    $sum = $x->Add( $y )->ToDouble();

**Operations in one line:**

Integers

    $x = new \MyOddWeb\BigNumber("17")->Add(\MyOddWeb\BigNumber("26"))->ToInt();

Double

    $x = new \MyOddWeb\BigNumber(1.234)->Add(\MyOddWeb\BigNumber(2.345))->ToDouble();

**Create a single item inline**
    
    $x = (new \MyOddWeb\BigNumber( 123 ) )->Log( 20 )->Sub(12)

or you could do the same without the '***new***' argument.

    $x = \MyOddWeb\BigNumber( 123 )->Log( 20 )->Sub(12)

# Functions #
### Math functions ###
- Add( number ) : Add '*number*' to '*this*' number.
- Sub( number ) : Subtract '*number*' from '*this*' number.
- Mul( number ) : Multiply '*number*' to '*this*' number.
- Div( number ) : Divide '*this*' by '*number*' number.
- Add( number ) : Add '*number*' to '*this*' number.
- Factorial() : The factorial of this number, (!n)
- Mod( number ) : The mod of '*this*' number, (n % m). The remainder of the division. 
- Quotient( number ) : : The quotient of dividing '*this*' number with this '*number*'.

### Other functions ###
- IsNeg() : If '*this*' number is negative or not.
- IsZero() : If '*this*' number is zero or not.
- IsNan() : If '*this*' number is Not a number or not, (NAN). This is the result of divide by zero or negative factorials for example.
- IsOdd() : If '*this*' number is odd.
- IsEven() : If '*this*' number is even.
- Compare( number ) : -ve = smaller / +ve = greater / 0 = same.
	- IsEqual( number ) : If '*this*' is equal to '*number*'.
	- IsUnequal( number ) : If '*this*' is not equal to '*number*'.
	- IsGreater( number ) : If '*this*' is greater than '*number*'.
	- IsLess( number ) : If '*this*' is less than '*number*'.
	- IsGreaterEqual( number ) : If '*this*' is greater or equal to '*number*'.
	- IsLessEqual( number ) : If '*this*' is less or equal to '*number*'.
- IsInteger() : If '*this*' number is a whole number positive or negative. 

- ToInt() : convert to int.
- ToDouble() : convert to double.
- ToString() : convert to string.
- Abs() : Get the absolute value of the number
- Trunc( precision ) : Truncate the number, strip the decimals. (+/-n.xyz = n)
- Ceil( precision ) : Round the number up (2.1 = 3 / -2.1 = -2)
- Floor( precision ) : Round the number down (2.1 = 2 / -2.1 = -3)
    
- ToDegree( ... ) : convert *this* from a Radian to a Degree given a certain precision.  
- ToRadiant( ... ) : convert *this* from a Degree to a Radian given a certain precision.    

## Constants ##
- e() : [Euler's number](https://en.wikipedia.org/wiki/E_%28mathematical_constant%29) (to 150 decimals).
- pi(): [Pi](https://en.wikipedia.org/wiki/Pi), (to 150 decimals).

# Todo #

see the [myoddweb.bignumber.cpp project](https://github.com/FFMG/myoddweb.bignumber.cpp) as functions are added here after they are added to the cpp project.

