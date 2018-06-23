# Add Lua to Makefile-based C Project
- published: 2018-07-05T08:00:00+0000
- tags: lua, makefile, git, programming

In some cases it might be useful to allow users of your application to
configure, modify, or extend behavior of your application. This, certainly, can
be achieved through multiple approaches, including extensions or just a
configuration file in simpler cases. Implementing a scripting interface is a
solution, which arguably combines relative ease of use of a configuration file
and flexibility of an extension.

One of the scripting languages developed explicitly for the purpose of being
embedded into an existing application is [Lua](https://lua.org). This post is,
however not a tutorial dealing with integration of Lua interpreter into your
C/C++ project (this topic might be explored in a later post), rather it deals
with integration of Lua source file into a makefile-based build system.

Examples in this post are taken from a Win32 C project built using MinGW
toolchain. This project also resides within a Git repository. I had several
objectives while integrating the Lua interpreter:

* to preserve distinct separation between Lua interpreter source code and the
  source code of my application,
* not to store the source code of Lua interpreter in my projects repository,
* to achieve easy migration to newer revision of the interpreter in the future.

The proposed solution then ended up being as follows: URL of the `.tar.gz`
package of the Lua interpreter source files is stored in `$(LUA_SRC)` make
variable, and will be downloaded and unpacked into `$(LUA_ARCH)` directory
(`lua-5.3.4` in the case of the example in this post). Both the directory and
downloaded archive are ignored in project's `.gitignore` file with wild card for
the Lua version.

The proposed Makefile solution can start with following definitions:

```makefile
# Download tool
DOWNT = curl -R -O
# TAR tool
TAR = tar

LUA_SRC = http://www.lua.org/ftp/lua-5.3.4.tar.gz

# Lua stuff
LUA_ARCH    = $(notdir $(LUA_SRC))
LUA_DIR     = $(basename $(basename $(LUA_ARCH)))
LUA_LIB     = $(LUA_DIR)/install/lib/liblua.a
```

The Lua building and cleaning is achieved in the following snippet:

```makefile
.PHONY: all clean cleanall

# Lua download
$(LUA_ARCH):
	$(DOWNT) $(LUA_SRC)

# Lua build
$(LUA_LIB): $(LUA_ARCH)
	$(TAR) -xzf $<
	$(MAKE) -C $(LUA_DIR) generic CC=$(CC)
	$(MAKE) -C $(LUA_DIR) install INSTALL_TOP=../install


clean:
	$(RM) $(EXECUTABLE) $(OBJ)
	-$(MAKE) -C $(LUA_DIR) uninstall INSTALL_TOP=../install
	-$(MAKE) -C $(LUA_DIR) $@

cleanall: clean
	$(RM) -r $(LUA_ARCH) $(LUA_DIR)
```

Note that, clean rule is divided into two sub-rules: `clean` itself and
`cleanall` where the former does not remove downloaded files to speed up
re-building of the project during development.

As mentioned on their [web page](https://www.lua.org/about.html), Lua
interpreter is implemented in C programming language using a subset of ANSI C
API, and as such it should compile virtually on any platform. Although the Lua's
Makefile provides multiple targets for individual platforms, dealing with minor
differences between these platform, e.g. for MinGW/Windows it will also build a
DLL file. For static linking of the Lua interpreter into the existing
project the `generic` target is sufficient (it will build an archive file, which
`gcc` can link against). It is, however, important to build the Lua interpreter
using the correct compiler, in case the default compiler is not the one used by
the project (e.g. MinGW compiler used within Cygwin environment). This is
achieved by the `CC=$(CC)` portion of the build command.
