# C++ Object Layout and Casting
- published: 2019-07-13T08:28:44+0000
- tags: cpp, programming

This article explores implications of using the `static_cast<>` on objects with
multiple parents and their memory layout.

Even moderately experienced C++ programmers can guess that the `static_cast<>`
performs some reasonable change of its parameter to produce appropriate result,
while `reinterpret_cast<>` just tells the compiler to reconsider the type of its
parameter without changing it.

In this article I will consider a hierarchy of classes as shown below:

```cpp
class A
{
    int x;
};

class B
{
    int y;
    void * ptr;
};

class C:
    public A, public B
{
    double z;
};
```

The important thing in this example is the multiple inheritance, because that
is where the interesting things happen. To explore the memory layout of the
object `C`, following code can be executed:

```cpp
int main()
{
    C * c_ptr = new C();

    std::cout << "c_ptr = " << c_ptr << std::endl;
    std::cout << "(A *)c_ptr = " << static_cast<A *>(c_ptr) << std::endl;
    std::cout << "(B *)c_ptr = " << static_cast<B *>(c_ptr) << std::endl;

    delete c_ptr;
    return 0;
}
```

The result of the code will depend on some things, including the architecture of
the computer, or implementation of the compiler, however it can be expected to
look something as follows:

```txt
c_ptr = 0x100000000000
(A *)c_ptr = 0x100000000000
(B *)c_ptr = 0x100000000008
```

From this output it can be inferred that objects of the class `C` are laid out
in the memory as is shown in the following diagram:

```ascii
+------- C -------+
|+------ A ------+|
|| int x         ||
|+---------------+|
|+------ B ------+|
|| int y         ||
|| void * ptr    ||
|+---------------+|
| double z        |
+-----------------+
```

Another thing that follows form the output of the test program is the fact that
`static_cast`ing a pointer to an object of a class that involves multiple
inheritance may lead to some arithmetic being done on the pointer to perform the
conversion. Therefore, one can expect that the following pseudo-code expresses
what the static cast actually does:

```cpp
template<typename TO>
inline TO static_cast(C* from);

// WARNING: Following code is not entirely correct, please read further!
template<>
inline B * static_cast(C * from)
{
    std::uintptr_t to =
            reinterpret_cast<std::uintptr_t>(from) +
            4 +  // sizeof(A)
            4;  // Padding
    return reinterpret_cast<B *>(to);
}
```

This is at least what I expected it to do, since I reasoned that `static_cast`,
in contrast with `dynamic_cast`, should not do any run-time checks. Luckily, I
was wrong.

The issue with the implementation above is the case, where the pointer on which
the type cast is to be performed was initialized from `nullptr` (it is important
that it is not the `nullptr` itself, which is being casted, since it is of type
`nullptr_t` which casts properly into any pointer type): let's say the
`nullptr`-initialized variable points towards memory position zero; after
performing the above cast, the pointer value suddenly becomes something else
than zero and any check to see whether the converted pointer is `nullptr` would
fail, potentially causing the following program to crash.

```cpp
int main()
{
    C * c_ptr = nullptr;
    B * b_ptr = static_cast<B *>(c_ptr);

    if (nullptr != b_ptr)
    {
        doSomething(b_ptr);
    }
}
```

This is fortunately not going to be the case. It can be easily verified by
running the first example program, however slightly modified, as follows:

```cpp
int main()
{
    C * c_ptr = nullptr;

    std::cout << "c_ptr = " << c_ptr << std::endl;
    std::cout << "(A *)c_ptr = " << static_cast<A *>(c_ptr) << std::endl;
    std::cout << "(B *)c_ptr = " << static_cast<B *>(c_ptr) << std::endl;

    return 0;
}
```

The result will be like this:

```txt
c_ptr = 0x0
(A *)c_ptr = 0x0
(B *)c_ptr = 0x0
```

From this it can be inferred that the pseudo-implementation of the `static_cast`
should look more like this:

```cpp
template<>
inline B * static_cast(C * from)
{
    if (nullptr == from)
    {
        return nullptr;
    }

    std::uintptr_t to =
            reinterpret_cast<std::intptr_t>(from) +
            4 +  // sizeof(A)
            4;  // Padding
    return reinterpret_cast<B *>(to);
}
```

And indeed this is supported by the disassembly of the example code above:

```x86asm
## int main()
## {

##     // ...

##     std::cout << "(B *)c_ptr = " << static_cast<B *>(c_ptr) << std::endl;
lea    rsi,[rip+0x157]
mov    rax,QWORD PTR [rip+0x270]
mov    rdi,rax
call   100000eb0
mov    rdx,rax

### Something is here compared to 0
cmp    QWORD PTR [rbp-0x18],0x0
### If it indeed is 0 jump over to just below the next 'jmp' instruction
je     100000dc4
mov    rax,QWORD PTR [rbp-0x18]
add    rax,0x8
### Jump over the 'mov' instruction below
jmp    100000dc9

# 100000dc4:
mov    eax,0x0

# 100000dc9:
mov    rsi,rax
mov    rdi,rdx
call   100000ea4
mov    rdx,rax
mov    rax,QWORD PTR [rip+0x242]
mov    rsi,rax
mov    rdi,rdx
call   100000e9e

##     return 0;
mov    eax,0x0
## }
```

In conclusion, while it is true that the `static_cast<>` does not do run-time
check to see whether the actual passed object is of the appropriate type,
however it will do run-time check to see whether the passed pointer equals to
`nullptr` in case pointer arithmetic is involved in the type casting (i.e. an
object with multiple parents is involved in the conversion).

This is supported by the C++ standard ([Working Draft, Standard for Programming
Language
C++](http://www.open-std.org/jtc1/sc22/wg21/docs/papers/2014/n4296.pdf),
[expr.static.cast], paragraph 13):

> The null pointer value is converted to the null pointer value of the
> destination type.

