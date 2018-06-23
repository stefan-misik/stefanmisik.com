# Avoid Pointer Problems with C++ Ref-qualifiers
- published: 2019-06-21T16:03:15+0000
- tags: programming, cpp

I am generally using C++ in various embedded projects, therefore I do not often
get the luxury of using dynamic memory allocation and smart pointers, therefore
I often have to resort to solution as the one described in this article to help
me keep my code bug-free.

### Const Qualifier

You might be familiar with `const` qualifier with method and with its ability to
overload non-constant version of the same function, as follows:

```cpp
#include <iostream>

class SomeClass
{
public:
    void doSomething() const
    {
        std::cout << __func__ << "() const called " << std::endl;
    }
    void doSomething()
    {
        std::cout << __func__ << "() called " << std::endl;
    }
};

int main()
{
    SomeClass t1;
    const SomeClass t2;

    t1.doSomething();
    t2.doSomething();

    return 0;
}
```

This code will result in following output:

```txt
doSomething() called
doSomething() const called
```

### Ref Qualifiers

Similarly to above mentioned constant qualifiers, since C++ 11, one can overload
methods based on value category of the object instance on which the method is
being called, as follows:

```cpp
#include <iostream>

class SomeClass
{
public:
    void doSomething() &&
    {
        std::cout << __func__ << "() r-value called " << std::endl;
    }
    void doSomething() &
    {
        std::cout << __func__ << "() l-value called " << std::endl;
    }
};

SomeClass g;

int main()
{
    g.doSomething();
    SomeClass().doSomething();
    return 0;
}
```

This code will produce following output:
```txt
doSomething() l-value called
doSomething() r-value called
```

### Avoiding Pointer Problems

As I mentioned, I often write C++ code for embedded systems, that does not
utilize dynamic memory allocation or smart pointers, therefore I often end up
with classes that internally hold buffer for whatever they need to do, as
follows:

```cpp
#include <iostream>
#include <cstring>

class ShortString
{
protected:
    char buffer_[64];

public:
    ShortString()
    {
        buffer_[0] = '\0';
    }

    ShortString(const char * str)
    {
        std::strncpy(buffer_, str, sizeof(buffer_));
    }

    operator const char * () const
    {
        return buffer_;
    }
};

int main()
{
    ShortString a("Test");

    std::cout << a << std::endl;

    return 0;
}
```

In some situations, however it might be tempting to do something as:

```cpp
const char * testFcn()
{
    return ShortString("Test2");
}
```

This is obviously incorrect, since the object and the buffer do not exist once
the functions return. To prevent this it is simple to to modify the class using
ref-qualifier overload, as follows:

```cpp
#include <cstring>

class ShortString
{
protected:
    char buffer_[64];

public:
    ShortString()
    {
        buffer_[0] = '\0';
    }

    ShortString(const char * str)
    {
        std::strncpy(buffer_, str, sizeof(buffer_));
    }

    operator const char * () const &
    {
        return buffer_;
    }

    operator const char * () const && = delete;
};
```

This code will refuse to compile in case the class is used as shown in the
function above. This use of ref-qualifier overload is not limited to use with
strings, rather it can be used anytime a method returns reference or a pointer
to a member of its object.

### Conclusion

This solution is not completely foolproof, since there are still ways to get the
invalid pointer out of the class. It, however, can prevent some awkward mistakes
which is quite important, since on many platforms the code above returning the
pointer form function can seem to work just fine in many cases, however it is an
undefined behavior.
