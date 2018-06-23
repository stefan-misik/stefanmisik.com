# Compile-time Floating Point Arithmetic For Fixed-point Target in C++
- published: 2018-08-18T08:00:00+0000
- tags: cpp, programming, embedded

Sometimes, especially on an embedded platforms, it is desirable to avoid
floating-point arithmetic, i.e. an arithmetic involving `double` or `float`
types in C/C++. This, however does not mean it is necessary to throw away these
types altogether.

Thanks to handy `constexpr` specifier in C++11, it is possible to perform some
floating-point calculations during compilation. This is useful for example in
case when some integer constants need to be calculated from given parameters
(e.g. A-D converter resolution, A-D reference voltage, etc.), making it easy to
change these parameters and having the fixed-point constants updated
automatically.

Let's say we want to measure voltage using and A-D converter and the real
measured voltage $`V_{measured}`$ is given in terms of A-D converted value $`N`$
by the equation:

```math
V_{measured} = \frac{1}{K}\frac{N}{N_{admax}}V_{adref}
```

Where $`K`$ [-] is some amplification/attenuation constant, $`V_{adref}`$ [V] is
reference voltage of the A-D converter, $`N_{admax}`$ [-] is given by the
resolution of the A-D converter, and $`N`$ [-] is the value measured by the A-D
converter.

The goal is to represent the measured voltage as an integer in millivolts, while
it is known that the measured voltage is not going to be greater than 10 volts.
This value will comfortably fit in a 16-bit wide integer of any signedness. 

Given goal can be achieved using the following function, which takes the value
from the A-D converter as its input (i.e. the raw value read from the
processor's special function register corresponding to the A-D converter) and
outputs the measured voltage in millivolts represented as 16-bit wide integer.

```cpp
#define K 0.1         // 1:10 voltage divider
#define N_ADMAX 1023  // 10-bit ADC
#define V_ADREF 3.3   // A-D reference voltage is 3.3 V
#define V_REF 10      // Our reference voltage 10 V is scaled to 10000
#define SCALE 10000   // Integer scale

short scale_measurement(short n_adc)
{
    /*
     *            V_ref * K * N_admax
     * N_vref = -----------------------
     *                  V_adref
     */
    constexpr int N_VREF = round_and_cast(
        (double)V_REF *
        (double)K *
        (double)N_ADMAX /
        (double)V_ADREF
        );

    return ((int)n_adc * (int)SCALE) / (short)N_VREF;
}
```

Where `constexpr` function `round_and_cast()` is defined as follows:

```cpp
constexpr int round_and_cast(double value)
{
    return (int)value +
        (((value - ((int)value)) >= 0.5) ? 1 :
            (((value - ((int)value)) <= -0.5) ? -1 : 0));
}
```

To verify the above code, let's compile it for ARM Cortex-M4 processor without
floating point unit using the following command:

```bash
arm-none-eabi-g++ -std=c++11 -pedantic -mcpu=cortex-m4 -S scale_measurements.cpp
```
The result is as follows:

```armasm
_Z17scale_measurements:
	.fnstart
.LFB2:
	@ args = 0, pretend = 0, frame = 16
	@ frame_needed = 1, uses_anonymous_args = 0
	@ link register save eliminated.
	push	{r7}
	sub	sp, sp, #20
	add	r7, sp, #0
	mov	r3, r0
	strh	r3, [r7, #6]	@ movhi
	mov	r3, #310
	strh	r3, [r7, #14]	@ movhi
	ldrsh	r3, [r7, #6]
	movw	r2, #10000
	mul	r3, r2, r3
	ldr	r2, .L3
	smull	r1, r2, r2, r3
	asrs	r2, r2, #5
	asrs	r3, r3, #31
	subs	r3, r2, r3
	sxth	r3, r3
	mov	r0, r3
	adds	r7, r7, #20
	mov	sp, r7
	@ sp needed
	pop	{r7}
	bx	lr
.L4:
	.align	2
.L3:
	.word	443351463
	.cantunwind
	.fnend
```
It is obvious that, the code above does not use any floating point arithmetic,
instead it uses the constant `#310` directly, which is the result of the
`constexpr` expression in the code for the given constants.
