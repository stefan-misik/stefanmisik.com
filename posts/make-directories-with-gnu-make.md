# Make Directories with GNU Make
- published: 2019-04-11T17:23:56+0000
- tags: makefile, programming

It is easy enough to have GNU Make compile your project and put your object
files and binaries alongside the source files, however it becomes more
complicated once a requirement arises to have them inside separate subdirectory
(e.g. 'build') in the project directory. This may be e.g. due to the need of
having separate 'debug' and 'release' directories or due to having some shared
source files compiled into multiple projects with different compiler settings.

In simple case this is not that complicated either, since all that is needed is
to make the list of directories so called *order-only-prerequisites* (the
prerequisites after the pipe `|` symbol) as shown below:

```makefile
# Make the list of directories containing the object files and the output
# binary
OBJDIRS = $(sort $(dir $(OBJ)))
# Object files depend on object directories
$(OBJ): | $(OBJDIRS)

# Create object directories
$(OBJDIRS):
	mkdir -p $@
endif
```

This is necessary, since once files are added to the directories, their
timestamps will change too, which would cause Make to remake all the files that
depend on particular directories. The *order-only-prerequisites* solve this.

This solution is perfectly fine in environments where `mkdir` command can indeed
handle the `-p` switch (this switch will tell the `mkdir` command to also create
all the non-existent parents of the directory passed to the command), which
arguably is almost all the cases. Now there is one exception and that is
Windows built-in `mkdir` command which does not have any equivalent of the `-p`
switch. Granted in most configurations this command does create the parent
directories, however this is not given, according to Microsoft's documentation.

This, of course, was not good enough for me and I had to start wondering whether
there was some Makefile macro magic that could solve this for me. And there
indeed is a quite involved solution, which consists of some recursive macro (I
was very surprised to see the GNU Make can handle recursive macros).

First and central piece of the solution was a Make macro capable of generating
list of parent directories form directory path (i.e. something that would yield
`a/b/c a/b a` from `a/b/c`). Code below achieves this goal just perfectly (just
try to use `$(call get_subdirs,a/b/c)`).

```makefile
## Remove the topmost directory from the path string
# $(1) Path string (e.g. dir_a/dir_b/dir_c/)
define remove_top_dir
$(if $(findstring /,$(1)),$(patsubst %/,%,$(dir $(patsubst %/,%,$(1)))),)
endef

## Generate list of sub-directory paths from directory path
# $(1) Path string (e.g. dir_a/dir_b/dir_c/)
define get_subdirs
$(if $(1),\
$(patsubst %/,%,$(1)) $(call get_subdirs,$(call remove_top_dir,$(1))),\
)
endef
```

Following code uses this macro to generate the list of all the directories
containing the object files along with their parent directories. Note that the
`$(sort)` function is here used as a way of removing duplicate directories.

```makefile
# List of directories containing object files
OBJDIRS = $(sort $(foreach DIR,$(sort $(dir $(OBJ))),$(call \
get_subdirs,$(DIR))))
```

Now the directories can be made in a similar way as at the top of this article,
just without the need for the `-p` switch. This just have one problem: it will
fail if the `make` command is invoked in the multi-threaded mode, i.e.:

```cmdline
$ make -j [n]
```

This is caused by Make lacking the information that `a\b\c` depends on `a\b` and
it will just happily try to make the former before the latter. This,
surprisingly, also has a solution in GNU Make, just look at the macro below:

```makefile
## Make each directory from the list depend on its parent directory
define make_dirs_depend_on_their_parent
$(foreach DIR,$(1),$(eval \
$(DIR): | $(call remove_top_dir,$(DIR))))
endef
```

This macro makes sure that every directory in the provided list depends on its
parent directory. That is all that it takes to make sure the multi-threaded
makes will not fail.

The final definition now looks as follows (notice that the `-p` switch is not
necessary anymore):

```makefile
# Make sure directory creation is ordered during multi-thread build
$(call make_dirs_depend_on_their_parent,$(OBJDIRS))
# Create object directories
$(OBJDIRS):
	mkdir $@
```

