# Sandboxing Lua from C
- published: 2018-07-06T17:00:00+0000
- tags: lua, c, programming

As mentioned in previous [post](/post/add-lua-to-makefile-based-c-project),
Lua programming language is useful tool for embedding scripting interface
into existing projects implemented (not just) in C.

In this post I would like to introduce my take on sandboxing Lua code from the
host-side C code. This is, however, **not** a security-oriented sandbox, rather
a safety one. This is due to the fact, that firstly, the unwanted functionality
is blacklisted, whereas secure sandbox should rather white-list wanted
functionality; and secondly, I am currently not sure that the blacklisted
functionality can not be recovered by an adversary Lua code.

So what is the purpose of such quasi-sandbox? The main idea behind this was to
prevent user unknowingly using functionality that is not supported by the host
application.

This is how the proposed quasi-sandbox is implemented: normally after creating
new Lua state, host code should call `luaL_openlibs()` function to load standard
libraries provided by the Lua language, however this will pull-in many functions
which might be unwanted in certain situations. Therefore, this sandbox pulls-in
each library individually by calling `luaL_requiref()` and exploits the fact
that this function leaves a table of loaded functions on the top of the Lua
stack. The unwanted functions are then undefined by giving them `nil` value, as
you can see in the pleasingly simple code below (please excuse the outdated
Hungarian notation, the code is taken from a Win32 application, and I tend to
strive for consistent code style).

```c
/**
 * @brief Load the specified module and undefine specified functions
 * 
 * @param[in] lpLua Pointer to the structure containing Lua state
 * @param[in] lpOpenFcn Function to load the module
 * @param[in] lpModuleName Module name, "_G" for base library
 * @param[in] lpFunctions Array of functions to be undefined after load. Last
 *            element must be NULL
 */
static VOID LuaLoadAndUndefine(
    LPLUA lpLua,
    lua_CFunction lpOpenFcn,
    LPCSTR lpModuleName,
    LPCSTR lpFunctions[]
)
{
    INT iFunction = 0;
    
    /* Load the module, the module table gets placed on the top of the stack */
    luaL_requiref(lpLua->lpLua, lpModuleName, lpOpenFcn, 1);
    
    /* Undefine the unwanted functions */
    while(NULL != lpFunctions[iFunction])
    {
        lua_pushnil(lpLua->lpLua);
        lua_setfield(lpLua->lpLua, -2, lpFunctions[iFunction]);
        
        iFunction ++;
    }
    
    /* Pop the module table */
    lua_pop(lpLua->lpLua, 1);
}
```

Below you can see an example of loading some libraries into new Lua state. The
list of *unsafe* functions is inspired by a
[post](http://lua-users.org/wiki/SandBoxes) in Lua users wiki.

```c
/* Create quasi-safe sand box by loading only portion of the libraries and
 * undefining potentially dangerous functions */
/* Load some of the Lua libraries */
LuaLoadAndUndefine(lpLua, luaopen_base, "_G", (LPCSTR []){"assert",
    "collectgarbage", "dofile", "getmetatable", "loadfile", "load",
    "loadstring", "print", "rawequal", "rawlen", "rawget", "rawset",
    "setmetatable", NULL});
LuaLoadAndUndefine(lpLua, luaopen_string, LUA_STRLIBNAME,
    (LPCSTR []){"dump", NULL});
LuaLoadAndUndefine(lpLua, luaopen_table, LUA_TABLIBNAME,
    (LPCSTR []){NULL});
LuaLoadAndUndefine(lpLua, luaopen_math, LUA_MATHLIBNAME,
    (LPCSTR []){NULL});
```

It is neccessary to give some **final notes** regarding the proposed solution:
it is not clear whether Lua interpreter will preserve full functionality in all
aspcts outside the unwanted functionality, when only subset of standard
libraries are loaded, however it did worked well for my solution. In any case
some testing is advised. This is due to the the fact, that some seemingly core
functionality of the language (e.g. `ipairs()`) is actually provided by the base
library.
