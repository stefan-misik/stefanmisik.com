# Portable Unaligned Memory Access for ARM Cortex
- published: 2019-08-26T18:05:45+0000
- tags: cpp, c, gcc, programming, arm, cortex

One of the interesting issues I recently had to solve manifested while I was
porting a piece of a software, which usually ran either on a PC or an ARM Cortex
M4 processor, to an ARM Cortex M0 processor, which seemingly unexpectedly ended
up in an hard fault while running the code, which worked fine on other platforms
and is even covered with unit tests.

After looking at the call stack after the hard fault, the culprit seemed to be
a routine, which looked something as follows:

```cpp
void encode(
    std::uint8_t * destiantion,
    const std::uint8_t * source,
    std::size_t length)
{
    const std::uint8_t * end = source + length;

    while (source < end)
    {
        *reinterpret_cast<std::uint16_t *>(destiantion) =
                encoding_table[*source];
        destiantion += sizeof(std::uint16_t);
        source++;
    }
}
```

I did have a vague idea about the M0 architecture not being able to perform
unaligned memory accesses and after a minute of debugging, I did discover the
routine above was indeed being called with `destination` parameter pointing to
an unaligned memory location and performing word-store operation on that
address. This is usually not an issue, apart from slight performance penalty
(but we are not here to micro-optimize the code), however ARM Cortex in fact can
not perform these type of operations.

The problem stems from the fact that the GCC compiler expects the *values* of
pointers to be correctly aligned (depending on the type of the pointer). While
this is normally reasonable assumption necessary for allowing reasonable
performance of the generated code, in this particular case it causes the
above-mentioned issue.

The naive solution to this problem would be to provide the specific
implementation of the code for the ARM Cortex M0 processor, however I did not
want to segment the code due to the maintainability concerns.

After some research I came up with a solution that seemed satisfactory for me.
The solution is to explicitly let the compiler know about the fact that it might
be accessing unaligned memory locations and let it generate the appropriate code
for given architecture. This can be achieved on GCC compilers as follows:

```cpp
void encode(
    std::uint8_t * destiantion,
    const std::uint8_t * source,
    std::size_t length)
{
    const std::uint8_t * end = source + length;

    struct packed_uint16_t
    {
        std::uint16_t value;
    }  __attribute__((packed));

    while (source < end)
    {
         reinterpret_cast<packed_uint16_t *>(destiantion)->value =
                 encoding_table[*source];
        destiantion += sizeof(std::uint16_t);
        source++;
    }
}
```

Since notion of packed structures exists on many compilers the above code can be
made even more portable by using some preprocessor magic.

The solution worked fine on the ARM Cortex M0, however I did have to investigate
whether this modification affected other platforms in some way. I did this by
disassembling the code generated (with at least debug `-Og` optimizations turned
on) for the ARM Cortex M4. Below is the code generated form the original version
of the function.

```armasm
<encode(unsigned char*, unsigned char const*, unsigned int)>:
  add     r2, r1
  cmp     r1, r2
  bcs.n   exit
  push    {r4}
loop:
  ldrb.w  r4, [r1], #1
  ldr     r3, [pc, #16]
  ldrh.w  r3, [r3, r4, lsl #1]
  strh.w  r3, [r0], #2
  cmp     r1, r2
  bcc.n   loop
  pop     {r4}
exit:
  bx      lr
```

Now follows the disassembly of the code generated for the ARM Cortex M4 form the
modified version of the function.

```armasm
<encode(unsigned char*, unsigned char const*, unsigned int)>:
  add     r2, r1
  cmp     r1, r2
  bcs.n   exit
  push    {r4}
loop:
  ldrb.w  r4, [r1], #1
  ldr     r3, [pc, #16]
  ldrh.w  r3, [r3, r4, lsl #1]
  strh.w  r3, [r0], #2
  cmp     r1, r2
  bcc.n   loop
  pop     {r4}
exit:
  bx      lr
```

As many keen-eyed readers might have noticed the instructions generated by my
compiler are indeed identical, even though the source code differs. I found this
result very satisfying.
