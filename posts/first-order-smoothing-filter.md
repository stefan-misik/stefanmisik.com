# First-Order Smoothing Filter
- published: 2019-11-09T10:20:54+0000
- tags: embedded, cpp, c, control theory

If you are dealing with analog measurements in your embedded applications, you
might have also had to deal with filters. The analog anti-aliasing one is of
course obligatory, however sometimes it is useful to filter the obtained
measurements digitally too. This can either be to remove natural noise in the
measurements, or even to trade between response time of the measured values and
precision of the measurement.

This is really easy, right? Well I thought so, until I spent half a day
debugging a bug in my filter that was actually a feature, I had completely
blanked on. Therefore I decided to write down all the basics on this type of
filter, if for nothing else, at least for my future reference.

Discrete-time first order smoothing filters considered in this posts are of the
form below, where $`y(k)`$ is the output of the filter at instant $`k`$,
$`u(k)`$ is the input of the filter at time $`k`$. Coefficients $`a`$ and $`b`$
specify the properties of the filter, like gain and cut-off frequency.

```math
y(k) = b u(k) + a y(k-1)
```

The $`z`$-transform of the filter has the following form:

```math
Y(z) = b U(z) + a Y(z) z^{-1}
```

From the above, the transfer function can be derived. This function has the
following form:

```math
F(z) = \frac{Y(z)}{U(z)} = \frac{b}{1 - a z^{-1}}
```

Generally, we want a filter to have gain of $`1`$, in which case the filter can
be characterized by single parameter known as **forget factor**  $`\gamma`$.
The two parameters of the filter can then be characterized using this factor as
follows.

```math
\begin{aligned}
    a &= 1 - \gamma \\
    b &= \gamma
\end{aligned}
```

The forget factor can be calculated, for given cut-off frequency $`f_c`$ and
sampling frequency $`f_s`$, as shown below.

```math
\gamma = 1 - e^{-2 \pi f_c / f_s}
```

The filter can be on embedded devices implemented using fixed-point arithmetic
as shown in the example code below. The proposed filter's `update()` method
needs to be called with new input value with frequency given by sampling
frequency $`f_s`$.

```cpp
#include <cstdint>

class Filter
{
public:
    static const int FRACTION_BITS = 16;

    struct Parameters
    {
        std::int32_t input_gain;
        std::int32_t feedback_gain;
    };

    Filter():
        previous_output_(0)
    { }

    std::int16_t update(const Parameters & parameters, std::int16_t value)
    {
        std::int64_t accumulator;

        //
        // y(k) = B * u(k) + A * y(k-1)
        //

        // B * u(k)
        accumulator = static_cast<std::int64_t>(parameters.input_gain) *
                (static_cast<std::int64_t>(value) * (1ll << FRACTION_BITS));

        // B * u(k) + A * y(k-1)
        accumulator += static_cast<std::int64_t>(parameters.feedback_gain) *
                static_cast<std::int64_t>(previous_output_);

        // k <- k + 1
        previous_output_ = static_cast<std::int32_t>(
                accumulator / (1ll << FRACTION_BITS));

        return static_cast<std::int16_t>(
                previous_output_ / (1l << FRACTION_BITS));
    }

private:
    std::int32_t previous_output_;
};
```

To verify the proper behavior of the smoothing filter, it is possible to measure
the time constant $`\tau`$ of the filter's step response. The expected time
constant can be calculated using equation below.

```math
\tau = \frac{1}{2 \pi f_c}
```
